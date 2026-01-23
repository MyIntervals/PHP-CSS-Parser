<?php

declare(strict_types=1);

namespace Sabberworm\CSS\RuleSet;

use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\Comment\CommentContainer;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\CSSList\CSSList;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\CSSList\KeyFrame;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\OutputException;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Position\Position;
use Sabberworm\CSS\Position\Positionable;
use Sabberworm\CSS\Property\KeyframeSelector;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\Settings;

/**
 * This class represents a `RuleSet` constrained by a `Selector`.
 *
 * It contains an array of selector objects (comma-separated in the CSS) as well as the rules to be applied to the
 * matching elements.
 *
 * Declaration blocks usually appear directly inside a `Document` or another `CSSList` (mostly a `MediaQuery`).
 *
 * Note that `CSSListItem` extends both `Commentable` and `Renderable`, so those interfaces must also be implemented.
 */
class DeclarationBlock implements CSSElement, CSSListItem, Positionable, RuleContainer
{
    use CommentContainer;
    use Position;

    /**
     * @var list<Selector>
     */
    private $selectors = [];

    /**
     * @var RuleSet
     */
    private $ruleSet;

    /**
     * @param int<1, max>|null $lineNumber
     */
    public function __construct(?int $lineNumber = null)
    {
        $this->ruleSet = new RuleSet($lineNumber);
        $this->setPosition($lineNumber);
    }

    /**
     * @throws UnexpectedTokenException
     * @throws UnexpectedEOFException
     *
     * @internal since V8.8.0
     */
    public static function parse(ParserState $parserState, ?CSSList $list = null): ?DeclarationBlock
    {
        $comments = [];
        $result = new DeclarationBlock($parserState->currentLine());
        try {
            $selectors = self::parseSelectors($parserState, $list, $comments);
            $result->setSelectors($selectors, $list);
            if ($parserState->comes('{')) {
                $parserState->consume(1);
            }
        } catch (UnexpectedTokenException $e) {
            if ($parserState->getSettings()->usesLenientParsing()) {
                if (!$parserState->consumeIfComes('}')) {
                    $parserState->consumeUntil(['}', ParserState::EOF], false, true);
                }
                return null;
            } else {
                throw $e;
            }
        }
        $result->setComments($comments);

        RuleSet::parseRuleSet($parserState, $result->getRuleSet());

        return $result;
    }

    /**
     * @param array<Selector|string>|string $selectors
     *
     * @throws UnexpectedTokenException
     */
    public function setSelectors($selectors, ?CSSList $list = null): void
    {
        if (\is_array($selectors)) {
            $selectorsToSet = $selectors;
        } else {
            // A string of comma-separated selectors requires parsing.
            try {
                $parserState = new ParserState($selectors, Settings::create());
                $selectorsToSet = self::parseSelectors($parserState, $list);
                if (!$parserState->isEnd()) {
                    throw new UnexpectedTokenException('EOF', 'more');
                }
            } catch (UnexpectedTokenException $exception) {
                // The exception message from parsing may refer to the faux `{` block start token,
                // which would be confusing.
                // Rethrow with a more useful message, that also includes the selector(s) string that was passed.
                throw new UnexpectedTokenException(
                    'Selector(s) string is not valid.',
                    $selectors,
                    'custom'
                );
            }
        }

        // Convert all items to a `Selector` if not already
        foreach ($selectorsToSet as $key => $selector) {
            if (!($selector instanceof Selector)) {
                if ($list === null || !($list instanceof KeyFrame)) {
                    if (!Selector::isValid($selector)) {
                        throw new UnexpectedTokenException(
                            "Selector did not match '" . Selector::SELECTOR_VALIDATION_RX . "'.",
                            $selector,
                            'custom'
                        );
                    }
                    $selectorsToSet[$key] = new Selector($selector);
                } else {
                    if (!KeyframeSelector::isValid($selector)) {
                        throw new UnexpectedTokenException(
                            "Selector did not match '" . KeyframeSelector::SELECTOR_VALIDATION_RX . "'.",
                            $selector,
                            'custom'
                        );
                    }
                    $selectorsToSet[$key] = new KeyframeSelector($selector);
                }
            }
        }

        // Discard the keys and reindex the array
        $this->selectors = \array_values($selectorsToSet);
    }

    /**
     * Remove one of the selectors of the block.
     *
     * @param Selector|string $selectorToRemove
     */
    public function removeSelector($selectorToRemove): bool
    {
        if ($selectorToRemove instanceof Selector) {
            $selectorToRemove = $selectorToRemove->getSelector();
        }
        foreach ($this->selectors as $key => $selector) {
            if ($selector->getSelector() === $selectorToRemove) {
                unset($this->selectors[$key]);
                return true;
            }
        }
        return false;
    }

    /**
     * @return list<Selector>
     */
    public function getSelectors(): array
    {
        return $this->selectors;
    }

    public function getRuleSet(): RuleSet
    {
        return $this->ruleSet;
    }

    /**
     * @see RuleSet::addRule()
     */
    public function addRule(Rule $ruleToAdd, ?Rule $sibling = null): void
    {
        $this->ruleSet->addRule($ruleToAdd, $sibling);
    }

    /**
     * @return array<int<0, max>, Rule>
     *
     * @see RuleSet::getRules()
     */
    public function getRules(?string $searchPattern = null): array
    {
        return $this->ruleSet->getRules($searchPattern);
    }

    /**
     * @param array<Rule> $rules
     *
     * @see RuleSet::setRules()
     */
    public function setRules(array $rules): void
    {
        $this->ruleSet->setRules($rules);
    }

    /**
     * @return array<string, Rule>
     *
     * @see RuleSet::getRulesAssoc()
     */
    public function getRulesAssoc(?string $searchPattern = null): array
    {
        return $this->ruleSet->getRulesAssoc($searchPattern);
    }

    /**
     * @see RuleSet::removeRule()
     */
    public function removeRule(Rule $ruleToRemove): void
    {
        $this->ruleSet->removeRule($ruleToRemove);
    }

    /**
     * @see RuleSet::removeMatchingRules()
     */
    public function removeMatchingRules(string $searchPattern): void
    {
        $this->ruleSet->removeMatchingRules($searchPattern);
    }

    /**
     * @see RuleSet::removeAllRules()
     */
    public function removeAllRules(): void
    {
        $this->ruleSet->removeAllRules();
    }

    /**
     * @return non-empty-string
     *
     * @throws OutputException
     */
    public function render(OutputFormat $outputFormat): string
    {
        $formatter = $outputFormat->getFormatter();
        $result = $formatter->comments($this);
        if (\count($this->selectors) === 0) {
            // If all the selectors have been removed, this declaration block becomes invalid
            throw new OutputException(
                'Attempt to print declaration block with missing selector',
                $this->getLineNumber()
            );
        }
        $result .= $outputFormat->getContentBeforeDeclarationBlock();
        $result .= $formatter->implode(
            $formatter->spaceBeforeSelectorSeparator() . ',' . $formatter->spaceAfterSelectorSeparator(),
            $this->selectors
        );
        $result .= $outputFormat->getContentAfterDeclarationBlockSelectors();
        $result .= $formatter->spaceBeforeOpeningBrace() . '{';
        $result .= $this->ruleSet->render($outputFormat);
        $result .= '}';
        $result .= $outputFormat->getContentAfterDeclarationBlock();

        return $result;
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
     * @param list<Comment> $comments
     *
     * @return list<Selector>
     *
     * @throws UnexpectedTokenException
     */
    private static function parseSelectors(ParserState $parserState, ?CSSList $list, array &$comments = []): array
    {
        $selectorClass = $list instanceof KeyFrame ? KeyFrameSelector::class : Selector::class;
        $selectors = [];

        while (true) {
            $selectors[] = $selectorClass::parse($parserState, $comments);
            if (!$parserState->consumeIfComes(',')) {
                break;
            }
        }

        return $selectors;
    }
}
