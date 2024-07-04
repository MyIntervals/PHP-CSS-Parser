<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;

class CalcFunction extends CSSFunction
{
    /**
     * @var int
     */
    private const T_OPERAND = 1;

    /**
     * @var int
     */
    private const T_OPERATOR = 2;

    /**
     * @throws UnexpectedTokenException
     * @throws UnexpectedEOFException
     */
    public static function parse(ParserState $oParserState, bool $bIgnoreCase = false): CSSFunction
    {
        $aOperators = ['+', '-', '*', '/'];
        $sFunction = $oParserState->parseIdentifier();
        if ($oParserState->peek() != '(') {
            // Found ; or end of line before an opening bracket
            throw new UnexpectedTokenException('(', $oParserState->peek(), 'literal', $oParserState->currentLine());
        } elseif (!\in_array($sFunction, ['calc', '-moz-calc', '-webkit-calc'], true)) {
            // Found invalid calc definition. Example calc (...
            throw new UnexpectedTokenException('calc', $sFunction, 'literal', $oParserState->currentLine());
        }
        $oParserState->consume('(');
        $oCalcList = new CalcRuleValueList($oParserState->currentLine());
        $oList = new RuleValueList(',', $oParserState->currentLine());
        $iNestingLevel = 0;
        $iLastComponentType = null;
        while (!$oParserState->comes(')') || $iNestingLevel > 0) {
            if ($oParserState->isEnd() && $iNestingLevel === 0) {
                break;
            }

            $oParserState->consumeWhiteSpace();
            if ($oParserState->comes('(')) {
                $iNestingLevel++;
                $oCalcList->addListComponent($oParserState->consume(1));
                $oParserState->consumeWhiteSpace();
                continue;
            } elseif ($oParserState->comes(')')) {
                $iNestingLevel--;
                $oCalcList->addListComponent($oParserState->consume(1));
                $oParserState->consumeWhiteSpace();
                continue;
            }
            if ($iLastComponentType != CalcFunction::T_OPERAND) {
                $oVal = Value::parsePrimitiveValue($oParserState);
                $oCalcList->addListComponent($oVal);
                $iLastComponentType = CalcFunction::T_OPERAND;
            } else {
                if (\in_array($oParserState->peek(), $aOperators, true)) {
                    if (($oParserState->comes('-') || $oParserState->comes('+'))) {
                        if (
                            $oParserState->peek(1, -1) != ' '
                            || !($oParserState->comes('- ')
                                || $oParserState->comes('+ '))
                        ) {
                            throw new UnexpectedTokenException(
                                " {$oParserState->peek()} ",
                                $oParserState->peek(1, -1) . $oParserState->peek(2),
                                'literal',
                                $oParserState->currentLine()
                            );
                        }
                    }
                    $oCalcList->addListComponent($oParserState->consume(1));
                    $iLastComponentType = CalcFunction::T_OPERATOR;
                } else {
                    throw new UnexpectedTokenException(
                        \sprintf(
                            'Next token was expected to be an operand of type %s. Instead "%s" was found.',
                            \implode(', ', $aOperators),
                            $oParserState->peek()
                        ),
                        '',
                        'custom',
                        $oParserState->currentLine()
                    );
                }
            }
            $oParserState->consumeWhiteSpace();
        }
        $oList->addListComponent($oCalcList);
        if (!$oParserState->isEnd()) {
            $oParserState->consume(')');
        }
        return new CalcFunction($sFunction, $oList, ',', $oParserState->currentLine());
    }
}
