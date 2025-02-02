<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;

/**
 * A `CSSFunction` represents a special kind of value that also contains a function name and where the values are the
 * function’s arguments. It also handles equals-sign-separated argument lists like `filter: alpha(opacity=90);`.
 */
class CSSFunction extends ValueList
{
    /**
     * @var string
     */
    protected $sName;

    /**
     * @param string $sName
     * @param RuleValueList|array<array-key, Value|string> $aArguments
     * @param string $sSeparator
     * @param int $lineNumber
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
     */
    public static function parse(ParserState $parserState, bool $bIgnoreCase = false): CSSFunction
    {
        $sName = self::parseName($parserState, $bIgnoreCase);
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
    private static function parseName(ParserState $parserState, bool $bIgnoreCase = false): string
    {
        return $parserState->parseIdentifier($bIgnoreCase);
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

    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $oOutputFormat): string
    {
        $aArguments = parent::render($oOutputFormat);
        return "{$this->sName}({$aArguments})";
    }
}
