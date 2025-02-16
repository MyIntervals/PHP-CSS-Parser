<?php

declare(strict_types=1);

namespace Sabberworm\CSS;

use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;

/**
 * This class parses CSS from text into a data structure.
 */
class Parser
{
    /**
     * @var ParserState
     */
    private $parserState;

    /**
     * @param string $sText the complete CSS as text (i.e., usually the contents of a CSS file)
     * @param int<0, max> $lineNumber the line number (starting from 1, not from 0)
     */
    public function __construct($sText, ?Settings $parserSettings = null, $lineNumber = 1)
    {
        if ($parserSettings === null) {
            $parserSettings = Settings::create();
        }
        $this->parserState = new ParserState($sText, $parserSettings, $lineNumber);
    }

    /**
     * Parses the CSS provided to the constructor and creates a `Document` from it.
     *
     * @throws SourceException
     */
    public function parse(): Document
    {
        return Document::parse($this->parserState);
    }
}
