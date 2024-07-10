<?php

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;

/**
 * An `Expression` represents a special kind of value that is comprised of multiple components wrapped in parenthesis.
 * Examle `height: (vh - 10);`.
 */
class Expression extends CSSFunction
{
    /**
     * @throws SourceException
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    public static function parse(ParserState $oParserState, bool $bIgnoreCase = false): Expression
    {
        $oParserState->consume('(');
        $aArguments = parent::parseArguments($oParserState);
        $mResult = new Expression("", $aArguments, ',', $oParserState->currentLine());
        $oParserState->consume(')');
        return $mResult;
    }
}
