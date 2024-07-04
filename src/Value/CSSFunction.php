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
     * @param RuleValueList|array<int, Value|string> $aArguments
     * @param string $sSeparator
     * @param int $iLineNo
     */
    public function __construct($sName, $aArguments, $sSeparator = ',', $iLineNo = 0)
    {
        if ($aArguments instanceof RuleValueList) {
            $sSeparator = $aArguments->getListSeparator();
            $aArguments = $aArguments->getListComponents();
        }
        $this->sName = $sName;
        $this->iLineNo = $iLineNo;
        parent::__construct($aArguments, $sSeparator, $iLineNo);
    }

    /**
     * @throws SourceException
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    public static function parse(ParserState $oParserState, bool $bIgnoreCase = false): CSSFunction
    {
        $sName = self::parseName($oParserState, $bIgnoreCase);
        $oParserState->consume('(');
        $mArguments = self::parseArguments($oParserState);

        $oResult = new CSSFunction($sName, $mArguments, ',', $oParserState->currentLine());
        $oParserState->consume(')');

        return $oResult;
    }

    /**
     * @throws SourceException
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    private static function parseName(ParserState $oParserState, bool $bIgnoreCase = false): string
    {
        return $oParserState->parseIdentifier($bIgnoreCase);
    }

    /**
     * @return Value|string
     *
     * @throws SourceException
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    private static function parseArguments(ParserState $oParserState)
    {
        return Value::parseValue($oParserState, ['=', ' ', ',']);
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
     * @return array<int, Value|string>
     */
    public function getArguments()
    {
        return $this->aComponents;
    }

    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    /**
     * @return string
     */
    public function render(OutputFormat $oOutputFormat)
    {
        $aArguments = parent::render($oOutputFormat);
        return "{$this->sName}({$aArguments})";
    }
}
