<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\FunctionalDeprecated\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\CalcFunction;
use Sabberworm\CSS\Value\CalcRuleValueList;
use Sabberworm\CSS\Value\Size;

/**
 * @covers \Sabberworm\CSS\Value\CalcFunction
 */
final class CalcFunctionTest extends TestCase
{
    /**
     * Parse provided CSS as a `CalcFunction`
     */
    private function parse(string $css): CalcFunction
    {
        $parserState = new ParserState($css, Settings::create());

        $function = CalcFunction::parse($parserState);
        self::assertInstanceOf(CalcFunction::class, $function);

        return $function;
    }

    /**
     * This exercises the deprecated `Size::getSize()` which has been replaced with `getNumber()` which is exercised in
     * the non-deprecated equivalent `TestCase`.
     *
     * @test
     */
    public function parseSimpleCalc(): void
    {
        $css = 'calc(100% - 20px)';
        $calcFunction = $this->parse($css);

        self::assertInstanceOf(CalcFunction::class, $calcFunction);
        self::assertSame('calc', $calcFunction->getName());

        $args = $calcFunction->getArguments();
        self::assertCount(1, $args);
        self::assertInstanceOf(CalcRuleValueList::class, $args[0]);

        $value = $args[0];
        $components = $value->getListComponents();
        self::assertCount(3, $components); // 100%, -, 20px

        self::assertInstanceOf(Size::class, $components[0]);
        self::assertSame(100.0, $components[0]->getSize());
        self::assertSame('%', $components[0]->getUnit());

        self::assertSame('-', $components[1]);

        self::assertInstanceOf(Size::class, $components[2]);
        self::assertSame(20.0, $components[2]->getSize());
        self::assertSame('px', $components[2]->getUnit());
    }
}
