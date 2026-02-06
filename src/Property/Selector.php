<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property;

use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Property\Selector\Combinator;
use Sabberworm\CSS\Property\Selector\Component;
use Sabberworm\CSS\Property\Selector\CompoundSelector;
use Sabberworm\CSS\Property\Selector\SpecificityCalculator;
use Sabberworm\CSS\Renderable;

use function Safe\preg_match;
use function Safe\preg_replace;

/**
 * Class representing a single CSS selector. Selectors have to be split by the comma prior to being passed into this
 * class.
 */
class Selector implements Renderable
{
    /**
     * @internal since 8.5.2
     */
    public const SELECTOR_VALIDATION_RX = '/
        ^(
            (?:
                # any sequence of valid unescaped characters, except quotes
                [a-zA-Z0-9\\x{00A0}-\\x{FFFF}_^$|*=~\\[\\]()\\-\\s\\.:#+>,]++
                |
                # one or more escaped characters
                (?:\\\\.)++
                |
                # quoted text, like in `[id="example"]`
                (?:
                    # opening quote
                    ([\'"])
                    (?:
                        # sequence of characters except closing quote or backslash
                        (?:(?!\\g{-1}|\\\\).)++
                        |
                        # one or more escaped characters
                        (?:\\\\.)++
                    )*+ # zero or more times
                    # closing quote or end (unmatched quote is currently allowed)
                    (?:\\g{-1}|$)
                )
            )*+ # zero or more times
        )$
        /ux';

    /**
     * @var string
     */
    private $selector;

    /**
     * @internal since V8.8.0
     */
    public static function isValid(string $selector): bool
    {
        // Note: We need to use `static::` here as the constant is overridden in the `KeyframeSelector` class.
        $numberOfMatches = preg_match(static::SELECTOR_VALIDATION_RX, $selector);

        return $numberOfMatches === 1;
    }

    /**
     * @throws \UnexpectedValueException if the selector is not valid
     */
    final public function __construct(string $selector)
    {
        $this->setSelector($selector);
    }

    /**
     * @param list<Comment> $comments
     *
     * @return list<Component>
     *
     * @throws UnexpectedTokenException
     */
    private static function parseComponents(ParserState $parserState, array &$comments = []): array
    {
        // Whitespace is a descendent combinator, not allowed around a compound selector.
        // (It is allowed within, e.g. as part of a string or within a function like `:not()`.)
        // Gobble any up now to get a clean start.
        $parserState->consumeWhiteSpace($comments);

        $selectorParts = [];
        while (true) {
            try {
                $selectorParts[] = CompoundSelector::parse($parserState, $comments);
            } catch (UnexpectedTokenException $e) {
                if ($selectorParts !== [] && \end($selectorParts)->getValue() === ' ') {
                    // The whitespace was not a descendent combinator, and was, in fact, arbitrary,
                    // after the end of the selector.  Discard it.
                    \array_pop($selectorParts);
                    break;
                } else {
                    throw $e;
                }
            }
            try {
                $selectorParts[] = Combinator::parse($parserState, $comments);
            } catch (UnexpectedTokenException $e) {
                // End of selector has been reached.
                break;
            }
        }

        return $selectorParts;
    }

    /**
     * @param list<Comment> $comments
     *
     * @throws UnexpectedTokenException
     *
     * @internal
     */
    public static function parse(ParserState $parserState, array &$comments = []): self
    {
        $selectorParts = self::parseComponents($parserState, $comments);

        // Check that the selector has been fully parsed:
        if (!\in_array($parserState->peek(), ['{', '}', ',', ''], true)) {
            throw new UnexpectedTokenException(
                '`,`, `{`, `}` or EOF',
                $parserState->peek(5),
                'literal',
                $parserState->currentLine()
            );
        }

        $selectorString = '';
        foreach ($selectorParts as $selectorPart) {
            $selectorPartValue = $selectorPart->getValue();
            if (\in_array($selectorPartValue, ['>', '+', '~'], true)) {
                $selectorString .= ' ' . $selectorPartValue . ' ';
            } else {
                $selectorString .= $selectorPartValue;
            }
        }

        return new static($selectorString);
    }

    public function getSelector(): string
    {
        return $this->selector;
    }

    /**
     * @throws \UnexpectedValueException if the selector is not valid
     */
    public function setSelector(string $selector): void
    {
        if (!self::isValid($selector)) {
            throw new \UnexpectedValueException("Selector `$selector` is not valid.");
        }

        $selector = \trim($selector);

        $hasAttribute = \strpos($selector, '[') !== false;

        // Whitespace can't be adjusted within an attribute selector, as it would change its meaning
        $this->selector = !$hasAttribute ? preg_replace('/\\s++/', ' ', $selector) : $selector;
    }

    /**
     * @return int<0, max>
     */
    public function getSpecificity(): int
    {
        return SpecificityCalculator::calculate($this->selector);
    }

    public function render(OutputFormat $outputFormat): string
    {
        return $this->getSelector();
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
}
