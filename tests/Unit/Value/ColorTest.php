<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\Color;

/**
 * Note: some test data is currently commented-out.
 * These cover
 * - CSS Color Module Level 4 syntaxes that are not yet supported;
 * - Some invalid syntaxes that should be rejected but currently are not.
 *
 * @covers \Sabberworm\CSS\Value\Color
 */
final class ColorTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function provideValidColorAndExpectedRendering(): array
    {
        return [
            '3-digit hex color' => [
                '#070',
                '#070',
            ],
            '6-digit hex color that can be represented as 3-digit' => [
                '#007700',
                '#070',
            ],
            '6-digit hex color that cannot be represented as 3-digit' => [
                '#007600',
                '#007600',
            ],
            '4-digit hex color (with alpha)' => [
                '#0707',
                'rgba(0,119,0,.47)',
            ],
            '8-digit hex color (with alpha)' => [
                '#0077007F',
                'rgba(0,119,0,.5)',
            ],
            'legacy rgb that can be represented as 3-digit hex' => [
                'rgb(0, 119, 0)',
                '#070',
            ],
            'legacy rgb that cannot be represented as 3-digit hex' => [
                'rgb(0, 118, 0)',
                '#007600',
            ],
            'legacy rgb with percentage components' => [
                'rgb(0%, 60%, 0%)',
                'rgb(0%,60%,0%)',
            ],
            'legacy rgba with fractional alpha' => [
                'rgba(0, 119, 0, 0.5)',
                'rgba(0,119,0,.5)',
            ],
            'legacy rgba with percentage alpha' => [
                'rgba(0, 119, 0, 50%)',
                'rgba(0,119,0,50%)',
            ],
            'legacy rgba with percentage components and fractional alpha' => [
                'rgba(0%, 60%, 0%, 0.5)',
                'rgba(0%,60%,0%,.5)',
            ],
            'legacy rgba with percentage components and percentage alpha' => [
                'rgba(0%, 60%, 0%, 50%)',
                'rgba(0%,60%,0%,50%)',
            ],
            'legacy rgb as rgba' => [
                'rgba(0, 119, 0)',
                '#070',
            ],
            'legacy rgba as rgb' => [
                'rgb(0, 119, 0, 0.5)',
                'rgba(0,119,0,.5)',
            ],
            'modern rgb' => [
                'rgb(0 119 0)',
                '#070',
            ],
            'modern rgb with percentage R' => [
                'rgb(0% 119 0)',
                'rgb(0% 119 0)',
            ],
            'modern rgb with percentage G' => [
                'rgb(0 60% 0)',
                'rgb(0 60% 0)',
            ],
            'modern rgb with percentage B' => [
                'rgb(0 119 0%)',
                'rgb(0 119 0%)',
            ],
            'modern rgb with percentage R&G' => [
                'rgb(0% 60% 0)',
                'rgb(0% 60% 0)',
            ],
            'modern rgb with percentage R&B' => [
                'rgb(0% 119 0%)',
                'rgb(0% 119 0%)',
            ],
            'modern rgb with percentage G&B' => [
                'rgb(0 60% 0%)',
                'rgb(0 60% 0%)',
            ],
            'modern rgb with percentage components' => [
                'rgb(0% 60% 0%)',
                'rgb(0%,60%,0%)',
            ],
            'modern rgb with none as red' => [
                'rgb(none 119 0)',
                'rgb(none 119 0)',
            ],
            'modern rgb with none as green' => [
                'rgb(0 none 0)',
                'rgb(0 none 0)',
            ],
            'modern rgb with none as blue' => [
                'rgb(0 119 none)',
                'rgb(0 119 none)',
            ],
            'modern rgba with fractional alpha' => [
                'rgb(0 119 0 / 0.5)',
                'rgba(0,119,0,.5)',
            ],
            'modern rgba with percentage alpha' => [
                'rgb(0 119 0 / 50%)',
                'rgba(0,119,0,50%)',
            ],
            'modern rgba with percentage R' => [
                'rgb(0% 119 0 / 0.5)',
                'rgba(0% 119 0/.5)',
            ],
            'modern rgba with percentage G' => [
                'rgb(0 60% 0 / 0.5)',
                'rgba(0 60% 0/.5)',
            ],
            'modern rgba with percentage B' => [
                'rgb(0 119 0% / 0.5)',
                'rgba(0 119 0%/.5)',
            ],
            'modern rgba with percentage RGB' => [
                'rgb(0% 60% 0% / 0.5)',
                'rgba(0%,60%,0%,.5)',
            ],
            'modern rgba with percentage components' => [
                'rgb(0% 60% 0% / 50%)',
                'rgba(0%,60%,0%,50%)',
            ],
            'modern rgba with none as alpha' => [
                'rgb(0 119 0 / none)',
                'rgba(0 119 0/none)',
            ],
            'legacy rgb with var for R' => [
                'rgb(var(--r), 119, 0)',
                'rgb(var(--r),119,0)',
            ],
            'legacy rgb with var for G' => [
                'rgb(0, var(--g), 0)',
                'rgb(0,var(--g),0)',
            ],
            'legacy rgb with var for B' => [
                'rgb(0, 119, var(--b))',
                'rgb(0,119,var(--b))',
            ],
            'legacy rgb with var for RG' => [
                'rgb(var(--rg), 0)',
                'rgb(var(--rg),0)',
            ],
            'legacy rgb with var for GB' => [
                'rgb(0, var(--gb))',
                'rgb(0,var(--gb))',
            ],
            'legacy rgba with var for R' => [
                'rgba(var(--r), 119, 0, 0.5)',
                'rgba(var(--r),119,0,.5)',
            ],
            'legacy rgba with var for G' => [
                'rgba(0, var(--g), 0, 0.5)',
                'rgba(0,var(--g),0,.5)',
            ],
            'legacy rgba with var for B' => [
                'rgb(0, 119, var(--b), 0.5)',
                'rgb(0,119,var(--b),.5)',
            ],
            'legacy rgba with var for A' => [
                'rgba(0, 119, 0, var(--a))',
                'rgba(0,119,0,var(--a))',
            ],
            'legacy rgba with var for RG' => [
                'rgba(var(--rg), 0, 0.5)',
                'rgba(var(--rg),0,.5)',
            ],
            'legacy rgba with var for GB' => [
                'rgba(0, var(--gb), 0.5)',
                'rgba(0,var(--gb),.5)',
            ],
            'legacy rgba with var for BA' => [
                'rgba(0, 119, var(--ba))',
                'rgba(0,119,var(--ba))',
            ],
            'legacy rgba with var for RGB' => [
                'rgba(var(--rgb), 0.5)',
                'rgba(var(--rgb),.5)',
            ],
            'legacy rgba with var for GBA' => [
                'rgba(0, var(--gba))',
                'rgba(0,var(--gba))',
            ],
            'modern rgb with var for R' => [
                'rgb(var(--r) 119 0)',
                'rgb(var(--r),119,0)',
            ],
            'modern rgb with var for G' => [
                'rgb(0 var(--g) 0)',
                'rgb(0,var(--g),0)',
            ],
            'modern rgb with var for B' => [
                'rgb(0 119 var(--b))',
                'rgb(0,119,var(--b))',
            ],
            'modern rgb with var for RG' => [
                'rgb(var(--rg) 0)',
                'rgb(var(--rg),0)',
            ],
            'modern rgb with var for GB' => [
                'rgb(0 var(--gb))',
                'rgb(0,var(--gb))',
            ],
            'modern rgba with var for R' => [
                'rgba(var(--r) 119 0 / 0.5)',
                'rgba(var(--r),119,0,.5)',
            ],
            'modern rgba with var for G' => [
                'rgba(0 var(--g) 0 / 0.5)',
                'rgba(0,var(--g),0,.5)',
            ],
            'modern rgba with var for B' => [
                'rgba(0 119 var(--b) / 0.5)',
                'rgba(0,119,var(--b),.5)',
            ],
            'modern rgba with var for A' => [
                'rgba(0 119 0 / var(--a))',
                'rgba(0,119,0,var(--a))',
            ],
            'modern rgba with var for RG' => [
                'rgba(var(--rg) 0 / 0.5)',
                'rgba(var(--rg),0,.5)',
            ],
            'modern rgba with var for GB' => [
                'rgba(0 var(--gb) / 0.5)',
                'rgba(0,var(--gb),.5)',
            ],
            'modern rgba with var for BA' => [
                'rgba(0 119 var(--ba))',
                'rgba(0,119,var(--ba))',
            ],
            'modern rgba with var for RGB' => [
                'rgba(var(--rgb) / 0.5)',
                'rgba(var(--rgb),.5)',
            ],
            'modern rgba with var for GBA' => [
                'rgba(0 var(--gba))',
                'rgba(0,var(--gba))',
            ],
            'rgba with var for RGBA' => [
                'rgba(var(--rgba))',
                'rgba(var(--rgba))',
            ],
            'legacy hsl' => [
                'hsl(120, 100%, 25%)',
                'hsl(120,100%,25%)',
            ],
            'legacy hsl with deg' => [
                'hsl(120deg, 100%, 25%)',
                'hsl(120deg,100%,25%)',
            ],
            'legacy hsl with grad' => [
                'hsl(133grad, 100%, 25%)',
                'hsl(133grad,100%,25%)',
            ],
            'legacy hsl with rad' => [
                'hsl(2.094rad, 100%, 25%)',
                'hsl(2.094rad,100%,25%)',
            ],
            'legacy hsl with turn' => [
                'hsl(0.333turn, 100%, 25%)',
                'hsl(.333turn,100%,25%)',
            ],
            'legacy hsla with fractional alpha' => [
                'hsla(120, 100%, 25%, 0.5)',
                'hsla(120,100%,25%,.5)',
            ],
            'legacy hsla with percentage alpha' => [
                'hsla(120, 100%, 25%, 50%)',
                'hsla(120,100%,25%,50%)',
            ],
            'legacy hsl as hsla' => [
                'hsla(120, 100%, 25%)',
                'hsl(120,100%,25%)',
            ],
            'legacy hsla as hsl' => [
                'hsl(120, 100%, 25%, 0.5)',
                'hsla(120,100%,25%,.5)',
            ],
            'modern hsl' => [
                'hsl(120 100% 25%)',
                'hsl(120,100%,25%)',
            ],
            'modern hsl with none as hue' => [
                'hsl(none 100% 25%)',
                'hsl(none 100% 25%)',
            ],
            'modern hsl with none as saturation' => [
                'hsl(120 none 25%)',
                'hsl(120 none 25%)',
            ],
            'modern hsl with none as lightness' => [
                'hsl(120 100% none)',
                'hsl(120 100% none)',
            ],
            'modern hsla' => [
                'hsl(120 100% 25% / 0.5)',
                'hsla(120,100%,25%,.5)',
            ],
            'modern hsla with none as alpha' => [
                'hsl(120 100% 25% / none)',
                'hsla(120 100% 25%/none)',
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideValidColorAndExpectedRendering
     */
    public function parsesAndRendersValidColor(string $color, string $expectedRendering): void
    {
        $subject = Color::parse(new ParserState($color, Settings::create()));

        $renderedResult = $subject->render(OutputFormat::create());

        self::assertSame($expectedRendering, $renderedResult);
    }

    /**
     * Browsers reject all these, thus so should the parser.
     *
     * @return array<string, array{0: string}>
     */
    public static function provideInvalidColor(): array
    {
        return [
            'hex color with 0 digits' => [
                '#',
            ],
            'hex color with 1 digit' => [
                '#f',
            ],
            'hex color with 2 digits' => [
                '#f0',
            ],
            'hex color with 5 digits' => [
                '#ff000',
            ],
            'hex color with 7 digits' => [
                '#ff00000',
            ],
            'hex color with 9 digits' => [
                '#ff0000000',
            ],
            'rgb color with 0 arguments' => [
                'rgb()',
            ],
            'rgb color with 1 argument' => [
                'rgb(255)',
            ],
            'legacy rgb color with 2 arguments' => [
                'rgb(255, 0)',
            ],
            'legacy rgb color with 5 arguments' => [
                'rgb(255, 0, 0, 0.5, 0)',
            ],
            /*
            'legacy rgb color with invalid unit' => [
                'rgb(255, 0px, 0)',
            ],
            //*/
            'legacy rgb color with none as red' => [
                'rgb(none, 0, 0)',
            ],
            'legacy rgb color with none as green' => [
                'rgb(255, none, 0)',
            ],
            'legacy rgb color with none as blue' => [
                'rgb(255, 0, none)',
            ],
            'legacy rgba color with none as alpha' => [
                'rgba(255, 0, 0, none)',
            ],
            'modern rgb color without slash separator for alpha' => [
                'rgb(255 0 0 0.5)',
            ],
            'rgb color with mixed separators, comma first' => [
                'rgb(255, 0 0)',
            ],
            'rgb color with mixed separators, space first' => [
                'rgb(255 0, 0)',
            ],
            'hsl color with 0 arguments' => [
                'hsl()',
            ],
            'hsl color with 1 argument' => [
                'hsl(0)',
            ],
            'legacy hsl color with 2 arguments' => [
                'hsl(0, 100%)',
            ],
            'legacy hsl color with 5 arguments' => [
                'hsl(0, 100%, 50%, 0.5, 0)',
            ],
            'legacy hsl color with none as hue' => [
                'hsl(none, 100%, 50%)',
            ],
            'legacy hsl color with none as saturation' => [
                'hsl(0, none, 50%)',
            ],
            'legacy hsl color with none as lightness' => [
                'hsl(0, 100%, none)',
            ],
            'legacy hsla color with none as alpha' => [
                'hsl(0, 100%, 50%, none)',
            ],
            /*
            'legacy hsl color without % for S/L units' => [
                'hsl(0, 1, 0.5)'
            ],
            'legacy hsl color with invalid unit for H' => [
                'hsl(0px, 100%, 50%)'
            ],
            //*/
            'modern hsl color without slash separator for alpha' => [
                'rgb(0 100% 50% 0.5)',
            ],
            'hsl color with mixed separators, comma first' => [
                'hsl(0, 100% 50%)',
            ],
            'hsl color with mixed separators, space first' => [
                'hsl(0 100%, 50%)',
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideInvalidColor
     */
    public function throwsExceptionWithInvalidColor(string $color): void
    {
        $this->expectException(SourceException::class);

        Color::parse(new ParserState($color, Settings::create()));
    }
}
