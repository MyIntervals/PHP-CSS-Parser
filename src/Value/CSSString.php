<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\ShortClassNameProvider;

use function Safe\preg_match;

/**
 * This class is a wrapper for quoted strings to distinguish them from keywords.
 *
 * `CSSString`s always output with double quotes.
 */
class CSSString extends PrimitiveValue
{
    use ShortClassNameProvider;

    /**
     * @var string
     */
    private $string;

    /**
     * @param int<1, max>|null $lineNumber
     */
    public function __construct(string $string, ?int $lineNumber = null)
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
            while (preg_match('/[\\s{}()<>\\[\\]]/isu', $parserState->peek()) === 0) {
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

    public function setString(string $string): void
    {
        $this->string = $string;
    }

    public function getString(): string
    {
        return $this->string;
    }

    /**
     * @return non-empty-string
     */
    public function render(OutputFormat $outputFormat): string
    {
        return $outputFormat->getStringQuotingType()
            . $this->escape($this->string, $outputFormat)
            . $outputFormat->getStringQuotingType();
    }

    /**
     * @return array<string, bool|int|float|string|array<mixed>|null>
     *
     * @internal
     */
    public function getArrayRepresentation(): array
    {
        return [
            'class' => $this->getShortClassName(),
            // We're using the term "contents" here to make the difference to the class more clear.
            'contents' => $this->string,
        ];
    }

    private function escape(string $string, OutputFormat $outputFormat): string
    {
        $charactersToEscape = '\\';
        $charactersToEscape .= ($outputFormat->getStringQuotingType() === '"' ? '"' : "'");
        $withEscapedQuotes = \addcslashes($string, $charactersToEscape);

        $withNewlineEncoded = \str_replace("\n", '\\A', $withEscapedQuotes);

        return $withNewlineEncoded;
    }
}
