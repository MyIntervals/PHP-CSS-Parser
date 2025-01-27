<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\Size;

/**
 * @covers \Sabberworm\CSS\Value\Size
 */
final class SizeTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
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
     * @dataProvider provideUnit
     */
    public function parsesUnit(string $unit): void
    {
        $subject = Size::parse(new ParserState('1' . $unit, Settings::create()));

        self::assertSame($unit, $subject->getUnit());
    }
}
