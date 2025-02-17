<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;

/**
 * This class represents URLs in CSS. `URL`s always output in `URL("")` notation.
 */
class URL extends PrimitiveValue
{
    /**
     * @var CSSString
     */
    private $oURL;

    /**
     * @param int<0, max> $lineNumber
     */
    public function __construct(CSSString $oURL, $lineNumber = 0)
    {
        parent::__construct($lineNumber);
        $this->oURL = $oURL;
    }

    /**
     * @throws SourceException
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     *
     * @internal since V8.8.0
     */
    public static function parse(ParserState $parserState): URL
    {
        $oAnchor = $parserState->anchor();
        $identifier = '';
        for ($i = 0; $i < 3; $i++) {
            $sChar = $parserState->parseCharacter(true);
            if ($sChar === null) {
                break;
            }
            $identifier .= $sChar;
        }
        $bUseUrl = $parserState->streql($identifier, 'url');
        if ($bUseUrl) {
            $parserState->consumeWhiteSpace();
            $parserState->consume('(');
        } else {
            $oAnchor->backtrack();
        }
        $parserState->consumeWhiteSpace();
        $result = new URL(CSSString::parse($parserState), $parserState->currentLine());
        if ($bUseUrl) {
            $parserState->consumeWhiteSpace();
            $parserState->consume(')');
        }
        return $result;
    }

    public function setURL(CSSString $oURL): void
    {
        $this->oURL = $oURL;
    }

    /**
     * @return CSSString
     */
    public function getURL()
    {
        return $this->oURL;
    }

    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $outputFormat): string
    {
        return "url({$this->oURL->render($outputFormat)})";
    }
}
