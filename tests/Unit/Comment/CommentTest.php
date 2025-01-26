<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Comment;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Renderable;

/**
 * @covers \Sabberworm\CSS\Comment\Comment
 */
final class CommentTest extends TestCase
{
    /**
     * @test
     */
    public function implementsRenderable(): void
    {
        $subject = new Comment();

        self::assertInstanceOf(Renderable::class, $subject);
    }

    /**
     * @test
     */
    public function getCommentOnEmptyInstanceReturnsEmptyString(): void
    {
        $subject = new Comment();

        self::assertSame('', $subject->getComment());
    }

    /**
     * @test
     */
    public function getCommentInitiallyReturnsCommentPassedToConstructor(): void
    {
        $comment = 'There is no spoon.';
        $subject = new Comment($comment);

        self::assertSame($comment, $subject->getComment());
    }

    /**
     * @test
     */
    public function setCommentSetsComments(): void
    {
        $comment = 'There is no spoon.';
        $subject = new Comment();

        $subject->setComment($comment);

        self::assertSame($comment, $subject->getComment());
    }

    /**
     * @test
     */
    public function getLineNoOnEmptyInstanceReturnsZero(): void
    {
        $subject = new Comment();

        self::assertSame(0, $subject->getLineNo());
    }

    /**
     * @test
     */
    public function getLineNoInitiallyReturnsLineNumberPassedToConstructor(): void
    {
        $lineNumber = 42;
        $subject = new Comment('', $lineNumber);

        self::assertSame($lineNumber, $subject->getLineNo());
    }

    /**
     * @test
     */
    public function toStringRendersCommentEnclosedInCommentDelimiters(): void
    {
        $comment = 'There is no spoon.';
        $subject = new Comment();

        $subject->setComment($comment);

        self::assertSame('/*' . $comment . '*/', (string) $subject);
    }

    /**
     * @test
     */
    public function renderRendersCommentEnclosedInCommentDelimiters(): void
    {
        $comment = 'There is no spoon.';
        $subject = new Comment();

        $subject->setComment($comment);

        self::assertSame('/*' . $comment . '*/', $subject->render(new OutputFormat()));
    }
}
