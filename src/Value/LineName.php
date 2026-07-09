<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;

/**
 * A name for a named CSS grid line.
 *
 * @see https://www.w3.org/TR/css-grid-1/#line-name
 * @see https://developer.mozilla.org/en-US/docs/Web/CSS/Guides/Grid_layout/Named_grid_lines
 */
class LineName extends ValueList
{
    /**
     * @param array<string> $components
     * @param int<1, max>|null $lineNumber
     */
    public function __construct(array $components = [], ?int $lineNumber = null)
    {
        parent::__construct($components, ' ', $lineNumber);
    }

    /**
     * @throws UnexpectedTokenException
     * @throws UnexpectedEOFException
     *
     * @internal since V8.8.0
     */
    public static function parse(ParserState $parserState): LineName
    {
        $parserState->consume('[');
        $parserState->consumeWhiteSpace();
        $names = [];
        do {
            if ($parserState->getSettings()->usesLenientParsing()) {
                try {
                    $names[] = $parserState->parseIdentifier();
                } catch (UnexpectedTokenException $e) {
                    if (!$parserState->comes(']')) {
                        throw $e;
                    }
                }
            } else {
                $names[] = $parserState->parseIdentifier();
            }
            $parserState->consumeWhiteSpace();
        } while (!$parserState->comes(']'));
        $parserState->consume(']');
        return new LineName($names, $parserState->currentLine());
    }

    /**
     * @return non-empty-string
     */
    public function render(OutputFormat $outputFormat): string
    {
        return '[' . parent::render(OutputFormat::createCompact()) . ']';
    }
}
