<?php

namespace Sabberworm\CSS\Tests\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\CSSList\KeyFrame;
use Sabberworm\CSS\Property\AtRule;
use Sabberworm\CSS\Renderable;

/**
 * @covers \Sabberworm\CSS\CSSList\KeyFrame
 */
final class KeyFrameTest extends TestCase
{
    /**
     * @var KeyFrame
     */
    protected $subject;

    protected function setUp()
    {
        $this->subject = new KeyFrame();
    }

    /**
     * @test
     */
    public function implementsAtRule()
    {
        self::assertInstanceOf(AtRule::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsRenderable()
    {
        self::assertInstanceOf(Renderable::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsCommentable()
    {
        self::assertInstanceOf(Commentable::class, $this->subject);
    }
}
