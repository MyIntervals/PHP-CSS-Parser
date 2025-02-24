<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\CSSList\CSSList;
use Sabberworm\CSS\Renderable;

/**
 * @covers \Sabberworm\CSS\CSSList\AtRuleBlockList
 * @covers \Sabberworm\CSS\CSSList\CSSBlockList
 * @covers \Sabberworm\CSS\CSSList\CSSList
 */
final class AtRuleBlockListTest extends TestCase
{
    /*
     * Tests for the implemented interfaces and superclasses
     */

    /**
     * @test
     */
    public function implementsAtRule(): void
    {
        $subject = new AtRuleBlockList('supports');

        self::assertInstanceOf(AtRuleBlockList::class, $subject);
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
    public function isCSSList(): void
    {
        $subject = new AtRuleBlockList('supports');

        self::assertInstanceOf(CSSList::class, $subject);
    }

    /*
     * not grouped yet
     */

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
    public function getLineNoReturnsLineNumberProvidedToConstructor(): void
    {
        $lineNumber = 42;

        $subject = new AtRuleBlockList('', '', $lineNumber);

        self::assertSame($lineNumber, $subject->getLineNo());
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
