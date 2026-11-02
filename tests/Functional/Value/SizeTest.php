<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\Size;

/**
 * @covers \Sabberworm\CSS\Value\Size
 */
final class SizeTest extends TestCase
{
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
     * @return array<non-empty-string, array{0: float, 1: non-empty-string}>
     */
    public static function provideNumberAndExpectedRendering(): array
    {
        return [
            '1e-7' => [1e-7, '.0000001'],
            '-1e-7' => [-1e-7, '-.0000001'],
            '1.23456789e-5' => [1.23456789e-5, '.0000123456789'],
            '-2.5e-8' => [-2.5e-8, '-.000000025'],
            '1e-5' => [1e-5, '.00001'],
            // TODO: this is the smallest number that can be set without `number_format()` rendering the equivalent of
            // 1e15 when the locale is set to Portuguese on Windows 10.
            // Must be a bug in PHP, since `number_format()` is supposed to be locale-independent.
            '1e-18' => [1e-18, '.000000000000000001'],
            '10000000000000000000000' => [10000000000000000000000, '10000000000000000000000'],
            // TODO: the expected rendering may vary depending on the precision of the FPU
            '3.0418206565232E+21' => [3.0418206565232E+21, '3041820656523200167936'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideNumberAndExpectedRendering
     */
    public function renderWritesNumberInValidCssSyntaxWithoutLosingDigits(float $number, string $expected): void
    {
        $subject = new Size($number, 'px');

        self::assertSame($expected . 'px', $subject->render(OutputFormat::create()));
    }

    /**
     * @test
     *
     * @dataProvider provideNumberAndExpectedRendering
     */
    public function renderWritesNumberLocaleIndependent(float $number, string $expected): void
    {
        $subject = new Size($number, 'px');

        \setlocale(LC_ALL, 'pt_PT', 'no');

        try {
            $result = $subject->render(OutputFormat::create());
        } finally {
            \setlocale(LC_ALL, null); // restore default
        }

        self::assertSame($expected . 'px', $result);
    }
}
