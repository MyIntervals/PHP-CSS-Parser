<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\CSSList\CSSBlockList;
use Sabberworm\CSS\CSSList\CSSList;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\Renderable;

/**
 * @covers \Sabberworm\CSS\CSSList\CSSBlockList
 * @covers \Sabberworm\CSS\CSSList\CSSList
 * @covers \Sabberworm\CSS\CSSList\Document
 */
final class DocumentTest extends TestCase
{
    /**
     * @test
     */
    public function implementsRenderable(): void
    {
        self::assertInstanceOf(Renderable::class, new Document());
    }

    /**
     * @test
     */
    public function implementsCommentable(): void
    {
        self::assertInstanceOf(Commentable::class, new Document());
    }

    /**
     * @test
     */
    public function isCSSBlockList(): void
    {
        $subject = new Document();

        self::assertInstanceOf(CSSBlockList::class, $subject);
    }

    /**
     * @test
     */
    public function isCSSList(): void
    {
        $subject = new Document();

        self::assertInstanceOf(CSSList::class, $subject);
    }

    /**
     * @test
     */
    public function isRootListAlwaysReturnsTrue(): void
    {
        $subject = new Document();

        self::assertTrue($subject->isRootList());
    }
}
