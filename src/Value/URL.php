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
    private $url;

    /**
     * @param int<0, max> $lineNumber
     */
    public function __construct(CSSString $url, $lineNumber = 0)
    {
        parent::__construct($lineNumber);
        $this->url = $url;
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
        $anchor = $parserState->anchor();
        $identifier = '';
        for ($i = 0; $i < 3; $i++) {
            $character = $parserState->parseCharacter(true);
            if ($character === null) {
                break;
            }
            $identifier .= $character;
        }
        $bUseUrl = $parserState->streql($identifier, 'url');
        if ($bUseUrl) {
            $parserState->consumeWhiteSpace();
            $parserState->consume('(');
        } else {
            $anchor->backtrack();
        }
        $parserState->consumeWhiteSpace();
        $result = new URL(CSSString::parse($parserState), $parserState->currentLine());
        if ($bUseUrl) {
            $parserState->consumeWhiteSpace();
            $parserState->consume(')');
        }
        return $result;
    }

    public function setURL(CSSString $url): void
    {
        $this->url = $url;
    }

    /**
     * @return CSSString
     */
    public function getURL()
    {
        return $this->url;
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
        return "url({$this->url->render($outputFormat)})";
    }
}
