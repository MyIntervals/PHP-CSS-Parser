<?php

namespace Sabberworm\CSS;

use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * This class parses CSS from text into a data structure.
 */
class Parser implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ParserState
     */
    private $oParserState;

    /**
     * @param string $sText the complete CSS as text (i.e., usually the contents of a CSS file)
     * @param Settings|null $oParserSettings
     * @param int $iLineNo the line number (starting from 1, not from 0)
     */
    public function __construct($sText, Settings $oParserSettings = null, $iLineNo = 1)
    {
        $this->logger = new NullLogger();

        if ($oParserSettings === null) {
            $oParserSettings = Settings::create();
        }
        $this->oParserState = new ParserState($sText, $oParserSettings, $iLineNo);
    }

    /**
     * Sets the charset to be used if the CSS does not contain an `@charset` declaration.
     *
     * @param string $sCharset
     */
    public function setCharset($sCharset): void
    {
        $this->oParserState->setCharset($sCharset);
    }

    /**
     * Returns the charset that is used if the CSS does not contain an `@charset` declaration.
     */
    public function getCharset(): void
    {
        // Note: The `return` statement is missing here. This is a bug that needs to be fixed.
        $this->oParserState->getCharset();
    }

    /**
     * Parses the CSS provided to the constructor and creates a `Document` from it.
     *
     * @throws SourceException
     */
    public function parse(): Document
    {
        return Document::parse($this->oParserState, $this->logger);
    }
}
