<?php

declare(strict_types=1);

namespace Sabberworm\CSS\RuleSet;

use Sabberworm\CSS\Comment\CommentContainer;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Position\Position;
use Sabberworm\CSS\Position\Positionable;
use Sabberworm\CSS\Property\Declaration;

/**
 * This class is a container for individual `Declaration`s.
 *
 * The most common form of a rule set is one constrained by a selector, i.e., a `DeclarationBlock`.
 * However, unknown `AtRule`s (like `@font-face`) are rule sets as well.
 *
 * If you want to manipulate a `RuleSet`,
 * use the methods `addDeclaration()`, `getDeclarations()`, `removeDeclaration()`, `removeMatchingDeclarations()`, etc.
 *
 * Note that `CSSListItem` extends both `Commentable` and `Renderable`, so those interfaces must also be implemented.
 */
class RuleSet implements CSSElement, CSSListItem, Positionable, DeclarationList
{
    use CommentContainer;
    use LegacyDeclarationListMethods;
    use Position;

    /**
     * the declarations in this rule set, using the property name as the key,
     * with potentially multiple declarations per property name.
     *
     * @var array<string, array<int<0, max>, Declaration>>
     */
    private $declarations = [];

    /**
     * @param int<1, max>|null $lineNumber
     */
    public function __construct(?int $lineNumber = null)
    {
        $this->setPosition($lineNumber);
    }

    /**
     * @throws UnexpectedTokenException
     * @throws UnexpectedEOFException
     *
     * @internal since V8.8.0
     */
    public static function parseRuleSet(ParserState $parserState, RuleSet $ruleSet): void
    {
        while ($parserState->comes(';')) {
            $parserState->consume(';');
        }
        while (true) {
            $commentsBeforeDeclaration = [];
            $parserState->consumeWhiteSpace($commentsBeforeDeclaration);
            if ($parserState->comes('}')) {
                break;
            }
            $declaration = null;
            if ($parserState->getSettings()->usesLenientParsing()) {
                try {
                    $declaration = Declaration::parse($parserState, $commentsBeforeDeclaration);
                } catch (UnexpectedTokenException $e) {
                    try {
                        $consumedText = $parserState->consumeUntil(["\n", ';', '}'], true);
                        // We need to “unfind” the matches to the end of the ruleSet as this will be matched later
                        if ($parserState->streql(\substr($consumedText, -1), '}')) {
                            $parserState->backtrack(1);
                        } else {
                            while ($parserState->comes(';')) {
                                $parserState->consume(';');
                            }
                        }
                    } catch (UnexpectedTokenException $e) {
                        // We’ve reached the end of the document. Just close the RuleSet.
                        return;
                    }
                }
            } else {
                $declaration = Declaration::parse($parserState, $commentsBeforeDeclaration);
            }
            if ($declaration instanceof Declaration) {
                $ruleSet->addDeclaration($declaration);
            }
        }
        $parserState->consume('}');
    }

    /**
     * @throws \UnexpectedValueException
     *         if the last `Declaration` is needed as a basis for setting position, but does not have a valid position,
     *         which should never happen
     */
    public function addDeclaration(Declaration $declarationToAdd, ?Declaration $sibling = null): void
    {
        $propertyName = $declarationToAdd->getPropertyName();
        if (!isset($this->declarations[$propertyName])) {
            $this->declarations[$propertyName] = [];
        }

        $position = \count($this->declarations[$propertyName]);

        if ($sibling !== null) {
            $siblingIsInSet = false;
            $siblingPosition = \array_search($sibling, $this->declarations[$propertyName], true);
            if ($siblingPosition !== false) {
                $siblingIsInSet = true;
                $position = $siblingPosition;
            } else {
                $siblingIsInSet = $this->hasDeclaration($sibling);
                if ($siblingIsInSet) {
                    // Maintain ordering within `$this->declarations[$propertyName]`
                    // by inserting before first `Declaration` with a same-or-later position than the sibling.
                    foreach ($this->declarations[$propertyName] as $index => $declaration) {
                        if (self::comparePositionable($declaration, $sibling) >= 0) {
                            $position = $index;
                            break;
                        }
                    }
                }
            }
            if ($siblingIsInSet) {
                // Increment column number of all existing declarations on same line, starting at sibling
                $siblingLineNumber = $sibling->getLineNumber();
                $siblingColumnNumber = $sibling->getColumnNumber();
                foreach ($this->declarations as $declarationsForAProperty) {
                    foreach ($declarationsForAProperty as $declaration) {
                        if (
                            $declaration->getLineNumber() === $siblingLineNumber &&
                            $declaration->getColumnNumber() >= $siblingColumnNumber
                        ) {
                            $declaration->setPosition($siblingLineNumber, $declaration->getColumnNumber() + 1);
                        }
                    }
                }
                $declarationToAdd->setPosition($siblingLineNumber, $siblingColumnNumber);
            }
        }

        if ($declarationToAdd->getLineNumber() === null) {
            //this node is added manually, give it the next best line
            $columnNumber = $declarationToAdd->getColumnNumber() ?? 0;
            $declarations = $this->getDeclarations();
            $declarationsCount = \count($declarations);
            if ($declarationsCount > 0) {
                $last = $declarations[$declarationsCount - 1];
                $lastsLineNumber = $last->getLineNumber();
                if (!\is_int($lastsLineNumber)) {
                    throw new \UnexpectedValueException(
                        'A Declaration without a line number was found during addDeclaration',
                        1750718399
                    );
                }
                $declarationToAdd->setPosition($lastsLineNumber + 1, $columnNumber);
            } else {
                $declarationToAdd->setPosition(1, $columnNumber);
            }
        } elseif ($declarationToAdd->getColumnNumber() === null) {
            $declarationToAdd->setPosition($declarationToAdd->getLineNumber(), 0);
        }

        \array_splice($this->declarations[$propertyName], $position, 0, [$declarationToAdd]);
    }

    /**
     * Returns all declarations matching the given property name
     *
     * @example $ruleSet->getDeclarations('font') // returns array(0 => $declaration, …) or array().
     *
     * @example $ruleSet->getDeclarations('font-')
     *          //returns an array of all declarations either beginning with font- or matching font.
     *
     * @param string|null $searchPattern
     *        Pattern to search for. If null, returns all declarations.
     *        If the pattern ends with a dash, all declarations starting with the pattern are returned
     *        as well as one matching the pattern with the dash excluded.
     *
     * @return array<int<0, max>, Declaration>
     */
    public function getDeclarations(?string $searchPattern = null): array
    {
        $result = [];
        foreach ($this->declarations as $propertyName => $declarations) {
            // Either no search pattern was given
            // or the search pattern matches the found declaration's property name exactly
            // or the search pattern ends in “-”
            // ... and the found declaration's property name starts with the search pattern
            if (
                $searchPattern === null || $propertyName === $searchPattern
                || (
                    \strrpos($searchPattern, '-') === \strlen($searchPattern) - \strlen('-')
                    && (\strpos($propertyName, $searchPattern) === 0
                        || $propertyName === \substr($searchPattern, 0, -1))
                )
            ) {
                $result = \array_merge($result, $declarations);
            }
        }
        \usort($result, [self::class, 'comparePositionable']);

        return $result;
    }

    /**
     * Overrides all the declarations of this set.
     *
     * @param array<Declaration> $declarations
     */
    public function setDeclarations(array $declarations): void
    {
        $this->declarations = [];
        foreach ($declarations as $declaration) {
            $this->addDeclaration($declaration);
        }
    }

    /**
     * Returns all declarations with property names matching the given pattern and returns them in an associative array
     * with the property names as keys.
     * This method exists mainly for backwards-compatibility and is really only partially useful.
     *
     * Note: This method loses some information: Calling this (with an argument of `background-`) on a declaration block
     * like `{ background-color: green; background-color; rgba(0, 127, 0, 0.7); }` will only yield an associative array
     * containing the rgba-valued declaration while `getDeclarations()` would yield an indexed array containing both.
     *
     * @param string|null $searchPattern
     *        Pattern to search for. If null, returns all declarations. If the pattern ends with a dash,
     *        all declarations starting with the pattern are returned as well as one matching the pattern with the dash
     *        excluded.
     *
     * @return array<string, Declaration>
     */
    public function getDeclarationsAssociative(?string $searchPattern = null): array
    {
        /** @var array<string, Declaration> $result */
        $result = [];
        foreach ($this->getDeclarations($searchPattern) as $declaration) {
            $result[$declaration->getPropertyName()] = $declaration;
        }

        return $result;
    }

    /**
     * Removes a `Declaration` from this `RuleSet` by identity.
     */
    public function removeDeclaration(Declaration $declarationToRemove): void
    {
        $nameOfPropertyToRemove = $declarationToRemove->getPropertyName();
        if (!isset($this->declarations[$nameOfPropertyToRemove])) {
            return;
        }
        foreach ($this->declarations[$nameOfPropertyToRemove] as $key => $declaration) {
            if ($declaration === $declarationToRemove) {
                unset($this->declarations[$nameOfPropertyToRemove][$key]);
            }
        }
    }

    /**
     * Removes declarations by property name or search pattern.
     *
     * @param string $searchPattern
     *        pattern to remove.
     *        If the pattern ends in a dash,
     *        all declarations starting with the pattern are removed as well as one matching the pattern with the dash
     *        excluded.
     */
    public function removeMatchingDeclarations(string $searchPattern): void
    {
        foreach ($this->declarations as $propertyName => $declarations) {
            // Either the search pattern matches the found declaration's property name exactly
            // or the search pattern ends in “-” and the found declaration's property name starts with the search
            // pattern or equals it (without the trailing dash).
            if (
                $propertyName === $searchPattern
                || (\strrpos($searchPattern, '-') === \strlen($searchPattern) - \strlen('-')
                    && (\strpos($propertyName, $searchPattern) === 0
                        || $propertyName === \substr($searchPattern, 0, -1)))
            ) {
                unset($this->declarations[$propertyName]);
            }
        }
    }

    public function removeAllDeclarations(): void
    {
        $this->declarations = [];
    }

    /**
     * @internal
     */
    public function render(OutputFormat $outputFormat): string
    {
        return $this->renderDeclarations($outputFormat);
    }

    protected function renderDeclarations(OutputFormat $outputFormat): string
    {
        $result = '';
        $isFirst = true;
        $nextLevelFormat = $outputFormat->nextLevel();
        foreach ($this->getDeclarations() as $declaration) {
            $nextLevelFormatter = $nextLevelFormat->getFormatter();
            $renderedDeclaration = $nextLevelFormatter->safely(
                static function () use ($declaration, $nextLevelFormat): string {
                    return $declaration->render($nextLevelFormat);
                }
            );
            if ($renderedDeclaration === null) {
                continue;
            }
            if ($isFirst) {
                $isFirst = false;
                $result .= $nextLevelFormatter->spaceBeforeRules();
            } else {
                $result .= $nextLevelFormatter->spaceBetweenRules();
            }
            $result .= $renderedDeclaration;
        }

        $formatter = $outputFormat->getFormatter();
        if (!$isFirst) {
            // Had some output
            $result .= $formatter->spaceAfterRules();
        }

        return $formatter->removeLastSemicolon($result);
    }

    /**
     * @return array<string, bool|int|float|string|array<mixed>|null>
     *
     * @internal
     */
    public function getArrayRepresentation(): array
    {
        throw new \BadMethodCallException('`getArrayRepresentation` is not yet implemented for `' . self::class . '`');
    }

    /**
     * @return int negative if `$first` is before `$second`; zero if they have the same position; positive otherwise
     *
     * @throws \UnexpectedValueException if either argument does not have a valid position, which should never happen
     */
    private static function comparePositionable(Positionable $first, Positionable $second): int
    {
        $firstsLineNumber = $first->getLineNumber();
        $secondsLineNumber = $second->getLineNumber();
        if (!\is_int($firstsLineNumber) || !\is_int($secondsLineNumber)) {
            throw new \UnexpectedValueException(
                'A Declaration without a line number was passed to comparePositionable',
                1750637683
            );
        }

        if ($firstsLineNumber === $secondsLineNumber) {
            $firstsColumnNumber = $first->getColumnNumber();
            $secondsColumnNumber = $second->getColumnNumber();
            if (!\is_int($firstsColumnNumber) || !\is_int($secondsColumnNumber)) {
                throw new \UnexpectedValueException(
                    'A Declaration without a column number was passed to comparePositionable',
                    1750637761
                );
            }
            return $firstsColumnNumber - $secondsColumnNumber;
        }

        return $firstsLineNumber - $secondsLineNumber;
    }

    private function hasDeclaration(Declaration $declaration): bool
    {
        foreach ($this->declarations as $declarationsForAProperty) {
            if (\in_array($declaration, $declarationsForAProperty, true)) {
                return true;
            }
        }

        return false;
    }
}
