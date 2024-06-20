<?php

namespace Sabberworm\CSS\Parsing;

use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\Parsing\Anchor;
use Sabberworm\CSS\Settings;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class ParserState implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var null
     *
     * @internal
     */
    public const EOF = null;

    /**
     * @var Settings
     */
    private $oParserSettings;

    /**
     * @var string
     */
    private $sText;

    /**
     * @var array<int, string>
     */
    private $aText;

    /**
     * @var int
     */
    private $iCurrentPosition;

    /**
     * will only be used if the CSS does not contain an `@charset` declaration
     *
     * @var string
     */
    private $sCharset;

    /**
     * @var int
     */
    private $iLength;

    /**
     * @var int
     */
    private $iLineNo;

    /**
     * @param string $sText the complete CSS as text (i.e., usually the contents of a CSS file)
     * @param int $iLineNo
     */
    public function __construct($sText, Settings $oParserSettings, $iLineNo = 1)
    {
        $this->logger = new NullLogger();

        $this->oParserSettings = $oParserSettings;
        $this->sText = $sText;
        $this->iCurrentPosition = 0;
        $this->iLineNo = $iLineNo;
        $this->setCharset($this->oParserSettings->sDefaultCharset);
    }

    /**
     * Sets the charset to be used if the CSS does not contain an `@charset` declaration.
     *
     * @param string $sCharset
     */
    public function setCharset($sCharset): void
    {
        $this->sCharset = $sCharset;
        $this->aText = $this->strsplit($this->sText);
        if (is_array($this->aText)) {
            $this->iLength = count($this->aText);
        }
    }

    /**
     * Returns the charset that is used if the CSS does not contain an `@charset` declaration.
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->sCharset;
    }

    /**
     * @return int
     */
    public function currentLine()
    {
        return $this->iLineNo;
    }

    /**
     * @return int
     */
    public function currentColumn()
    {
        return $this->iCurrentPosition;
    }

    /**
     * @return Settings
     */
    public function getSettings()
    {
        return $this->oParserSettings;
    }


    public function anchor(): Anchor
    {
        return new Anchor($this->iCurrentPosition, $this);
    }

    /**
     * @param int $iPosition
     */
    public function setPosition($iPosition): void
    {
        $this->iCurrentPosition = $iPosition;
    }

    /**
     * @param bool $bIgnoreCase
     *
     * @return string
     *
     * @throws UnexpectedTokenException
     */
    public function parseIdentifier($bIgnoreCase = true)
    {
        if ($this->isEnd()) {
            $this->logger->error('Unexpected end of file while parsing identifier at line {line}', ['line' => $this->iLineNo]);
            throw new UnexpectedEOFException('', '', 'identifier', $this->iLineNo);
        }
        $sResult = $this->parseCharacter(true);
        if ($sResult === null) {
            $this->logger->error('Unexpected token while parsing identifier at line {line}', ['line' => $this->iLineNo]);
            throw new UnexpectedTokenException($sResult, $this->peek(5), 'identifier', $this->iLineNo);
        }
        $sCharacter = null;
        while (!$this->isEnd() && ($sCharacter = $this->parseCharacter(true)) !== null) {
            if (preg_match('/[a-zA-Z0-9\x{00A0}-\x{FFFF}_-]/Sux', $sCharacter)) {
                $sResult .= $sCharacter;
            } else {
                $sResult .= '\\' . $sCharacter;
            }
        }
        if ($bIgnoreCase) {
            $sResult = $this->strtolower($sResult);
        }
        return $sResult;
    }

    /**
     * @param bool $bIsForIdentifier
     *
     * @return string|null
     *
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    public function parseCharacter($bIsForIdentifier)
    {
        if ($this->peek() === '\\') {
            if (
                $bIsForIdentifier && $this->oParserSettings->bLenientParsing
                && ($this->comes('\0') || $this->comes('\9'))
            ) {
                // Non-strings can contain \0 or \9 which is an IE hack supported in lenient parsing.
                return null;
            }
            $this->consume('\\');
            if ($this->comes('\n') || $this->comes('\r')) {
                return '';
            }
            if (preg_match('/[0-9a-fA-F]/Su', $this->peek()) === 0) {
                return $this->consume(1);
            }
            $sUnicode = $this->consumeExpression('/^[0-9a-fA-F]{1,6}/u', 6);
            if ($this->strlen($sUnicode) < 6) {
                // Consume whitespace after incomplete unicode escape
                if (preg_match('/\\s/isSu', $this->peek())) {
                    if ($this->comes('\r\n')) {
                        $this->consume(2);
                    } else {
                        $this->consume(1);
                    }
                }
            }
            $iUnicode = intval($sUnicode, 16);
            $sUtf32 = "";
            for ($i = 0; $i < 4; ++$i) {
                $sUtf32 .= chr($iUnicode & 0xff);
                $iUnicode = $iUnicode >> 8;
            }
            return iconv('utf-32le', $this->sCharset, $sUtf32);
        }
        if ($bIsForIdentifier) {
            $peek = ord($this->peek());
            // Ranges: a-z A-Z 0-9 - _
            if (
                ($peek >= 97 && $peek <= 122)
                || ($peek >= 65 && $peek <= 90)
                || ($peek >= 48 && $peek <= 57)
                || ($peek === 45)
                || ($peek === 95)
                || ($peek > 0xa1)
            ) {
                return $this->consume(1);
            }
        } else {
            return $this->consume(1);
        }
        return null;
    }

    /**
     * @return array<int, Comment>|void
     *
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    public function consumeWhiteSpace(): array
    {
        $aComments = [];
        do {
            while (preg_match('/\\s/isSu', $this->peek()) === 1) {
                $this->consume(1);
            }
            if ($this->oParserSettings->bLenientParsing) {
                try {
                    $oComment = $this->consumeComment();
                } catch (UnexpectedEOFException $e) {
                    $this->iCurrentPosition = $this->iLength;
                    return $aComments;
                }
            } else {
                $oComment = $this->consumeComment();
            }
            if ($oComment !== false) {
                $aComments[] = $oComment;
            }
        } while ($oComment !== false);
        return $aComments;
    }

    /**
     * @param string $sString
     * @param bool $bCaseInsensitive
     */
    public function comes($sString, $bCaseInsensitive = false): bool
    {
        $sPeek = $this->peek(strlen($sString));
        return ($sPeek == '')
            ? false
            : $this->streql($sPeek, $sString, $bCaseInsensitive);
    }

    /**
     * @param int $iLength
     * @param int $iOffset
     */
    public function peek($iLength = 1, $iOffset = 0): string
    {
        $iOffset += $this->iCurrentPosition;
        if ($iOffset >= $this->iLength) {
            return '';
        }
        return $this->substr($iOffset, $iLength);
    }

    /**
     * @param int $mValue
     *
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    public function consume($mValue = 1): string
    {
        if (is_string($mValue)) {
            $iLineCount = substr_count($mValue, "\n");
            $iLength = $this->strlen($mValue);
            if (!$this->streql($this->substr($this->iCurrentPosition, $iLength), $mValue)) {
                $this->logger->error('Unexpected token "{token}" at line {line}', ['token' => $mValue, 'line' => $this->iLineNo]);
                throw new UnexpectedTokenException($mValue, $this->peek(max($iLength, 5)), $this->iLineNo);
            }
            $this->iLineNo += $iLineCount;
            $this->iCurrentPosition += $this->strlen($mValue);
            return $mValue;
        } else {
            if ($this->iCurrentPosition + $mValue > $this->iLength) {
                $this->logger->error('Unexpected end of file while consuming {count} chars at line {line}', ['count' => $mValue, 'line' => $this->iLineNo]);
                throw new UnexpectedEOFException($mValue, $this->peek(5), 'count', $this->iLineNo);
            }
            $sResult = $this->substr($this->iCurrentPosition, $mValue);
            $iLineCount = substr_count($sResult, "\n");
            $this->iLineNo += $iLineCount;
            $this->iCurrentPosition += $mValue;
            return $sResult;
        }
    }

    /**
     * @param string $mExpression
     * @param int|null $iMaxLength
     *
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    public function consumeExpression($mExpression, $iMaxLength = null): string
    {
        $aMatches = null;
        $sInput = $iMaxLength !== null ? $this->peek($iMaxLength) : $this->inputLeft();
        if (preg_match($mExpression, $sInput, $aMatches, PREG_OFFSET_CAPTURE) === 1) {
            return $this->consume($aMatches[0][0]);
        }
        $this->logger->error(
            'Unexpected expression "{token}" instead of {expression} at line {line}',
            [
                'token' => $this->peek(5),
                'expression' => $mExpression,
                'line' => $this->iLineNo,
            ]
        );
        throw new UnexpectedTokenException($mExpression, $this->peek(5), 'expression', $this->iLineNo);
    }

    /**
     * @return Comment|false
     */
    public function consumeComment()
    {
        $mComment = false;
        if ($this->comes('/*')) {
            $iLineNo = $this->iLineNo;
            $this->consume(1);
            $mComment = '';
            while (($char = $this->consume(1)) !== '') {
                $mComment .= $char;
                if ($this->comes('*/')) {
                    $this->consume(2);
                    break;
                }
            }
        }

        if ($mComment !== false) {
            // We skip the * which was included in the comment.
            return new Comment(substr($mComment, 1), $iLineNo);
        }

        return $mComment;
    }

    public function isEnd(): bool
    {
        return $this->iCurrentPosition >= $this->iLength;
    }

    /**
     * @param array<array-key, string>|string $aEnd
     * @param string $bIncludeEnd
     * @param string $consumeEnd
     * @param array<int, Comment> $comments
     *
     * @return string
     *
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    public function consumeUntil($aEnd, $bIncludeEnd = false, $consumeEnd = false, array &$comments = [])
    {
        $aEnd = is_array($aEnd) ? $aEnd : [$aEnd];
        $out = '';
        $start = $this->iCurrentPosition;

        while (!$this->isEnd()) {
            $char = $this->consume(1);
            if (in_array($char, $aEnd)) {
                if ($bIncludeEnd) {
                    $out .= $char;
                } elseif (!$consumeEnd) {
                    $this->iCurrentPosition -= $this->strlen($char);
                }
                return $out;
            }
            $out .= $char;
            if ($comment = $this->consumeComment()) {
                $comments[] = $comment;
            }
        }

        if (in_array(self::EOF, $aEnd)) {
            return $out;
        }

        $this->iCurrentPosition = $start;
        $this->logger->error(
            'Unexpected end of file while searching for one of "{end}" at line {line}',
            ['end' => implode('","', $aEnd), 'line' => $this->iLineNo]
        );
        throw new UnexpectedEOFException(
            'One of ("' . implode('","', $aEnd) . '")',
            $this->peek(5),
            'search',
            $this->iLineNo
        );
    }

    private function inputLeft(): string
    {
        return $this->substr($this->iCurrentPosition, -1);
    }

    /**
     * @param string $sString1
     * @param string $sString2
     * @param bool $bCaseInsensitive
     */
    public function streql($sString1, $sString2, $bCaseInsensitive = true): bool
    {
        if ($bCaseInsensitive) {
            return $this->strtolower($sString1) === $this->strtolower($sString2);
        } else {
            return $sString1 === $sString2;
        }
    }

    /**
     * @param int $iAmount
     */
    public function backtrack($iAmount): void
    {
        $this->iCurrentPosition -= $iAmount;
    }

    /**
     * @param string $sString
     */
    public function strlen($sString): int
    {
        if ($this->oParserSettings->bMultibyteSupport) {
            return mb_strlen($sString, $this->sCharset);
        } else {
            return strlen($sString);
        }
    }

    /**
     * @param int $iStart
     * @param int $iLength
     */
    private function substr($iStart, $iLength): string
    {
        if ($iLength < 0) {
            $iLength = $this->iLength - $iStart + $iLength;
        }
        if ($iStart + $iLength > $this->iLength) {
            $iLength = $this->iLength - $iStart;
        }
        $sResult = '';
        while ($iLength > 0) {
            $sResult .= $this->aText[$iStart];
            $iStart++;
            $iLength--;
        }
        return $sResult;
    }

    /**
     * @param string $sString
     */
    private function strtolower($sString): string
    {
        if ($this->oParserSettings->bMultibyteSupport) {
            return mb_strtolower($sString, $this->sCharset);
        } else {
            return strtolower($sString);
        }
    }

    /**
     * @param string $sString
     *
     * @return array<int, string>
     */
    private function strsplit($sString)
    {
        if ($this->oParserSettings->bMultibyteSupport) {
            if ($this->streql($this->sCharset, 'utf-8')) {
                return preg_split('//u', $sString, -1, PREG_SPLIT_NO_EMPTY);
            } else {
                $iLength = mb_strlen($sString, $this->sCharset);
                $aResult = [];
                for ($i = 0; $i < $iLength; ++$i) {
                    $aResult[] = mb_substr($sString, $i, 1, $this->sCharset);
                }
                return $aResult;
            }
        } else {
            if ($sString === '') {
                return [];
            } else {
                return str_split($sString);
            }
        }
    }

    /**
     * @param string $sString
     * @param string $sNeedle
     * @param int $iOffset
     *
     * @return int|false
     */
    private function strpos($sString, $sNeedle, $iOffset)
    {
        if ($this->oParserSettings->bMultibyteSupport) {
            return mb_strpos($sString, $sNeedle, $iOffset, $this->sCharset);
        } else {
            return strpos($sString, $sNeedle, $iOffset);
        }
    }
}
