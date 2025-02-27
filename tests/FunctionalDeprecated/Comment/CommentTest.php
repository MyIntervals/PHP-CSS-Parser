<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\FunctionalDeprecated\Comment;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Comment;

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
}
