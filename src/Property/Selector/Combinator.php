<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property\Selector;

use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\ShortClassNameProvider;

/**
 * Class representing a CSS selector combinator (space, `>`, `+`, or `~`).
 */
class Combinator implements Component, Renderable
{
    use ShortClassNameProvider;

    /**
     * @var ' '|'>'|'+'|'~'
     */
    private $value;

    /**
     * @param ' '|'>'|'+'|'~' $value
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
     * @return ' '|'>'|'+'|'~'
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param non-empty-string $value
     *
     * @throws \UnexpectedValueException
     */
    public function setValue(string $value): void
    {
        // Allow extra and other whitespace even if not publicly documented.
        $trimmedValue = \trim($value);

        // But reject anything else that is not a combinator.
        if (!\in_array($trimmedValue, ['', '>', '+', '~'], true)) {
            throw new \UnexpectedValueException('`' . $trimmedValue . '` is not a valid selector combinator.');
        }

        $this->value = $trimmedValue !== '' ? $trimmedValue : ' ';
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
        // TODO: allow optional spacing controlled via OutputFormat
        return $this->getValue();
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
