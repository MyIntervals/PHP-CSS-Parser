<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Value\PrimitiveValue;
use Sabberworm\CSS\Value\Size;
use Sabberworm\CSS\Value\Value;

/**
 * @covers \Sabberworm\CSS\Value\PrimitiveValue
 * @covers \Sabberworm\CSS\Value\Size
 * @covers \Sabberworm\CSS\Value\Value
 */
final class SizeTest extends TestCase
{
    /**
     * @test
     */
    public function isPrimitiveValue(): void
    {
        $subject = new Size(1);

        self::assertInstanceOf(PrimitiveValue::class, $subject);
    }

    /**
     * @test
     */
    public function isValue(): void
    {
        $subject = new Size(1);

        self::assertInstanceOf(Value::class, $subject);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new Size(1);

        $result = $subject->getArrayRepresentation();

        self::assertSame('Size', $result['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesNumber(): void
    {
        $subject = new Size(1);

        $result = $subject->getArrayRepresentation();

        self::assertSame(1.0, $result['number']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesUnit(): void
    {
        $subject = new Size(1, 'px');

        $result = $subject->getArrayRepresentation();

        self::assertSame('px', $result['unit']);
    }

    /**
     * @test
     */
    public function setUnitSetsUnit(): void
    {
        $subject = new Size(1, 'px');
        $unit = 'em';

        $subject->setUnit($unit);

        self::assertSame($unit, $subject->getUnit());
    }

    /**
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
    public function isNotColorComponentByDefault(): void
    {
        self::assertFalse((new Size(1))->isColorComponent());
    }

    /**
     * @test
     */
    public function isColorComponentReturnsValueProvidedToConstructor(): void
    {
        self::assertTrue((new Size(1, null, true))->isColorComponent());
    }

    /**
     * @test
     */
    public function colorComponentIsNotLength(): void
    {
        self::assertFalse((new Size(1, null, true))->isLength());
    }

    /**
     * @test
     */
    public function isRelativeReturnsTrueForRelativeUnit(): void
    {
        self::assertTrue((new Size(1, 'em'))->isRelative());
    }

    /**
     * @test
     */
    public function isRelativeReturnsTrueForPercentage(): void
    {
        self::assertTrue((new Size(1, '%'))->isRelative());
    }

    /**
     * @test
     */
    public function isRelativeReturnsTrueForNonZeroNumberWithoutUnit(): void
    {
        self::assertTrue((new Size(1))->isRelative());
    }

    /**
     * @test
     */
    public function isRelativeReturnsFalseForAbsoluteUnit(): void
    {
        self::assertFalse((new Size(1, 'px'))->isRelative());
    }

    /**
     * @test
     */
    public function isRelativeReturnsFalseForZeroWithoutUnit(): void
    {
        self::assertFalse((new Size(0))->isRelative());
    }
}
