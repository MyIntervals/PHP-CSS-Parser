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

        $subject->setUnit('em');

        self::assertSame('em', $subject->getUnit());
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
     * @return array<non-empty-string, array{0: float, 1: string|null, 2: bool}>
     */
    public static function provideSizeAndRelativity(): array
    {
        return [
            'relative unit' => [1.0, 'em', true],
            'percentage' => [1.0, '%', true],
            'absolute unit' => [1.0, 'px', false],
            'non-zero number without unit' => [1.0, null, true],
            'zero without unit' => [0.0, null, false],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideSizeAndRelativity
     */
    public function isRelativeDetectsRelativeSizes(float $number, ?string $unit, bool $expected): void
    {
        $subject = new Size($number, $unit);

        self::assertSame($expected, $subject->isRelative());
    }
}
