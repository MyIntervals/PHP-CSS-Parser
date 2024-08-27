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

    private function setUpTestcase()
    {
        $this->subject = new KeyFrame();
    }

    /**
     * @test
     */
    public function implementsAtRule()
    {
        $this->setUpTestcase();

        self::assertInstanceOf(AtRule::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsRenderable()
    {
        $this->setUpTestcase();

        self::assertInstanceOf(Renderable::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsCommentable()
    {
        $this->setUpTestcase();

        self::assertInstanceOf(Commentable::class, $this->subject);
    }
}
