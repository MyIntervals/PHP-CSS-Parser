<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Parsing;

use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\Settings;

/**
 * @internal since 8.7.0
 */
class ParserState
{
    /**
     * @var null
     */
    public const EOF = null;

    /**
     * @var Settings
     */
    private $parserSettings;

    /**
     * @var string
     */
    private $text;

    /**
     * @var array<int, string>
     */
    private $characters;

    /**
     * @var int
     */
    private $currentPosition = 0;

    /**
     * will only be used if the CSS does not contain an `@charset` declaration
     *
     * @var string
     */
    private $charset;

    /**
     * @var int
     */
    private $length;

    /**
     * @var int
     */
    private $lineNumber;

    /**
     * @param string $text the complete CSS as text (i.e., usually the contents of a CSS file)
     * @param int<0, max> $lineNumber
     */
    public function __construct($text, Settings $parserSettings, $lineNumber = 1)
    {
        $this->parserSettings = $parserSettings;
        $this->text = $text;
        $this->lineNumber = $lineNumber;
        $this->setCharset($this->parserSettings->sDefaultCharset);
    }

    /**
     * Sets the charset to be used if the CSS does not contain an `@charset` declaration.
     *
     * @param string $charset
     */
    public function setCharset($charset): void
    {
        $this->charset = $charset;
        $this->characters = $this->strsplit($this->text);
        if (\is_array($this->characters)) {
            $this->length = \count($this->characters);
        }
    }

    /**
     * @return int
     */
    public function currentLine()
    {
        return $this->lineNumber;
    }

    /**
     * @return int
     */
    public function currentColumn()
    {
        return $this->currentPosition;
    }

    /**
     * @return Settings
     */
    public function getSettings()
    {
        return $this->parserSettings;
    }

    public function anchor(): Anchor
    {
        return new Anchor($this->currentPosition, $this);
    }

    /**
     * @param int $position
     */
    public function setPosition($position): void
    {
        $this->currentPosition = $position;
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
            throw new UnexpectedEOFException('', '', 'identifier', $this->lineNumber);
        }
        $result = $this->parseCharacter(true);
        if ($result === null) {
            throw new UnexpectedTokenException('', $this->peek(5), 'identifier', $this->lineNumber);
        }
        $sCharacter = null;
        while (!$this->isEnd() && ($sCharacter = $this->parseCharacter(true)) !== null) {
            if (\preg_match('/[a-zA-Z0-9\\x{00A0}-\\x{FFFF}_-]/Sux', $sCharacter)) {
                $result .= $sCharacter;
            } else {
                $result .= '\\' . $sCharacter;
            }
        }
        if ($bIgnoreCase) {
            $result = $this->strtolower($result);
        }
        return $result;
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
                $bIsForIdentifier && $this->parserSettings->bLenientParsing
                && ($this->comes('\\0') || $this->comes('\\9'))
            ) {
                // Non-strings can contain \0 or \9 which is an IE hack supported in lenient parsing.
                return null;
            }
            $this->consume('\\');
            if ($this->comes('\\n') || $this->comes('\\r')) {
                return '';
            }
            if (\preg_match('/[0-9a-fA-F]/Su', $this->peek()) === 0) {
                return $this->consume(1);
            }
            $sUnicode = $this->consumeExpression('/^[0-9a-fA-F]{1,6}/u', 6);
            if ($this->strlen($sUnicode) < 6) {
                // Consume whitespace after incomplete unicode escape
                if (\preg_match('/\\s/isSu', $this->peek())) {
                    if ($this->comes('\\r\\n')) {
                        $this->consume(2);
                    } else {
                        $this->consume(1);
                    }
                }
            }
            $iUnicode = \intval($sUnicode, 16);
            $sUtf32 = '';
            for ($i = 0; $i < 4; ++$i) {
                $sUtf32 .= \chr($iUnicode & 0xff);
                $iUnicode = $iUnicode >> 8;
            }
            return \iconv('utf-32le', $this->charset, $sUtf32);
        }
        if ($bIsForIdentifier) {
            $peek = \ord($this->peek());
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
        $comments = [];
        do {
            while (\preg_match('/\\s/isSu', $this->peek()) === 1) {
                $this->consume(1);
            }
            if ($this->parserSettings->bLenientParsing) {
                try {
                    $oComment = $this->consumeComment();
                } catch (UnexpectedEOFException $e) {
                    $this->currentPosition = $this->length;
                    return $comments;
                }
            } else {
                $oComment = $this->consumeComment();
            }
            if ($oComment !== false) {
                $comments[] = $oComment;
            }
        } while ($oComment !== false);
        return $comments;
    }

    /**
     * @param string $sString
     * @param bool $bCaseInsensitive
     */
    public function comes($sString, $bCaseInsensitive = false): bool
    {
        $sPeek = $this->peek(\strlen($sString));
        return ($sPeek == '')
            ? false
            : $this->streql($sPeek, $sString, $bCaseInsensitive);
    }

    /**
     * @param int $length
     * @param int $iOffset
     */
    public function peek($length = 1, $iOffset = 0): string
    {
        $iOffset += $this->currentPosition;
        if ($iOffset >= $this->length) {
            return '';
        }
        return $this->substr($iOffset, $length);
    }

    /**
     * @param int $mValue
     *
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    public function consume($mValue = 1): string
    {
        if (\is_string($mValue)) {
            $iLineCount = \substr_count($mValue, "\n");
            $length = $this->strlen($mValue);
            if (!$this->streql($this->substr($this->currentPosition, $length), $mValue)) {
                throw new UnexpectedTokenException(
                    $mValue,
                    $this->peek(\max($length, 5)),
                    'literal',
                    $this->lineNumber
                );
            }
            $this->lineNumber += $iLineCount;
            $this->currentPosition += $this->strlen($mValue);
            return $mValue;
        } else {
            if ($this->currentPosition + $mValue > $this->length) {
                throw new UnexpectedEOFException((string) $mValue, $this->peek(5), 'count', $this->lineNumber);
            }
            $result = $this->substr($this->currentPosition, $mValue);
            $iLineCount = \substr_count($result, "\n");
            $this->lineNumber += $iLineCount;
            $this->currentPosition += $mValue;
            return $result;
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
        if (\preg_match($mExpression, $sInput, $aMatches, PREG_OFFSET_CAPTURE) === 1) {
            return $this->consume($aMatches[0][0]);
        }
        throw new UnexpectedTokenException($mExpression, $this->peek(5), 'expression', $this->lineNumber);
    }

    /**
     * @return Comment|false
     */
    public function consumeComment()
    {
        $mComment = false;
        if ($this->comes('/*')) {
            $lineNumber = $this->lineNumber;
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
            return new Comment(\substr($mComment, 1), $lineNumber);
        }

        return $mComment;
    }

    public function isEnd(): bool
    {
        return $this->currentPosition >= $this->length;
    }

    /**
     * @param array<array-key, string>|string $aEnd
     * @param string $bIncludeEnd
     * @param string $consumeEnd
     * @param array<int, Comment> $comments
     *
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    public function consumeUntil($aEnd, $bIncludeEnd = false, $consumeEnd = false, array &$comments = []): string
    {
        $aEnd = \is_array($aEnd) ? $aEnd : [$aEnd];
        $out = '';
        $start = $this->currentPosition;

        while (!$this->isEnd()) {
            $char = $this->consume(1);
            if (\in_array($char, $aEnd, true)) {
                if ($bIncludeEnd) {
                    $out .= $char;
                } elseif (!$consumeEnd) {
                    $this->currentPosition -= $this->strlen($char);
                }
                return $out;
            }
            $out .= $char;
            if ($comment = $this->consumeComment()) {
                $comments[] = $comment;
            }
        }

        if (\in_array(self::EOF, $aEnd, true)) {
            return $out;
        }

        $this->currentPosition = $start;
        throw new UnexpectedEOFException(
            'One of ("' . \implode('","', $aEnd) . '")',
            $this->peek(5),
            'search',
            $this->lineNumber
        );
    }

    private function inputLeft(): string
    {
        return $this->substr($this->currentPosition, -1);
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
        $this->currentPosition -= $iAmount;
    }

    /**
     * @param string $sString
     */
    public function strlen($sString): int
    {
        if ($this->parserSettings->bMultibyteSupport) {
            return \mb_strlen($sString, $this->charset);
        } else {
            return \strlen($sString);
        }
    }

    /**
     * @param int $iStart
     * @param int $length
     */
    private function substr($iStart, $length): string
    {
        if ($length < 0) {
            $length = $this->length - $iStart + $length;
        }
        if ($iStart + $length > $this->length) {
            $length = $this->length - $iStart;
        }
        $result = '';
        while ($length > 0) {
            $result .= $this->characters[$iStart];
            $iStart++;
            $length--;
        }
        return $result;
    }

    /**
     * @param string $sString
     */
    private function strtolower($sString): string
    {
        if ($this->parserSettings->bMultibyteSupport) {
            return \mb_strtolower($sString, $this->charset);
        } else {
            return \strtolower($sString);
        }
    }

    /**
     * @param string $sString
     *
     * @return array<int, string>
     */
    private function strsplit($sString)
    {
        if ($this->parserSettings->bMultibyteSupport) {
            if ($this->streql($this->charset, 'utf-8')) {
                return \preg_split('//u', $sString, -1, PREG_SPLIT_NO_EMPTY);
            } else {
                $length = \mb_strlen($sString, $this->charset);
                $result = [];
                for ($i = 0; $i < $length; ++$i) {
                    $result[] = \mb_substr($sString, $i, 1, $this->charset);
                }
                return $result;
            }
        } else {
            if ($sString === '') {
                return [];
            } else {
                return \str_split($sString);
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
        if ($this->parserSettings->bMultibyteSupport) {
            return \mb_strpos($sString, $sNeedle, $iOffset, $this->charset);
        } else {
            return \strpos($sString, $sNeedle, $iOffset);
        }
    }
}
