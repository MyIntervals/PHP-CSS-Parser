<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property\Selector;

use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Renderable;

/**
 * Class representing a CSS selector combinator (space, `>`, `+`, `~`).
 */
class Combinator implements Renderable, SelectorComponent
{
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

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $trimmedValue = \trim($value);

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
}
