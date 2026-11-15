<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\UnitDeprecated\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Value\Size;

/**
 * @covers \Sabberworm\CSS\Value\Size
 */
final class SizeTest extends TestCase
{
    /**
     * @test
     */
    public function isSizeReturnsTrueForLength(): void
    {
        self::assertTrue((new Size(1, 'px'))->isSize());
    }

    /**
     * @test
     */
    public function isSizeReturnsFalseForNonLength(): void
    {
        self::assertFalse((new Size(1, 's'))->isSize());
    }
}
