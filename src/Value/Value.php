<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Renderable;

/**
 * Abstract base class for specific classes of CSS values: `Size`, `Color`, `CSSString` and `URL`, and another
 * abstract subclass `ValueList`.
 */
abstract class Value implements Renderable
{
    /**
     * @var int<0, max>
     *
     * @internal since 8.8.0
     */
    protected $lineNumber;

    /**
     * @param int<0, max> $lineNumber
     */
    public function __construct($lineNumber = 0)
    {
        $this->lineNumber = $lineNumber;
    }

    /**
     * @param array<array-key, string> $aListDelimiters
     *
     * @return Value|string
     *
     * @throws UnexpectedTokenException
     * @throws UnexpectedEOFException
     *
     * @internal since V8.8.0
     */
    public static function parseValue(ParserState $parserState, array $aListDelimiters = [])
    {
        /** @var array<int, Value|string> $aStack */
        $aStack = [];
        $parserState->consumeWhiteSpace();
        //Build a list of delimiters and parsed values
        while (
        !($parserState->comes('}') || $parserState->comes(';') || $parserState->comes('!')
            || $parserState->comes(')')
            || $parserState->isEnd())
        ) {
            if (\count($aStack) > 0) {
                $bFoundDelimiter = false;
                foreach ($aListDelimiters as $sDelimiter) {
                    if ($parserState->comes($sDelimiter)) {
                        \array_push($aStack, $parserState->consume($sDelimiter));
                        $parserState->consumeWhiteSpace();
                        $bFoundDelimiter = true;
                        break;
                    }
                }
                if (!$bFoundDelimiter) {
                    //Whitespace was the list delimiter
                    \array_push($aStack, ' ');
                }
            }
            \array_push($aStack, self::parsePrimitiveValue($parserState));
            $parserState->consumeWhiteSpace();
        }
        // Convert the list to list objects
        foreach ($aListDelimiters as $sDelimiter) {
            $iStackLength = \count($aStack);
            if ($iStackLength === 1) {
                return $aStack[0];
            }
            $aNewStack = [];
            for ($offset = 0; $offset < $iStackLength; ++$offset) {
                if ($offset === ($iStackLength - 1) || $sDelimiter !== $aStack[$offset + 1]) {
                    $aNewStack[] = $aStack[$offset];
                    continue;
                }
                $length = 2; //Number of elements to be joined
                for ($i = $offset + 3; $i < $iStackLength; $i += 2, ++$length) {
                    if ($sDelimiter !== $aStack[$i]) {
                        break;
                    }
                }
                $list = new RuleValueList($sDelimiter, $parserState->currentLine());
                for ($i = $offset; $i - $offset < $length * 2; $i += 2) {
                    $list->addListComponent($aStack[$i]);
                }
                $aNewStack[] = $list;
                $offset += $length * 2 - 2;
            }
            $aStack = $aNewStack;
        }
        if (!isset($aStack[0])) {
            throw new UnexpectedTokenException(
                " {$parserState->peek()} ",
                $parserState->peek(1, -1) . $parserState->peek(2),
                'literal',
                $parserState->currentLine()
            );
        }
        return $aStack[0];
    }

    /**
     * @param bool $ignoreCase
     *
     * @return CSSFunction|string
     *
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     *
     * @internal since V8.8.0
     */
    public static function parseIdentifierOrFunction(ParserState $parserState, $ignoreCase = false)
    {
        $oAnchor = $parserState->anchor();
        $result = $parserState->parseIdentifier($ignoreCase);

        if ($parserState->comes('(')) {
            $oAnchor->backtrack();
            if ($parserState->streql('url', $result)) {
                $result = URL::parse($parserState);
            } elseif (
                $parserState->streql('calc', $result)
                || $parserState->streql('-webkit-calc', $result)
                || $parserState->streql('-moz-calc', $result)
            ) {
                $result = CalcFunction::parse($parserState);
            } else {
                $result = CSSFunction::parse($parserState, $ignoreCase);
            }
        }

        return $result;
    }

    /**
     * @return CSSFunction|CSSString|LineName|Size|URL|string
     *
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     * @throws SourceException
     *
     * @internal since V8.8.0
     */
    public static function parsePrimitiveValue(ParserState $parserState)
    {
        $oValue = null;
        $parserState->consumeWhiteSpace();
        if (
            \is_numeric($parserState->peek())
            || ($parserState->comes('-.')
                && \is_numeric($parserState->peek(1, 2)))
            || (($parserState->comes('-') || $parserState->comes('.')) && \is_numeric($parserState->peek(1, 1)))
        ) {
            $oValue = Size::parse($parserState);
        } elseif ($parserState->comes('#') || $parserState->comes('rgb', true) || $parserState->comes('hsl', true)) {
            $oValue = Color::parse($parserState);
        } elseif ($parserState->comes("'") || $parserState->comes('"')) {
            $oValue = CSSString::parse($parserState);
        } elseif ($parserState->comes('progid:') && $parserState->getSettings()->usesLenientParsing()) {
            $oValue = self::parseMicrosoftFilter($parserState);
        } elseif ($parserState->comes('[')) {
            $oValue = LineName::parse($parserState);
        } elseif ($parserState->comes('U+')) {
            $oValue = self::parseUnicodeRangeValue($parserState);
        } else {
            $sNextChar = $parserState->peek(1);
            try {
                $oValue = self::parseIdentifierOrFunction($parserState);
            } catch (UnexpectedTokenException $e) {
                if (\in_array($sNextChar, ['+', '-', '*', '/'], true)) {
                    $oValue = $parserState->consume(1);
                } else {
                    throw $e;
                }
            }
        }
        $parserState->consumeWhiteSpace();
        return $oValue;
    }

    /**
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    private static function parseMicrosoftFilter(ParserState $parserState): CSSFunction
    {
        $sFunction = $parserState->consumeUntil('(', false, true);
        $aArguments = Value::parseValue($parserState, [',', '=']);
        return new CSSFunction($sFunction, $aArguments, ',', $parserState->currentLine());
    }

    /**
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    private static function parseUnicodeRangeValue(ParserState $parserState): string
    {
        $iCodepointMaxLength = 6; // Code points outside BMP can use up to six digits
        $sRange = '';
        $parserState->consume('U+');
        do {
            if ($parserState->comes('-')) {
                $iCodepointMaxLength = 13; // Max length is 2 six-digit code points + the dash(-) between them
            }
            $sRange .= $parserState->consume(1);
        } while (\strlen($sRange) < $iCodepointMaxLength && \preg_match('/[A-Fa-f0-9\\?-]/', $parserState->peek()));
        return "U+{$sRange}";
    }

    /**
     * @return int<0, max>
     */
    public function getLineNo(): int
    {
        return $this->lineNumber;
    }
}
