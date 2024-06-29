<?php

namespace Sabberworm\CSS\Tests\Value;

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
    public static function provideUnit()
    {
        $units = [
            'px', 'pt', 'pc',
            'cm', 'mm', 'mozmm', 'in',
            'vh', 'dvh', 'svh', 'lvh',
            'vw', 'vmin', 'vmax', 'rem',
            '%', 'em', 'ex', 'ch', 'fr',
            'deg', 'grad', 'rad', 's', 'ms', 'turn', 'Hz', 'kHz',
        ];

        return \array_combine(
            $units,
            \array_map(
                function ($unit) {
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
    public function parsesUnit($unit)
    {
        $subject = Size::parse(new ParserState('1' . $unit, Settings::create()));

        self::assertSame($unit, $subject->getUnit());
    }
}
