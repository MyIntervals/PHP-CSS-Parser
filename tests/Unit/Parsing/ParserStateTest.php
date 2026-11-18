<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Parsing;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Settings;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @covers \Sabberworm\CSS\Parsing\ParserState
 */
final class ParserStateTest extends TestCase
{
    /**
     * @test
     */
    public function constructorThrowsForTextThatIsNotValidUtf8(): void
    {
        if (\PHP_VERSION_ID < 70304) {
            // https://bugs.php.net/76127
            self::markTestSkipped('Before PHP 7.3.4 preg_split did not return false for invalid UTF-8');
        }

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The CSS is not valid UTF-8.');
        $this->expectExceptionCode(1787112623);

        new ParserState("\xFF body{}", Settings::create());
    }

    /**
     * @test
     */
    public function currentColumnIsOneBasedPositionWithinCurrentLine(): void
    {
        $subject = new ParserState("a{}\n\n  b{}", Settings::create());

        self::assertSame(1, $subject->currentColumn());

        $subject->consume(3);
        self::assertSame(4, $subject->currentColumn());

        $subject->consume(4);
        self::assertSame(3, $subject->currentColumn());
    }

    /**
     * @test
     */
    public function parseIdentifierAcceptsMultibyteCharacters(): void
    {
        $subject = new ParserState('Café', Settings::create()->withMultibyteSupport(true));

        $result = $subject->parseIdentifier(false);

        self::assertSame('Café', $result);
    }

    /**
     * @test
     */
    public function parseIdentifierThrowsForTextThatIsNotValidUtf8WithoutMultibyteSupport(): void
    {
        $subject = new ParserState("a\xFF", Settings::create()->withMultibyteSupport(false));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The CSS is not valid UTF-8.');
        $this->expectExceptionCode(1787112617);

        $subject->parseIdentifier();
    }

    /**
     * @test
     */
    public function parseCharacterThrowsForEscapedTextThatIsNotValidUtf8WithoutMultibyteSupport(): void
    {
        $subject = new ParserState("\\\xFF", Settings::create()->withMultibyteSupport(false));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The CSS is not valid UTF-8.');
        $this->expectExceptionCode(1787112618);

        $subject->parseCharacter(true);
    }

    /**
     * @test
     */
    public function parseCharacterThrowsForMultibyteCharacterAfterIncompleteUnicodeEscapeWithoutMultibyteSupport(): void
    {
        // "\xC3\xA9" is "é". Without multibyte support, it is split into two separate "characters",
        // each of which is not valid UTF-8 on its own.
        $subject = new ParserState("\\3\xC3\xA9", Settings::create()->withMultibyteSupport(false));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The CSS is not valid UTF-8.');
        $this->expectExceptionCode(1787112619);

        $subject->parseCharacter(true);
    }

    /**
     * @test
     */
    public function parseCharacterThrowsForEscapedCodePointThatCannotBeConverted(): void
    {
        // 0xFFFFFF is outside the Unicode range, so `\iconv` cannot convert it.
        $subject = new ParserState('\\FFFFFF', Settings::create());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'The Unicode escape sequence could not be converted to the target character set.'
        );
        $this->expectExceptionCode(1787112620);

        $subject->parseCharacter(false);
    }

    /**
     * @return array<
     *             string,
     *             array{
     *                 text: non-empty-string,
     *                 stopCharacter: non-empty-string,
     *                 expectedConsumedText: non-empty-string,
     *                 expectedComments: non-empty-list<non-empty-string>
     *             }
     *         >
     */
    public static function provideTextForConsumptionWithComments(): array
    {
        return [
            'comment at start' => [
                'text' => '/*comment*/hello{',
                'stopCharacter' => '{',
                'expectedConsumedText' => 'hello',
                'expectedComments' => ['comment'],
            ],
            'comment at end' => [
                'text' => 'hello/*comment*/{',
                'stopCharacter' => '{',
                'expectedConsumedText' => 'hello',
                'expectedComments' => ['comment'],
            ],
            'comment in middle' => [
                'text' => 'hell/*comment*/o{',
                'stopCharacter' => '{',
                'expectedConsumedText' => 'hello',
                'expectedComments' => ['comment'],
            ],
            'two comments at start' => [
                'text' => '/*comment1*//*comment2*/hello{',
                'stopCharacter' => '{',
                'expectedConsumedText' => 'hello',
                'expectedComments' => ['comment1', 'comment2'],
            ],
            'two comments at end' => [
                'text' => 'hello/*comment1*//*comment2*/{',
                'stopCharacter' => '{',
                'expectedConsumedText' => 'hello',
                'expectedComments' => ['comment1', 'comment2'],
            ],
            'two comments interspersed' => [
                'text' => 'he/*comment1*/ll/*comment2*/o{',
                'stopCharacter' => '{',
                'expectedConsumedText' => 'hello',
                'expectedComments' => ['comment1', 'comment2'],
            ],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $text
     * @param non-empty-string $stopCharacter
     * @param non-empty-string $expectedConsumedText
     * @param non-empty-list<non-empty-string> $expectedComments
     *
     * @dataProvider provideTextForConsumptionWithComments
     */
    public function consumeUntilExtractsComments(
        string $text,
        string $stopCharacter,
        string $expectedConsumedText,
        array $expectedComments
    ): void {
        $subject = new ParserState($text, Settings::create());

        $comments = [];
        $result = $subject->consumeUntil($stopCharacter, false, false, $comments);

        self::assertSame($expectedConsumedText, $result);
        $commentsAsText = \array_map(
            static function (Comment $comment): string {
                return $comment->getComment();
            },
            $comments
        );
        self::assertSame($expectedComments, $commentsAsText);
    }

    /**
     * @test
     */
    public function consumeIfComesComsumesMatchingContent(): void
    {
        $subject = new ParserState('abc', Settings::create());

        $subject->consumeIfComes('ab');

        self::assertSame('c', $subject->peek());
    }

    /**
     * @test
     */
    public function consumeIfComesDoesNotComsumeNonMatchingContent(): void
    {
        $subject = new ParserState('a', Settings::create());

        $subject->consumeIfComes('x');

        self::assertSame('a', $subject->peek());
    }

    /**
     * @test
     */
    public function consumeIfComesReturnsTrueIfContentConsumed(): void
    {
        $subject = new ParserState('abc', Settings::create());

        $result = $subject->consumeIfComes('ab');

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function consumeIfComesReturnsFalseIfContentNotConsumed(): void
    {
        $subject = new ParserState('a', Settings::create());

        $result = $subject->consumeIfComes('x');

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function consumeIfComesUpdatesLineNumber(): void
    {
        $subject = new ParserState("\n", Settings::create());

        $subject->consumeIfComes("\n");

        self::assertSame(2, $subject->currentLine());
    }

    /**
     * @return array<non-empty-string, array{0: string, 1: string}>
     */
    public static function provideContentWhichMayHaveWhitespaceOrCommentsAndExpectedConsumption(): array
    {
        return [
            'nothing' => ['', ''],
            'space' => [' ', ' '],
            'tab' => ["\t", "\t"],
            'line feed' => ["\n", "\n"],
            'carriage return' => ["\r", "\r"],
            'two spaces' => ['  ', '  '],
            'comment' => ['/*hello*/', ''],
            'comment with space to the left' => [' /*hello*/', ' '],
            'comment with space to the right' => ['/*hello*/ ', ' '],
            'two comments' => ['/*hello*//*bye*/', ''],
            'two comments with space between' => ['/*hello*/ /*bye*/', ' '],
            'two comments with line feed between' => ["/*hello*/\n/*bye*/", "\n"],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideContentWhichMayHaveWhitespaceOrCommentsAndExpectedConsumption
     */
    public function consumeWhiteSpaceReturnsTheConsumed(
        string $whitespaceMaybeWithComments,
        string $expectedConsumption
    ): void {
        $subject = new ParserState($whitespaceMaybeWithComments, Settings::create());

        $result = $subject->consumeWhiteSpace();

        self::assertSame($expectedConsumption, $result);
    }

    /**
     * @test
     */
    public function consumeWhiteSpaceExtractsComment(): void
    {
        $commentText = 'Did they get you to trade your heroes for ghosts?';
        $subject = new ParserState('/*' . $commentText . '*/', Settings::create());

        $result = [];
        $subject->consumeWhiteSpace($result);

        self::assertInstanceOf(Comment::class, $result[0]);
        self::assertSame($commentText, $result[0]->getComment());
    }

    /**
     * @test
     */
    public function consumeWhiteSpaceExtractsTwoComments(): void
    {
        $commentText1 = 'Hot ashes for trees? Hot air for a cool breeze?';
        $commentText2 = 'Cold comfort for change? Did you exchange';
        $subject = new ParserState('/*' . $commentText1 . '*//*' . $commentText2 . '*/', Settings::create());

        $result = [];
        $subject->consumeWhiteSpace($result);

        self::assertInstanceOf(Comment::class, $result[0]);
        self::assertSame($commentText1, $result[0]->getComment());
        self::assertInstanceOf(Comment::class, $result[1]);
        self::assertSame($commentText2, $result[1]->getComment());
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideWhitespace(): array
    {
        return [
            'space' => [' '],
            'tab' => ["\t"],
            'line feed' => ["\n"],
            'carriage return' => ["\r"],
            'two spaces' => ['  '],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $whitespace
     *
     * @dataProvider provideWhitespace
     */
    public function consumeWhiteSpaceExtractsCommentWithSurroundingWhitespace(string $whitespace): void
    {
        $commentText = 'A walk-on part in the war for a lead role in a cage?';
        $subject = new ParserState($whitespace . '/*' . $commentText . '*/' . $whitespace, Settings::create());

        $result = [];
        $subject->consumeWhiteSpace($result);

        self::assertInstanceOf(Comment::class, $result[0]);
        self::assertSame($commentText, $result[0]->getComment());
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideNonWhitespace(): array
    {
        return [
            'number' => ['7'],
            'uppercase letter' => ['B'],
            'lowercase letter' => ['c'],
            'symbol' => ['@'],
            'sequence of non-whitespace characters' => ['Hello!'],
            'sequence of characters including space which isn\'t first' => ['Oh no!'],
        ];
    }

    /**
     * @return DataProvider<non-empty-string, array{0: non-empty-string, 1: string}>
     */
    public function provideNonWhitespaceAndContentWithPossibleWhitespaceOrComments(): DataProvider
    {
        return DataProvider::cross(
            self::provideNonWhitespace(),
            self::provideContentWhichMayHaveWhitespaceOrCommentsAndExpectedConsumption()
        );
    }

    /**
     * @test
     *
     * @param non-empty-string $nonWhitespace
     *
     * @dataProvider provideNonWhitespaceAndContentWithPossibleWhitespaceOrComments
     */
    public function consumeWhiteSpaceStopsAtNonWhitespace(string $nonWhitespace, string $whitespace): void
    {
        $subject = new ParserState($whitespace . $nonWhitespace, Settings::create());

        $subject->consumeWhiteSpace();

        self::assertTrue($subject->comes($nonWhitespace));
    }

    /**
     * @test
     */
    public function consumeWhiteSpaceThrowsForTextThatIsNotValidUtf8WithoutMultibyteSupport(): void
    {
        $subject = new ParserState("\xFF body{}", Settings::create()->withMultibyteSupport(false));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The CSS is not valid UTF-8.');
        $this->expectExceptionCode(1787112621);

        $subject->consumeWhiteSpace();
    }

    /**
     * @test
     */
    public function consumeExpressionThrowsForTextThatIsNotValidUtf8WithoutMultibyteSupport(): void
    {
        $subject = new ParserState("\xFF", Settings::create()->withMultibyteSupport(false));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The CSS is not valid UTF-8.');
        $this->expectExceptionCode(1787112622);

        $subject->consumeExpression('/^[0-9a-fA-F]{1,6}/u', 6);
    }

    /**
     * @test
     */
    public function parseCharacterKeepsEscapedBackslashBeforeLetterThatIsNotNewline(): void
    {
        $subject = new ParserState('\\\\new\\\\road', Settings::create());

        $result = '';
        for ($i = 0; $i < 4; ++$i) {
            $result .= $subject->parseCharacter(false);
        }

        self::assertSame('\\new', $result);
    }

    /**
     * @return array<string, array{0: non-empty-string}>
     */
    public static function provideNewline(): array
    {
        return [
            'LF' => ["\n"],
            'CR' => ["\r"],
            'CRLF' => ["\r\n"],
            'FF' => ["\f"],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideNewline
     */
    public function parseCharacterConsumesNewlineAfterIncompleteUnicodeEscape(string $newline): void
    {
        // `\41` is the CSS escape for `A`. Whitespace after a hex escape is part of the escape, so a literal `A`
        // would not exercise this.
        $subject = new ParserState('\\41' . $newline . 'B', Settings::create());

        self::assertSame('A', $subject->parseCharacter(false));
        self::assertSame('B', $subject->parseCharacter(false));
    }

    /**
     * @test
     *
     * @dataProvider provideNewline
     */
    public function parseCharacterForStringConsumesEscapedNewline(string $newline): void
    {
        $subject = new ParserState('\\' . $newline . 'B', Settings::create());

        self::assertSame('', $subject->parseCharacter(false));
        self::assertSame('B', $subject->parseCharacter(false));
    }

    /**
     * @test
     *
     * @dataProvider provideNewline
     */
    public function parseCharacterForIdentifierDoesNotConsumeEscapedNewline(string $newline): void
    {
        $subject = new ParserState('\\' . $newline . 'B', Settings::create());

        self::assertSame('', $subject->parseCharacter(true));
        self::assertTrue($subject->comes($newline));
    }

    /**
     * @test
     */
    public function getSettingsReturnsSettingsProvidedToConstructor(): void
    {
        $settings = Settings::create();
        $subject = new ParserState('a', $settings);

        $result = $subject->getSettings();

        self::assertSame($settings, $result);
    }

    /**
     * @test
     */
    public function setPositionMovesCurrentPosition(): void
    {
        $subject = new ParserState('abc', Settings::create());
        $subject->consume(2);

        $subject->setPosition(0);

        $offset = $subject->currentOffset();
        $character = $subject->peek();

        self::assertSame(0, $offset);
        self::assertSame('a', $character);
    }

    /**
     * @test
     */
    public function backtrackMovesPositionBack(): void
    {
        $subject = new ParserState('abc', Settings::create());
        $subject->consume(2);

        $subject->backtrack(1);

        $result = $subject->peek();

        self::assertSame('b', $result);
    }

    /**
     * @test
     */
    public function anchorBacktracksToPositionAtWhichItWasCreated(): void
    {
        $subject = new ParserState('abc', Settings::create());
        $subject->consume(1);
        $anchor = $subject->anchor();
        $subject->consume(1);

        $anchor->backtrack();

        $result = $subject->peek();

        self::assertSame('b', $result);
    }

    /**
     * @test
     */
    public function parseIdentifierThrowsAtEndOfInput(): void
    {
        $subject = new ParserState('', Settings::create());

        $this->expectException(UnexpectedEOFException::class);

        $subject->parseIdentifier();
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideCharacterThatCannotBePartOfIdentifier(): array
    {
        return [
            'opening brace' => ['{'],
            'closing brace' => ['}'],
            'opening parenthesis' => ['('],
            'closing parenthesis' => [')'],
            'semicolon' => [';'],
            'comma' => [','],
            'colon' => [':'],
            'space' => [' '],
            'full stop' => ['.'],
            'hash' => ['#'],
            'at sign' => ['@'],
            'plus' => ['+'],
            'asterisk' => ['*'],
            'slash' => ['/'],
            'double quote' => ['"'],
            'single quote' => ["'"],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideCharacterThatCannotBePartOfIdentifier
     */
    public function parseIdentifierThrowsForCharacterThatCannotStartIdentifier(string $character): void
    {
        $subject = new ParserState($character, Settings::create());

        $this->expectException(UnexpectedTokenException::class);

        $subject->parseIdentifier();
    }

    /**
     * @test
     */
    public function parseIdentifierWithoutIgnoringCaseKeepsCase(): void
    {
        $identifier = 'Ab';
        $subject = new ParserState($identifier, Settings::create());

        $result = $subject->parseIdentifier(false);

        self::assertSame($identifier, $result);
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideCharacterThatWouldNeedEscaping(): array
    {
        return [
            'full stop' => ['.'],
            'opening brace' => ['{'],
            'closing brace' => ['}'],
            'opening parenthesis' => ['('],
            'closing parenthesis' => [')'],
            'colon' => [':'],
            'semicolon' => [';'],
            'comma' => [','],
            'hash' => ['#'],
            'plus' => ['+'],
            'slash' => ['/'],
            'space' => [' '],
            'double quote' => ['"'],
            'single quote' => ["'"],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideCharacterThatWouldNeedEscaping
     */
    public function parseIdentifierIncludesEscapedCharacter(string $character): void
    {
        $identifier = 'a\\' . $character . 'b';
        $subject = new ParserState($identifier, Settings::create());

        $result = $subject->parseIdentifier();

        self::assertSame($identifier, $result);
    }

    /**
     * @test
     *
     * @dataProvider provideCharacterThatWouldNeedEscaping
     */
    public function parseCharacterForEscapedNonHexCharacterReturnsThatCharacter(string $character): void
    {
        $subject = new ParserState('\\' . $character, Settings::create());

        $result = $subject->parseCharacter(false);

        self::assertSame($character, $result);
    }

    /**
     * @test
     */
    public function parseCharacterForIncompleteUnicodeEscapeConsumesFollowingWhitespace(): void
    {
        $subject = new ParserState('\\41 x', Settings::create());

        $result = $subject->parseCharacter(false);

        self::assertSame('A', $result);
        self::assertSame('x', $subject->peek());
    }

    /**
     * @test
     *
     * @dataProvider provideCharacterThatCannotBePartOfIdentifier
     */
    public function parseCharacterForIdentifierReturnsNullForCharacterNotValidInIdentifier(string $character): void
    {
        $subject = new ParserState($character, Settings::create());

        $result = $subject->parseCharacter(true);

        self::assertNull($result);
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideAnyCharacter(): array
    {
        return [
            'lowercase letter' => ['a'],
            'uppercase letter' => ['Z'],
            'digit' => ['5'],
            'hyphen' => ['-'],
            'underscore' => ['_'],
            'space' => [' '],
            'full stop' => ['.'],
            'semicolon' => [';'],
            'opening brace' => ['{'],
            'closing brace' => ['}'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideAnyCharacter
     */
    public function parseCharacterNotForIdentifierReturnsAnyCharacter(string $character): void
    {
        $subject = new ParserState($character, Settings::create());

        $result = $subject->parseCharacter(false);

        self::assertSame($character, $result);
    }

    /**
     * @test
     */
    public function consumeWhiteSpaceInLenientModeConsumesUnclosedCommentUntilEndOfInput(): void
    {
        $subject = new ParserState('/* x', Settings::create());

        $subject->consumeWhiteSpace();

        self::assertTrue($subject->isEnd());
    }

    /**
     * @test
     */
    public function consumeWhiteSpaceInStrictModeThrowsForUnclosedComment(): void
    {
        $subject = new ParserState('/* x', Settings::create()->beStrict());

        $this->expectException(UnexpectedEOFException::class);

        $subject->consumeWhiteSpace();
    }

    /**
     * @test
     */
    public function consumeThrowsForStringThatDoesNotCome(): void
    {
        $subject = new ParserState('ab', Settings::create());

        $this->expectException(UnexpectedTokenException::class);

        $subject->consume('x');
    }

    /**
     * @test
     */
    public function consumeThrowsForMoreCharactersThanAreLeft(): void
    {
        $subject = new ParserState('ab', Settings::create());

        $this->expectException(UnexpectedEOFException::class);

        $subject->consume(3);
    }

    /**
     * @test
     */
    public function consumeExpressionConsumesMatch(): void
    {
        $subject = new ParserState('ab', Settings::create());

        $result = $subject->consumeExpression('/^a/');

        self::assertSame('a', $result);
        self::assertSame('b', $subject->peek());
    }

    /**
     * @test
     */
    public function consumeExpressionThrowsIfExpressionDoesNotMatch(): void
    {
        $subject = new ParserState('ab', Settings::create());

        $this->expectException(UnexpectedTokenException::class);

        $subject->consumeExpression('/^x/');
    }

    /**
     * @test
     */
    public function consumeUntilWithIncludeEndReturnsStopCharacter(): void
    {
        $content = 'ab';
        $stopCharacter = ';';
        $rest = 'c';
        $subject = new ParserState($content . $stopCharacter . $rest, Settings::create());

        $result = $subject->consumeUntil($stopCharacter, true);

        self::assertSame($content . $stopCharacter, $result);
        self::assertSame($rest, $subject->peek());
    }

    /**
     * @test
     */
    public function consumeUntilWithConsumeEndConsumesButDoesNotReturnStopCharacter(): void
    {
        $content = 'ab';
        $stopCharacter = ';';
        $rest = 'c';
        $subject = new ParserState($content . $stopCharacter . $rest, Settings::create());

        $result = $subject->consumeUntil($stopCharacter, false, true);

        self::assertSame($content, $result);
        self::assertSame($rest, $subject->peek());
    }

    /**
     * @test
     */
    public function consumeUntilWithEofAsStopCharacterConsumesRestOfInput(): void
    {
        $text = 'abc';
        $subject = new ParserState($text, Settings::create());

        $result = $subject->consumeUntil([ParserState::EOF]);

        self::assertSame($text, $result);
        self::assertTrue($subject->isEnd());
    }

    /**
     * @test
     */
    public function consumeUntilThrowsIfStopCharacterIsNotFound(): void
    {
        $subject = new ParserState('abc', Settings::create());

        $this->expectException(UnexpectedEOFException::class);

        $subject->consumeUntil(';');
    }

    /**
     * @test
     */
    public function peekWithMultibyteSupportSplitsNonUtf8TextIntoCharacters(): void
    {
        // `\xE4` is `ä` in ISO-8859-1, but it is not valid UTF-8 on its own.
        $firstCharacter = "\xE4";
        $secondCharacter = 'b';
        $subject = new ParserState(
            $firstCharacter . $secondCharacter,
            Settings::create()->withMultibyteSupport(true)->withDefaultCharset('ISO-8859-1')
        );

        $first = $subject->peek();
        $second = $subject->peek(1, 1);

        self::assertSame($firstCharacter, $first);
        self::assertSame($secondCharacter, $second);
    }
}
