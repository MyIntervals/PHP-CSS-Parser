<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Tests\Unit\Value\Fixtures\ConcreteValue;

/**
 * @covers \Sabberworm\CSS\Value\Value
 */
final class ValueTest extends TestCase
{
    /**
     * @test
     */
    public function implementsCSSElement(): void
    {
        $subject = new ConcreteValue();

        self::assertInstanceOf(CSSElement::class, $subject);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new ConcreteValue();

        $result = $subject->getArrayRepresentation();

        self::assertSame('ConcreteValue', $result['class']);
    }

    /**
     * @return \Generator<string, array{0: string, 1: string}>
     */
    public static function provideIdentifiersStartingWithColorFunctionName(): \Generator
    {
        yield 'rgb prefix' => ['animation-name:rgbshift', 'animation-name: rgbshift;'];
        yield 'hsl prefix' => ['font-family:hslfont', 'font-family: hslfont;'];
        yield 'uppercase prefix' => ['animation-name:RGBshift', 'animation-name: RGBshift;'];
        yield 'bare rgb' => ['animation-name:rgb', 'animation-name: rgb;'];
        yield 'color function' => ['color:rgb(1,2,3)', 'color: #010203;'];
        yield 'color function after prefixed identifier' => [
            'animation-name:rgbshift;color:hsl(0,0%,0%)',
            'animation-name: rgbshift;color: hsl(0,0%,0%);',
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideIdentifiersStartingWithColorFunctionName
     */
    public function parsesIdentifiersStartingWithColorFunctionNameAsIdentifiers(
        string $declarations,
        string $expected
    ): void {
        $result = (new Parser('a{' . $declarations . '}'))->parse()->render();

        self::assertSame('a {' . $expected . '}', $result);
    }
}
