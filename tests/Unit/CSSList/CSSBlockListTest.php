<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\Tests\Unit\CSSList\Fixtures\ConcreteCSSBlockList;

/**
 * @covers \Sabberworm\CSS\CSSList\CSSBlockList
 * @covers \Sabberworm\CSS\CSSList\CSSList
 */
final class CSSBlockListTest extends TestCase
{
    /**
     * @test
     */
    public function implementsRenderable(): void
    {
        $subject = new ConcreteCSSBlockList();

        self::assertInstanceOf(Renderable::class, $subject);
    }

    /**
     * @test
     */
    public function implementsCommentable(): void
    {
        $subject = new ConcreteCSSBlockList();

        self::assertInstanceOf(Commentable::class, $subject);
    }

    /**
     * @test
     */
    public function isCSSList(): void
    {
        $subject = new ConcreteCSSBlockList();

        self::assertInstanceOf(ConcreteCSSBlockList::class, $subject);
    }
}
