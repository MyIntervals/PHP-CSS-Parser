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
     * @var int<0, max>
     */
    private $currentPosition = 0;

    /**
     * will only be used if the CSS does not contain an `@charset` declaration
     *
     * @var string
     */
    private $charset;

    /**
     * @var int<1, max> $lineNumber
     */
    private $lineNumber;

    /**
     * @param string $text the complete CSS as text (i.e., usually the contents of a CSS file)
     * @param int<1, max> $lineNumber
     */
    public function __construct(string $text, Settings $parserSettings, int $lineNumber = 1)
    {
        $this->parserSettings = $parserSettings;
        $this->text = $text;
        $this->lineNumber = $lineNumber;
        $this->setCharset($this->parserSettings->getDefaultCharset());
    }

    /**
     * Sets the charset to be used if the CSS does not contain an `@charset` declaration.
     *
     * @throws SourceException if the charset is UTF-8 and the content has invalid byte sequences
     */
    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
        $this->characters = $this->strsplit($this->text);
    }

    /**
     * @return int<1, max>
     */
    public function currentLine(): int
    {
        return $this->lineNumber;
    }

    /**
     * @return int<0, max>
     */
    public function currentColumn(): int
    {
        return $this->currentPosition;
    }

    public function getSettings(): Settings
    {
        return $this->parserSettings;
    }

    public function anchor(): Anchor
    {
        return new Anchor($this->currentPosition, $this);
    }

    /**
     * @param int<0, max> $position
     */
    public function setPosition(int $position): void
    {
        $this->currentPosition = $position;
    }

    /**
     * @throws UnexpectedTokenException
     */
    public function parseIdentifier(bool $ignoreCase = true): string
    {
        if ($this->isEnd()) {
            throw new UnexpectedEOFException('', '', 'identifier', $this->lineNumber);
        }
        $result = $this->parseCharacter(true);
        if ($result === null) {
            throw new UnexpectedTokenException('', $this->peek(5), 'identifier', $this->lineNumber);
        }
        $character = null;
        while (!$this->isEnd() && ($character = $this->parseCharacter(true)) !== null) {
            if (\preg_match('/[a-zA-Z0-9\\x{00A0}-\\x{FFFF}_-]/Sux', $character)) {
                $result .= $character;
            } else {
                $result .= '\\' . $character;
            }
        }
        if ($ignoreCase) {
            $result = $this->strtolower($result);
        }
        return $result;
    }

    /**
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    public function parseCharacter(bool $isForIdentifier): ?string
    {
        if ($this->peek() === '\\') {
            $this->consume('\\');
            if ($this->comes('\\n') || $this->comes('\\r')) {
                return '';
            }
            if (\preg_match('/[0-9a-fA-F]/Su', $this->peek()) === 0) {
                return $this->consume(1);
            }
            $hexCodePoint = $this->consumeExpression('/^[0-9a-fA-F]{1,6}/u', 6);
            if ($this->strlen($hexCodePoint) < 6) {
                // Consume whitespace after incomplete unicode escape
                if (\preg_match('/\\s/isSu', $this->peek())) {
                    if ($this->comes('\\r\\n')) {
                        $this->consume(2);
                    } else {
                        $this->consume(1);
                    }
                }
            }
            $codePoint = \intval($hexCodePoint, 16);
            $utf32EncodedCharacter = '';
            for ($i = 0; $i < 4; ++$i) {
                $utf32EncodedCharacter .= \chr($codePoint & 0xff);
                $codePoint = $codePoint >> 8;
            }
            return \iconv('utf-32le', $this->charset, $utf32EncodedCharacter);
        }
        if ($isForIdentifier) {
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
            if ($this->parserSettings->usesLenientParsing()) {
                try {
                    $comment = $this->consumeComment();
                } catch (UnexpectedEOFException $e) {
                    $this->currentPosition = \count($this->characters);
                    return $comments;
                }
            } else {
                $comment = $this->consumeComment();
            }
            if ($comment !== false) {
                $comments[] = $comment;
            }
        } while ($comment !== false);
        return $comments;
    }

    public function comes(string $string, bool $caseInsensitive = false): bool
    {
        $peek = $this->peek(\strlen($string));
        return ($peek == '')
            ? false
            : $this->streql($peek, $string, $caseInsensitive);
    }

    /**
     * @param int<1, max> $length
     * @param int<0, max> $offset
     */
    public function peek(int $length = 1, int $offset = 0): string
    {
        $offset += $this->currentPosition;
        if ($offset >= \count($this->characters)) {
            return '';
        }
        return $this->substr($offset, $length);
    }

    /**
     * @param string|int<1, max> $value
     *
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    public function consume($value = 1): string
    {
        if (\is_string($value)) {
            $numberOfLines = \substr_count($value, "\n");
            $length = $this->strlen($value);
            if (!$this->streql($this->substr($this->currentPosition, $length), $value)) {
                throw new UnexpectedTokenException(
                    $value,
                    $this->peek(\max($length, 5)),
                    'literal',
                    $this->lineNumber
                );
            }
            $this->lineNumber += $numberOfLines;
            $this->currentPosition += $this->strlen($value);
            return $value;
        } else {
            if ($this->currentPosition + $value > \count($this->characters)) {
                throw new UnexpectedEOFException((string) $value, $this->peek(5), 'count', $this->lineNumber);
            }
            $result = $this->substr($this->currentPosition, $value);
            $numberOfLines = \substr_count($result, "\n");
            $this->lineNumber += $numberOfLines;
            $this->currentPosition += $value;
            return $result;
        }
    }

    /**
     * @param string $expression
     * @param int<1, max>|null $maximumLength
     *
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    public function consumeExpression(string $expression, ?int $maximumLength = null): string
    {
        $matches = null;
        $input = $maximumLength !== null ? $this->peek($maximumLength) : $this->inputLeft();
        if (\preg_match($expression, $input, $matches, PREG_OFFSET_CAPTURE) === 1) {
            return $this->consume($matches[0][0]);
        }
        throw new UnexpectedTokenException($expression, $this->peek(5), 'expression', $this->lineNumber);
    }

    /**
     * @return Comment|false
     */
    public function consumeComment()
    {
        $comment = false;
        if ($this->comes('/*')) {
            $lineNumber = $this->lineNumber;
            $this->consume(1);
            $comment = '';
            while (($char = $this->consume(1)) !== '') {
                $comment .= $char;
                if ($this->comes('*/')) {
                    $this->consume(2);
                    break;
                }
            }
        }

        if ($comment !== false) {
            // We skip the * which was included in the comment.
            return new Comment(\substr($comment, 1), $lineNumber);
        }

        return $comment;
    }

    public function isEnd(): bool
    {
        return $this->currentPosition >= \count($this->characters);
    }

    /**
     * @param array<array-key, string>|string $stopCharacters
     * @param array<int, Comment> $comments
     *
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    public function consumeUntil(
        $stopCharacters,
        bool $includeEnd = false,
        bool $consumeEnd = false,
        array &$comments = []
    ): string {
        $stopCharacters = \is_array($stopCharacters) ? $stopCharacters : [$stopCharacters];
        $consumedCharacters = '';
        $start = $this->currentPosition;

        while (!$this->isEnd()) {
            $character = $this->consume(1);
            if (\in_array($character, $stopCharacters, true)) {
                if ($includeEnd) {
                    $consumedCharacters .= $character;
                } elseif (!$consumeEnd) {
                    $this->currentPosition -= $this->strlen($character);
                }
                return $consumedCharacters;
            }
            $consumedCharacters .= $character;
            if ($comment = $this->consumeComment()) {
                $comments[] = $comment;
            }
        }

        if (\in_array(self::EOF, $stopCharacters, true)) {
            return $consumedCharacters;
        }

        $this->currentPosition = $start;
        throw new UnexpectedEOFException(
            'One of ("' . \implode('","', $stopCharacters) . '")',
            $this->peek(5),
            'search',
            $this->lineNumber
        );
    }

    private function inputLeft(): string
    {
        return $this->substr($this->currentPosition, -1);
    }

    public function streql(string $string1, string $string2, bool $caseInsensitive = true): bool
    {
        if ($caseInsensitive) {
            return $this->strtolower($string1) === $this->strtolower($string2);
        } else {
            return $string1 === $string2;
        }
    }

    /**
     * @param int<1, max> $numberOfCharacters
     */
    public function backtrack(int $numberOfCharacters): void
    {
        $this->currentPosition -= $numberOfCharacters;
    }

    /**
     * @return int<0, max>
     */
    public function strlen(string $string): int
    {
        if ($this->parserSettings->hasMultibyteSupport()) {
            return \mb_strlen($string, $this->charset);
        } else {
            return \strlen($string);
        }
    }

    /**
     * @param int<0, max> $offset
     */
    private function substr(int $offset, int $length): string
    {
        if ($length < 0) {
            $length = \count($this->characters) - $offset + $length;
        }
        if ($offset + $length > \count($this->characters)) {
            $length = \count($this->characters) - $offset;
        }
        $result = '';
        while ($length > 0) {
            $result .= $this->characters[$offset];
            $offset++;
            $length--;
        }
        return $result;
    }

    private function strtolower(string $string): string
    {
        if ($this->parserSettings->hasMultibyteSupport()) {
            return \mb_strtolower($string, $this->charset);
        } else {
            return \strtolower($string);
        }
    }

    /**
     * @return array<int, string>
     *
     * @throws SourceException if the charset is UTF-8 and the string contains invalid byte sequences
     */
    private function strsplit(string $string): array
    {
        if ($this->parserSettings->hasMultibyteSupport()) {
            if ($this->streql($this->charset, 'utf-8')) {
                $result = \preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
                if (!\is_array($result)) {
                    throw new SourceException('`preg_split` failed with error ' . \preg_last_error());
                }
                return $result;
            } else {
                $length = \mb_strlen($string, $this->charset);
                $result = [];
                for ($i = 0; $i < $length; ++$i) {
                    $result[] = \mb_substr($string, $i, 1, $this->charset);
                }
                return $result;
            }
        } else {
            if ($string === '') {
                return [];
            } else {
                return \str_split($string);
            }
        }
    }

    /**
     * @return int|false
     */
    private function strpos(string $haystack, string $needle, int $offset)
    {
        if ($this->parserSettings->hasMultibyteSupport()) {
            return \mb_strpos($haystack, $needle, $offset, $this->charset);
        } else {
            return \strpos($haystack, $needle, $offset);
        }
    }
}
