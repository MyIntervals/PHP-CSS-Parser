<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\Comment;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\OutputFormat;

/**
 * @covers \Sabberworm\CSS\Comment\Comment
 */
final class CommentTest extends TestCase
{
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
    public function renderWithDefaultOutputFormatRendersCommentEnclosedInCommentDelimiters(): void
    {
        $comment = 'There is no spoon.';
        $subject = new Comment();

        $subject->setComment($comment);

        self::assertSame('/*' . $comment . '*/', $subject->render(new OutputFormat()));
    }

    /**
     * @test
     */
    public function renderWithCompactOutputFormatRendersCommentEnclosedInCommentDelimiters(): void
    {
        $comment = 'There is no spoon.';
        $subject = new Comment();

        $subject->setComment($comment);

        self::assertSame('/*' . $comment . '*/', $subject->render(OutputFormat::createCompact()));
    }

    /**
     * @test
     */
    public function renderWithPrettyOutputFormatRendersCommentEnclosedInCommentDelimiters(): void
    {
        $comment = 'There is no spoon.';
        $subject = new Comment();

        $subject->setComment($comment);

        self::assertSame('/*' . $comment . '*/', $subject->render(OutputFormat::createPretty()));
    }
}
