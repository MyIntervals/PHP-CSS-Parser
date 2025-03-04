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
     *
     * @internal since V8.8.0
     */
    public static function parse(ParserState $parserState, bool $ignoreCase = false): CSSFunction
    {
        $aOperators = ['+', '-', '*', '/'];
        $sFunction = $parserState->parseIdentifier();
        if ($parserState->peek() != '(') {
            // Found ; or end of line before an opening bracket
            throw new UnexpectedTokenException('(', $parserState->peek(), 'literal', $parserState->currentLine());
        } elseif (!\in_array($sFunction, ['calc', '-moz-calc', '-webkit-calc'], true)) {
            // Found invalid calc definition. Example calc (...
            throw new UnexpectedTokenException('calc', $sFunction, 'literal', $parserState->currentLine());
        }
        $parserState->consume('(');
        $oCalcList = new CalcRuleValueList($parserState->currentLine());
        $list = new RuleValueList(',', $parserState->currentLine());
        $nestingLevel = 0;
        $iLastComponentType = null;
        while (!$parserState->comes(')') || $nestingLevel > 0) {
            if ($parserState->isEnd() && $nestingLevel === 0) {
                break;
            }

            $parserState->consumeWhiteSpace();
            if ($parserState->comes('(')) {
                $nestingLevel++;
                $oCalcList->addListComponent($parserState->consume(1));
                $parserState->consumeWhiteSpace();
                continue;
            } elseif ($parserState->comes(')')) {
                $nestingLevel--;
                $oCalcList->addListComponent($parserState->consume(1));
                $parserState->consumeWhiteSpace();
                continue;
            }
            if ($iLastComponentType != CalcFunction::T_OPERAND) {
                $oVal = Value::parsePrimitiveValue($parserState);
                $oCalcList->addListComponent($oVal);
                $iLastComponentType = CalcFunction::T_OPERAND;
            } else {
                if (\in_array($parserState->peek(), $aOperators, true)) {
                    if (($parserState->comes('-') || $parserState->comes('+'))) {
                        if (
                            $parserState->peek(1, -1) != ' '
                            || !($parserState->comes('- ')
                                || $parserState->comes('+ '))
                        ) {
                            throw new UnexpectedTokenException(
                                " {$parserState->peek()} ",
                                $parserState->peek(1, -1) . $parserState->peek(2),
                                'literal',
                                $parserState->currentLine()
                            );
                        }
                    }
                    $oCalcList->addListComponent($parserState->consume(1));
                    $iLastComponentType = CalcFunction::T_OPERATOR;
                } else {
                    throw new UnexpectedTokenException(
                        \sprintf(
                            'Next token was expected to be an operand of type %s. Instead "%s" was found.',
                            \implode(', ', $aOperators),
                            $parserState->peek()
                        ),
                        '',
                        'custom',
                        $parserState->currentLine()
                    );
                }
            }
            $parserState->consumeWhiteSpace();
        }
        $list->addListComponent($oCalcList);
        if (!$parserState->isEnd()) {
            $parserState->consume(')');
        }
        return new CalcFunction($sFunction, $list, ',', $parserState->currentLine());
    }
}
