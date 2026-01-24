<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Parsing;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Settings;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @covers \Sabberworm\CSS\Parsing\ParserState
 */
final class ParserStateTest extends TestCase
{
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
}
