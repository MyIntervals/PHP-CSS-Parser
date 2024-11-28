<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;

/**
 * This class is a wrapper for quoted strings to distinguish them from keywords.
 *
 * `CSSString`s always output with double quotes.
 */
class CSSString extends PrimitiveValue
{
    const PARSE_QUOTE_STRINGS = ['"', "'", '\"', "\'"];

    /**
     * @var string
     */
    private $sString;

    /**
     * @param string $sString
     * @param int $iLineNo
     */
    public function __construct($sString, $iLineNo = 0)
    {
        $this->sString = $sString;
        parent::__construct($iLineNo);
    }

    /**
     * @throws SourceException
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    public static function parse(ParserState $oParserState): CSSString
    {
        $sBegin = $oParserState->peek();
        if ($sBegin === '\\') {
            $sBegin = $oParserState->peek(2);
        }
        $sQuote = null;
        if (in_array($sBegin, self::PARSE_QUOTE_STRINGS)) {
            $sQuote = $sBegin;
        }
        if ($sQuote !== null) {
            $oParserState->consume($sQuote);
        }
        $sResult = '';
        $sContent = null;
        if ($sQuote === null) {
            // Unquoted strings end in whitespace or with braces, brackets, parentheses
            while (\preg_match('/[\\s{}()<>\\[\\]]/isu', $oParserState->peek()) !== 1) {
                $sResult .= $oParserState->parseCharacter(false);
            }
        } else {
            while (!$oParserState->comes($sQuote)) {
                $sContent = $oParserState->parseCharacter(false);
                if ($sContent === null) {
                    throw new SourceException(
                        "Non-well-formed quoted string {$oParserState->peek(3)}",
                        $oParserState->currentLine()
                    );
                }
                $sResult .= $sContent;
            }
            $oParserState->consume($sQuote);
        }
        return new CSSString($sResult, $oParserState->currentLine());
    }

    /**
     * @param string $sString
     */
    public function setString($sString): void
    {
        $this->sString = $sString;
    }

    /**
     * @return string
     */
    public function getString()
    {
        return $this->sString;
    }

    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $oOutputFormat): string
    {
        $sString = \addslashes($this->sString);
        $sString = \str_replace("\n", '\\A', $sString);
        return $oOutputFormat->getStringQuotingType() . $sString . $oOutputFormat->getStringQuotingType();
    }
}
