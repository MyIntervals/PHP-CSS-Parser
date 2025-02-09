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
    private $iPosition;

    /**
     * @var ParserState
     */
    private $parserState;

    /**
     * @param int $iPosition
     *
     * @internal since 8.8.0
     */
    public function __construct($iPosition, ParserState $parserState)
    {
        $this->iPosition = $iPosition;
        $this->parserState = $parserState;
    }

    public function backtrack(): void
    {
        $this->parserState->setPosition($this->iPosition);
    }
}
