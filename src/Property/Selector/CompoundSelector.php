<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property\Selector;

use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Renderable;

use function Safe\preg_match;

/**
 * Class representing a CSS compound selector.
 * Selectors have to be split at combinators (space, `>`, `+`, `~`) before being passed to this class.
 */
class CompoundSelector implements Renderable, SelectorComponent
{
    private const SELECTOR_VALIDATION_RX = '/
        ^(
            (?:
                # any sequence of valid unescaped characters, except quotes
                [a-zA-Z0-9\\x{00A0}-\\x{FFFF}_^$|*=\\[\\]()\\-\\s\\.:#,]++
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
            |
            # keyframe animation progress percentage (e.g. 50%), untrimmed
            \\s*+(?:\\d++%)\\s*+
        )$
        /ux';

    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
    {
        $this->setValue($value);
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
        static $stopCharacters = [
            '{',
            '}',
            '\'',
            '"',
            '(',
            ')',
            ',',
            ' ',
            "\t",
            "\n",
            "\r",
            '>',
            '+',
            '~',
            ParserState::EOF,
            '',
        ];

        $parserState->consumeWhiteSpace($comments);

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
                case '{':
                    // The fallthrough is intentional.
                case '}':
                    if (!\is_string($stringWrapperCharacter)) {
                        break 2;
                    }
                    break;
                case ',':
                    // The fallthrough is intentional.
                case ' ':
                    // The fallthrough is intentional.
                case "\t":
                    // The fallthrough is intentional.
                case "\n":
                    // The fallthrough is intentional.
                case "\r":
                    // The fallthrough is intentional.
                case '>':
                    // The fallthrough is intentional.
                case '+':
                    // The fallthrough is intentional.
                case '~':
                    if (!\is_string($stringWrapperCharacter) && $functionNestingLevel === 0) {
                        break 2;
                    }
                    break;
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

        $value = \trim(\implode('', $selectorParts));
        if ($value === '') {
            throw new UnexpectedTokenException('selector', $nextCharacter, 'literal', $parserState->currentLine());
        }
        if (!self::isValid($value)) {
            throw new UnexpectedTokenException(
                "Selector did not match '" . self::SELECTOR_VALIDATION_RX . "'.",
                $value,
                'custom',
                $parserState->currentLine()
            );
        }

        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = \trim($value);
    }

    /**
     * @return int<0, max>
     */
    public function getSpecificity(): int
    {
        return SpecificityCalculator::calculate($this->value);
    }

    public function render(OutputFormat $outputFormat): string
    {
        return $this->getValue();
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

    private static function isValid(string $value): bool
    {
        $numberOfMatches = preg_match(self::SELECTOR_VALIDATION_RX, $value);

        return $numberOfMatches === 1;
    }
}
