<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\Renderable;

/**
 * @covers \Sabberworm\CSS\CSSList\AtRuleBlockList
 */
final class AtRuleBlockListTest extends TestCase
{
    /**
     * @test
     */
    public function implementsAtRule(): void
    {
        $subject = new AtRuleBlockList('');

        self::assertInstanceOf(AtRuleBlockList::class, $subject);
    }

    /**
     * @test
     */
    public function implementsRenderable(): void
    {
        $subject = new AtRuleBlockList('');

        self::assertInstanceOf(Renderable::class, $subject);
    }

    /**
     * @test
     */
    public function implementsCommentable(): void
    {
        $subject = new AtRuleBlockList('');

        self::assertInstanceOf(Commentable::class, $subject);
    }
}
