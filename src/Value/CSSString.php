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
    public function __construct($string, int $lineNumber = 0)
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
        $begin = $parserState->peek();
        $quote = null;
        if ($begin === "'") {
            $quote = "'";
        } elseif ($begin === '"') {
            $quote = '"';
        }
        if ($quote !== null) {
            $parserState->consume($quote);
        }
        $result = '';
        $content = null;
        if ($quote === null) {
            // Unquoted strings end in whitespace or with braces, brackets, parentheses
            while (\preg_match('/[\\s{}()<>\\[\\]]/isu', $parserState->peek()) !== 1) {
                $result .= $parserState->parseCharacter(false);
            }
        } else {
            while (!$parserState->comes($quote)) {
                $content = $parserState->parseCharacter(false);
                if ($content === null) {
                    throw new SourceException(
                        "Non-well-formed quoted string {$parserState->peek(3)}",
                        $parserState->currentLine()
                    );
                }
                $result .= $content;
            }
            $parserState->consume($quote);
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
