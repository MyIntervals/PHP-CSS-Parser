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
        $subject = new Size(1);

        $result = $subject->isColorComponent();

        self::assertFalse($result);
    }

    /**
     * @test
     *
     * @dataProvider provideBooleans
     */
    public function isColorComponentReturnsValueProvidedToConstructor(bool $isColorComponent): void
    {
        $subject = new Size(1, null, $isColorComponent);

        $result = $subject->isColorComponent();

        self::assertSame($isColorComponent, $result);
    }

    /**
     * @return array<non-empty-string, array{0: bool}>
     */
    public static function provideBooleans(): array
    {
        return [
            'true' => [true],
            'false' => [false],
        ];
    }

    /**
     * @test
     */
    public function isLengthReturnsFalseForColorComponent(): void
    {
        $subject = new Size(1, null, true);

        $result = $subject->isLength();

        self::assertFalse($result);
    }

    /**
     * @test
     *
     * @dataProvider provideRelativeSizes
     */
    public function isRelativeReturnsTrueForRelativeSizes(float $number, ?string $unit): void
    {
        $subject = new Size($number, $unit);

        $result = $subject->isRelative();

        self::assertTrue($result);
    }

    /**
     * @return array<non-empty-string, array{0: float, 1: string|null}>
     */
    public static function provideRelativeSizes(): array
    {
        return [
            'relative unit' => [1.0, 'em'],
            'percentage' => [1.0, '%'],
            'non-zero number without unit' => [1.0, null],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideAbsoluteSizes
     */
    public function isRelativeReturnsFalseForAbsoluteSizes(float $number, ?string $unit): void
    {
        $subject = new Size($number, $unit);

        $result = $subject->isRelative();

        self::assertFalse($result);
    }

    /**
     * @return array<non-empty-string, array{0: float, 1: string|null}>
     */
    public static function provideAbsoluteSizes(): array
    {
        return [
            'absolute unit' => [1.0, 'px'],
            'zero without unit' => [0.0, null],
        ];
    }
}
