<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;

/**
 * A `CSSFunction` represents a special kind of value that also contains a function name and where the values are the
 * function’s arguments. It also handles equals-sign-separated argument lists like `filter: alpha(opacity=90);`.
 */
class CSSFunction extends ValueList
{
    /**
     * @var string
     *
     * @internal since 8.8.0
     */
    protected $sName;

    /**
     * @param string $sName
     * @param RuleValueList|array<array-key, Value|string> $aArguments
     * @param string $sSeparator
     * @param int<0, max> $lineNumber
     */
    public function __construct($sName, $aArguments, $sSeparator = ',', $lineNumber = 0)
    {
        if ($aArguments instanceof RuleValueList) {
            $sSeparator = $aArguments->getListSeparator();
            $aArguments = $aArguments->getListComponents();
        }
        $this->sName = $sName;
        $this->lineNumber = $lineNumber;
        parent::__construct($aArguments, $sSeparator, $lineNumber);
    }

    /**
     * @throws SourceException
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     *
     * @internal since V8.8.0
     */
    public static function parse(ParserState $parserState, bool $ignoreCase = false): CSSFunction
    {
        $sName = self::parseName($parserState, $ignoreCase);
        $parserState->consume('(');
        $mArguments = self::parseArguments($parserState);

        $result = new CSSFunction($sName, $mArguments, ',', $parserState->currentLine());
        $parserState->consume(')');

        return $result;
    }

    /**
     * @throws SourceException
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    private static function parseName(ParserState $parserState, bool $ignoreCase = false): string
    {
        return $parserState->parseIdentifier($ignoreCase);
    }

    /**
     * @return Value|string
     *
     * @throws SourceException
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    private static function parseArguments(ParserState $parserState)
    {
        return Value::parseValue($parserState, ['=', ' ', ',']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->sName;
    }

    /**
     * @param string $sName
     */
    public function setName($sName): void
    {
        $this->sName = $sName;
    }

    /**
     * @return array<array-key, Value|string>
     */
    public function getArguments()
    {
        return $this->aComponents;
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
        $aArguments = parent::render($outputFormat);
        return "{$this->sName}({$aArguments})";
    }
}
