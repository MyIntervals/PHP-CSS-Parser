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
    private $oParserState;

    /**
     * @param int $iPosition
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
