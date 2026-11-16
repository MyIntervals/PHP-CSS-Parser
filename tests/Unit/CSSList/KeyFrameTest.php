<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\CSSList\CSSList;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\CSSList\KeyFrame;
use Sabberworm\CSS\Property\AtRule;
use Sabberworm\CSS\Renderable;

/**
 * @covers \Sabberworm\CSS\CSSList\CSSList
 * @covers \Sabberworm\CSS\CSSList\KeyFrame
 */
final class KeyFrameTest extends TestCase
{
    /**
     * @test
     */
    public function implementsAtRule(): void
    {
        $subject = new KeyFrame();

        self::assertInstanceOf(AtRule::class, $subject);
    }

    /**
     * @test
     */
    public function implementsRenderable(): void
    {
        $subject = new KeyFrame();

        self::assertInstanceOf(Renderable::class, $subject);
    }

    /**
     * @test
     */
    public function implementsCommentable(): void
    {
        $subject = new KeyFrame();

        self::assertInstanceOf(Commentable::class, $subject);
    }

    /**
     * @test
     */
    public function implementsCSSListItem(): void
    {
        $subject = new KeyFrame();

        self::assertInstanceOf(CSSListItem::class, $subject);
    }

    /**
     * @test
     */
    public function isCSSList(): void
    {
        $subject = new KeyFrame();

        self::assertInstanceOf(CSSList::class, $subject);
    }

    /**
     * @test
     */
    public function getLineNumberByDefaultReturnsNull(): void
    {
        $subject = new KeyFrame();

        self::assertNull($subject->getLineNumber());
    }

    /**
     * @test
     */
    public function getLineNumberReturnsLineNumberProvidedToConstructor(): void
    {
        $lineNumber = 42;
        $subject = new KeyFrame($lineNumber);

        self::assertSame($lineNumber, $subject->getLineNumber());
    }

    /**
     * @test
     */
    public function getAnimationNameByDefaultReturnsNone(): void
    {
        $subject = new KeyFrame();

        self::assertSame('none', $subject->getAnimationName());
    }

    /**
     * @test
     */
    public function getVendorKeyFrameByDefaultReturnsKeyframes(): void
    {
        $subject = new KeyFrame();

        self::assertSame('keyframes', $subject->getVendorKeyFrame());
    }

    /**
     * @test
     */
    public function getArrayRepresentationThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $subject = new KeyFrame();

        $subject->getArrayRepresentation();
    }

    /**
     * @test
     */
    public function setAnimationNameSetsAnimationName(): void
    {
        $subject = new KeyFrame();

        $animationName = 'spin';

        $subject->setAnimationName($animationName);

        self::assertSame($animationName, $subject->getAnimationName());
    }

    /**
     * @test
     */
    public function setVendorKeyFrameSetsVendorKeyFrame(): void
    {
        $subject = new KeyFrame();

        $vendorKeyFrame = '-webkit-keyframes';

        $subject->setVendorKeyFrame($vendorKeyFrame);

        self::assertSame($vendorKeyFrame, $subject->getVendorKeyFrame());
    }

    /**
     * @test
     */
    public function isNotRootList(): void
    {
        self::assertFalse((new KeyFrame())->isRootList());
    }

    /**
     * @test
     */
    public function atRuleNameReturnsVendorKeyFrame(): void
    {
        $subject = new KeyFrame();
        $vendorKeyFrame = '-webkit-keyframes';
        $subject->setVendorKeyFrame($vendorKeyFrame);

        self::assertSame($vendorKeyFrame, $subject->atRuleName());
    }

    /**
     * @test
     */
    public function atRuleArgsReturnsAnimationName(): void
    {
        $subject = new KeyFrame();
        $animationName = 'spin';
        $subject->setAnimationName($animationName);

        self::assertSame($animationName, $subject->atRuleArgs());
    }
}
