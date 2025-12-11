<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Parsing;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Settings;

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
}
