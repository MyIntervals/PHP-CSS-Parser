<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;

/**
 * An `Expression` represents a special kind of value that is comprised of multiple components wrapped in parenthesis.
 * Examle `height: (vh - 10);`.
 */
class Expression extends CSSFunction
{
    /**
     * @throws SourceException
     */
    public static function parse(ParserState $oParserState, bool $bIgnoreCase = false): CSSFunction
    {
        $oParserState->consume('(');
        $aArguments = parent::parseArguments($oParserState);
        $mResult = new Expression('', $aArguments, ',', $oParserState->currentLine());
        $oParserState->consume(')');
        return $mResult;
    }
}
