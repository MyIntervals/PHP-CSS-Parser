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
    /**
     * @var string
     */
    private $string;

    /**
     * @param string $string
     * @param int<0, max> $lineNumber
     */
    public function __construct($string, $lineNumber = 0)
    {
        $this->string = $string;
        parent::__construct($lineNumber);
    }

    /**
     * @throws SourceException
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     *
     * @internal since V8.8.0
     */
    public static function parse(ParserState $parserState): CSSString
    {
        $sBegin = $parserState->peek();
        $sQuote = null;
        if ($sBegin === "'") {
            $sQuote = "'";
        } elseif ($sBegin === '"') {
            $sQuote = '"';
        }
        if ($sQuote !== null) {
            $parserState->consume($sQuote);
        }
        $result = '';
        $sContent = null;
        if ($sQuote === null) {
            // Unquoted strings end in whitespace or with braces, brackets, parentheses
            while (\preg_match('/[\\s{}()<>\\[\\]]/isu', $parserState->peek()) !== 1) {
                $result .= $parserState->parseCharacter(false);
            }
        } else {
            while (!$parserState->comes($sQuote)) {
                $sContent = $parserState->parseCharacter(false);
                if ($sContent === null) {
                    throw new SourceException(
                        "Non-well-formed quoted string {$parserState->peek(3)}",
                        $parserState->currentLine()
                    );
                }
                $result .= $sContent;
            }
            $parserState->consume($sQuote);
        }
        return new CSSString($result, $parserState->currentLine());
    }

    /**
     * @param string $string
     */
    public function setString($string): void
    {
        $this->string = $string;
    }

    /**
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * @deprecated in V8.8.0, will be removed in V9.0.0. Use `render` instead.
     */
    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $outputFormat): string
    {
        $string = \addslashes($this->string);
        $string = \str_replace("\n", '\\A', $string);
        return $outputFormat->getStringQuotingType() . $string . $outputFormat->getStringQuotingType();
    }
}
