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
     * This exercises the deprecated `Size::setSize()` and `getSize()` which have been replaced with `setNumber()` and
     * `getNumber()` which are exercised in the non-deprecated equivalent `TestCase`.
     *
     * @test
     */
    public function setSizeConvertsNumericStringToFloat(): void
    {
        $subject = new Size(1);

        $subject->setSize('2.5');

        self::assertSame(2.5, $subject->getSize());
    }

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
