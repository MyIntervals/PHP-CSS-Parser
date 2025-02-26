<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Parsing;

/**
 * @internal since 8.7.0
 */
class Anchor
{
    /**
     * @var int
     */
    private $position;

    /**
     * @var ParserState
     */
    private $parserState;

    public function __construct(int $position, ParserState $parserState)
    {
        $this->position = $position;
        $this->parserState = $parserState;
    }

    public function backtrack(): void
    {
        $this->parserState->setPosition($this->position);
    }
}
