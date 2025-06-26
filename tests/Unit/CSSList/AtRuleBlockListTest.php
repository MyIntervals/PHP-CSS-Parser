<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\CSSList\CSSBlockList;
use Sabberworm\CSS\CSSList\CSSList;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\Property\AtRule;
use Sabberworm\CSS\Renderable;

/**
 * @covers \Sabberworm\CSS\CSSList\AtRuleBlockList
 * @covers \Sabberworm\CSS\CSSList\CSSBlockList
 * @covers \Sabberworm\CSS\CSSList\CSSList
 */
final class AtRuleBlockListTest extends TestCase
{
    /**
     * @test
     */
    public function implementsAtRule(): void
    {
        $subject = new AtRuleBlockList('supports');

        self::assertInstanceOf(AtRule::class, $subject);
    }

    /**
     * @test
     */
    public function implementsRenderable(): void
    {
        $subject = new AtRuleBlockList('supports');

        self::assertInstanceOf(Renderable::class, $subject);
    }

    /**
     * @test
     */
    public function implementsCommentable(): void
    {
        $subject = new AtRuleBlockList('supports');

        self::assertInstanceOf(Commentable::class, $subject);
    }

    /**
     * @test
     */
    public function implementsCSSListItem(): void
    {
        $subject = new AtRuleBlockList('supports');

        self::assertInstanceOf(CSSListItem::class, $subject);
    }

    /**
     * @test
     */
    public function isCSSBLockList(): void
    {
        $subject = new AtRuleBlockList('supports');

        self::assertInstanceOf(CSSBlockList::class, $subject);
    }

    /**
     * @test
     */
    public function isCSSList(): void
    {
        $subject = new AtRuleBlockList('supports');

        self::assertInstanceOf(CSSList::class, $subject);
    }

    /**
     * @test
     */
    public function atRuleNameReturnsTypeProvidedToConstructor(): void
    {
        $type = 'keyframes';

        $subject = new AtRuleBlockList($type);

        self::assertSame($type, $subject->atRuleName());
    }

    /**
     * @test
     */
    public function atRuleArgsByDefaultReturnsEmptyString(): void
    {
        $subject = new AtRuleBlockList('supports');

        self::assertSame('', $subject->atRuleArgs());
    }

    /**
     * @test
     */
    public function atRuleArgsReturnsArgumentsProvidedToConstructor(): void
    {
        $arguments = 'bar';

        $subject = new AtRuleBlockList('', $arguments);

        self::assertSame($arguments, $subject->atRuleArgs());
    }

    /**
     * @test
     */
    public function getLineNumberByDefaultReturnsNull(): void
    {
        $subject = new AtRuleBlockList('');

        self::assertNull($subject->getLineNumber());
    }

    /**
     * @test
     */
    public function getLineNumberReturnsLineNumberProvidedToConstructor(): void
    {
        $lineNumber = 42;
        $subject = new AtRuleBlockList('', '', $lineNumber);

        self::assertSame($lineNumber, $subject->getLineNumber());
    }

    /**
     * @test
     */
    public function isRootListAlwaysReturnsFalse(): void
    {
        $subject = new AtRuleBlockList('supports');

        self::assertFalse($subject->isRootList());
    }
}
