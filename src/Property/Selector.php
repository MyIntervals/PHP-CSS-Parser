<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property;

use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Property\Selector\SpecificityCalculator;
use Sabberworm\CSS\Renderable;

use function Sabberworm\CSS\Safe\preg_match;
use function Sabberworm\CSS\Safe\preg_replace;

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

    final public function __construct(string $selector)
    {
        $this->setSelector($selector);
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
        $selectorParts = [];
        $stringWrapperCharacter = null;
        $functionNestingLevel = 0;
        static $stopCharacters = ['{', '}', '\'', '"', '(', ')', ',', ParserState::EOF, ''];

        while (true) {
            $selectorParts[] = $parserState->consumeUntil($stopCharacters, false, false, $comments);
            $nextCharacter = $parserState->peek();
            switch ($nextCharacter) {
                case '':
                    // EOF
                    break 2;
                case '\'':
                    // The fallthrough is intentional.
                case '"':
                    if (!\is_string($stringWrapperCharacter)) {
                        $stringWrapperCharacter = $nextCharacter;
                    } elseif ($stringWrapperCharacter === $nextCharacter) {
                        if (\substr(\end($selectorParts), -1) !== '\\') {
                            $stringWrapperCharacter = null;
                        }
                    }
                    break;
                case '(':
                    if (!\is_string($stringWrapperCharacter)) {
                        ++$functionNestingLevel;
                    }
                    break;
                case ')':
                    if (!\is_string($stringWrapperCharacter)) {
                        if ($functionNestingLevel <= 0) {
                            throw new UnexpectedTokenException(
                                'anything but',
                                ')',
                                'literal',
                                $parserState->currentLine()
                            );
                        }
                        --$functionNestingLevel;
                    }
                    break;
                case ',':
                    if (!\is_string($stringWrapperCharacter) && $functionNestingLevel === 0) {
                        break 2;
                    }
                    break;
                case '{':
                    // The fallthrough is intentional.
                case '}':
                    if (!\is_string($stringWrapperCharacter)) {
                        break 2;
                    }
                    break;
                default:
                    // This will never happen unless something gets broken in `ParserState`.
                    throw new \UnexpectedValueException(
                        'Unexpected character \'' . $nextCharacter
                        . '\' returned from `ParserState::peek()` in `Selector::parse()`'
                    );
            }
            $selectorParts[] = $parserState->consume(1);
        }

        if ($functionNestingLevel !== 0) {
            throw new UnexpectedTokenException(')', $nextCharacter, 'literal', $parserState->currentLine());
        }
        if (\is_string($stringWrapperCharacter)) {
            throw new UnexpectedTokenException(
                $stringWrapperCharacter,
                $nextCharacter,
                'literal',
                $parserState->currentLine()
            );
        }

        $selector = \trim(\implode('', $selectorParts));
        if ($selector === '') {
            throw new UnexpectedTokenException('selector', $nextCharacter, 'literal', $parserState->currentLine());
        }
        if (!self::isValid($selector)) {
            throw new UnexpectedTokenException(
                "Selector did not match '" . static::SELECTOR_VALIDATION_RX . "'.",
                $selector,
                'custom',
                $parserState->currentLine()
            );
        }

        return new static($selector);
    }

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function setSelector(string $selector): void
    {
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
