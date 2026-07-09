<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property\Selector;

use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\ShortClassNameProvider;

/**
 * Class representing a CSS selector combinator (space, `>`, `+`, or `~`).
 *
 * @phpstan-type ValidCombinatorValue ' '|'>'|'+'|'~'
 */
class Combinator implements Component
{
    use ShortClassNameProvider;

    /**
     * @var ValidCombinatorValue
     */
    private $value;

    /**
     * @param ValidCombinatorValue $value
     */
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
        $consumedWhitespace = $parserState->consumeWhiteSpace($comments);

        $nextToken = $parserState->peek();
        if (\in_array($nextToken, ['>', '+', '~'], true)) {
            $value = $nextToken;
            $parserState->consume(1);
            $parserState->consumeWhiteSpace($comments);
        } elseif ($consumedWhitespace !== '') {
            $value = ' ';
        } else {
            throw new UnexpectedTokenException(
                'combinator',
                $nextToken,
                'literal',
                $parserState->currentLine()
            );
        }

        return new self($value);
    }

    /**
     * @return ValidCombinatorValue
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param non-empty-string $value
     *
     * @throws \UnexpectedValueException if `$value` is not either space, '>', '+' or '~'
     */
    public function setValue(string $value): void
    {
        if (!\in_array($value, [' ', '>', '+', '~'], true)) {
            throw new \UnexpectedValueException('`' . $value . '` is not a valid selector combinator.');
        }

        $this->value = $value;
    }

    /**
     * @return int<0, max>
     */
    public function getSpecificity(): int
    {
        return 0;
    }

    public function render(OutputFormat $outputFormat): string
    {
        $spacing = $outputFormat->getSpaceAroundSelectorCombinator();
        if ($this->value === ' ') {
            $rendering = $spacing !== '' ? $spacing : ' ';
        } else {
            $rendering = $spacing . $this->value . $spacing;
        }

        return $rendering;
    }

    /**
     * @return array<string, bool|int|float|string|array<mixed>|null>
     *
     * @internal
     */
    public function getArrayRepresentation(): array
    {
        return [
            'class' => $this->getShortClassName(),
            'value' => $this->value,
        ];
    }
}
