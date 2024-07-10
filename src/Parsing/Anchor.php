<?php

namespace Sabberworm\CSS\Parsing;

class Anchor
{
    /**
     * @var int
     */
    private $iPosition;

    /**
     * @var \Sabberworm\CSS\Parsing\ParserState
     */
    private $oParserState;

    /**
     * @param int $iPosition
     * @param \Sabberworm\CSS\Parsing\ParserState $oParserState
     */
    public function __construct($iPosition, ParserState $oParserState)
    {
        $this->iPosition = $iPosition;
        $this->oParserState = $oParserState;
    }

    public function backtrack(): void
    {
        $this->oParserState->setPosition($this->iPosition);
    }
}
