<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;

class LineName extends ValueList
{
    /**
     * @param array<int, Value|string> $aComponents
     * @param int $lineNumber
     *
     * @internal since 8.8.0
     */
    public function __construct(array $aComponents = [], $lineNumber = 0)
    {
        parent::__construct($aComponents, ' ', $lineNumber);
    }

    /**
     * @throws UnexpectedTokenException
     * @throws UnexpectedEOFException
     */
    public static function parse(ParserState $parserState): LineName
    {
        $parserState->consume('[');
        $parserState->consumeWhiteSpace();
        $aNames = [];
        do {
            if ($parserState->getSettings()->bLenientParsing) {
                try {
                    $aNames[] = $parserState->parseIdentifier();
                } catch (UnexpectedTokenException $e) {
                    if (!$parserState->comes(']')) {
                        throw $e;
                    }
                }
            } else {
                $aNames[] = $parserState->parseIdentifier();
            }
            $parserState->consumeWhiteSpace();
        } while (!$parserState->comes(']'));
        $parserState->consume(']');
        return new LineName($aNames, $parserState->currentLine());
    }

    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $oOutputFormat): string
    {
        return '[' . parent::render(OutputFormat::createCompact()) . ']';
    }
}
