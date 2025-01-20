<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
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
            'legacy rgba with fractional alpha' => [
                'rgba(0, 119, 0, 0.5)',
                'rgba(0,119,0,.5)',
            ],
            'legacy rgba with percentage alpha' => [
                'rgba(0, 119, 0, 50%)',
                'rgba(0,119,0,50%)',
            ],
            'legacy rgb as rgba' => [
                'rgba(0, 119, 0)',
                '#070',
            ],
            'legacy rgba as rgb' => [
                'rgb(0, 119, 0, 0.5)',
                'rgba(0,119,0,.5)',
            ],
            /*
            'modern rgb' => [
                'rgb(0 119 0)',
                'rgb(0,119,0)',
            ],
            'modern rgb with none' => [
                'rgb(none 119 0)',
                'rgb(none 119 0)',
            ],
            'modern rgba' => [
                'rgb(0 119 0 / 0.5)',
                'rgba(0,119,0,.5)',
            ],
            //*/
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

        $renderedResult = (string) $subject;

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
            /*
            'legacy hsl color without % for S/L units' => [
                'hsl(0, 1, 0.5)'
            ],
            'legacy hsl color with invalid unit for H' => [
                'hsl(0px, 100%, 50%)'
            ],
            //*/
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
