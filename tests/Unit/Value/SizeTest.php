<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Settings;
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
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideLengthUnit(): array
    {
        return self::mapUnitListForDataProvider([
            'px',
            'pt',
            'pc',
            'cm',
            'mm',
            'mozmm',
            'in',
            'vh',
            'dvh',
            'svh',
            'lvh',
            'vw',
            'dvw',
            'svw',
            'lvw',
            'vi',
            'dvi',
            'svi',
            'lvi',
            'vb',
            'dvb',
            'svb',
            'lvb',
            'vmin',
            'dvmin',
            'svmin',
            'lvmin',
            'vmax',
            'dvmax',
            'svmax',
            'lvmax',
            'rem',
            'rex',
            'rch',
            'rcap',
            'ric',
            'rlh',
            '%',
            'em',
            'ex',
            'ch',
            'cap',
            'ic',
            'lh',
            'fr',
            'cqw',
            'cqh',
            'cqi',
            'cqb',
            'cqmin',
            'cqmax',
        ]);
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideNonLengthUnit(): array
    {
        return self::mapUnitListForDataProvider([
            'deg',
            'grad',
            'rad',
            's',
            'ms',
            'turn',
            'Hz',
            'kHz',
            'dpi',
            'dpcm',
            'dppx',
            'x',
        ]);
    }

    /**
     * @test
     *
     * @param non-empty-string $unit
     *
     * @dataProvider provideLengthUnit
     * @dataProvider provideNonLengthUnit
     */
    public function parsesUnit(string $unit): void
    {
        $parsedSize = Size::parse(new ParserState('1' . $unit, Settings::create()));

        self::assertSame($unit, $parsedSize->getUnit());
    }

    /**
     * @test
     *
     * @param non-empty-string $unit
     *
     * @dataProvider provideLengthUnit
     */
    public function isLengthReturnsTrueForLengthUnit(string $unit): void
    {
        $parsedSize = Size::parse(new ParserState('1' . $unit, Settings::create()));

        self::assertTrue($parsedSize->isLength());
    }

    /**
     * @test
     *
     * @param non-empty-string $unit
     *
     * @dataProvider provideNonLengthUnit
     */
    public function isLengthReturnsFalseForNonLengthUnit(string $unit): void
    {
        $parsedSize = Size::parse(new ParserState('1' . $unit, Settings::create()));

        self::assertFalse($parsedSize->isLength());
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
     * @param array<non-empty-string> $units
     *
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    private static function mapUnitListForDataProvider(array $units): array
    {
        return \array_combine(
            $units,
            \array_map(
                static function (string $unit): array {
                    return [$unit];
                },
                $units
            )
        );
    }
}
