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
     * @return array<string, array{0: non-empty-string}>
     */
    public static function provideUnit(): array
    {
        $units = [
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
            'vmin',
            'vmax',
            'rem',
            '%',
            'em',
            'ex',
            'ch',
            'fr',
            'deg',
            'grad',
            'rad',
            's',
            'ms',
            'turn',
            'Hz',
            'kHz',
        ];

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
     * @test
     *
     * @param non-empty-string $unit
     *
     * @dataProvider provideUnit
     */
    public function parsesUnit(string $unit): void
    {
        $parsedSize = Size::parse(new ParserState('1' . $unit, Settings::create()));

        self::assertSame($unit, $parsedSize->getUnit());
    }
}
