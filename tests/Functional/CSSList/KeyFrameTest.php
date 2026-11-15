<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\KeyFrame;
use Sabberworm\CSS\OutputFormat;

/**
 * @covers \Sabberworm\CSS\CSSList\KeyFrame
 */
final class KeyFrameTest extends TestCase
{
    /**
     * @test
     */
    public function renderRendersVendorKeyFrameAndAnimationName(): void
    {
        $subject = new KeyFrame();
        $subject->setVendorKeyFrame('-webkit-keyframes');
        $subject->setAnimationName('spin');

        self::assertSame('@-webkit-keyframes spin {}', $subject->render(OutputFormat::create()));
    }
}
