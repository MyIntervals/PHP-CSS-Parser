<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
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

    /**
     * @test
     */
    public function parseNestedCalc(): void
    {
        $css = 'calc(100% - calc(20px + 1em))';
        $calcFunction = $this->parse($css);

        /** @var CalcRuleValueList $value */
        $value = $calcFunction->getArguments()[0];
        $components = $value->getListComponents();

        self::assertCount(3, $components);
        self::assertSame('-', $components[1]);

        $nestedCalc = $components[2];
        self::assertInstanceOf(CalcFunction::class, $nestedCalc);

        $nestedValue = $nestedCalc->getArguments()[0];
        self::assertInstanceOf(CalcRuleValueList::class, $nestedValue);
        $nestedComponents = $nestedValue->getListComponents();

        self::assertCount(3, $nestedComponents);
        self::assertSame('+', $nestedComponents[1]);
    }

    /**
     * @test
     */
    public function parseWithParentheses(): void
    {
        $css = 'calc((100% - 20px) * 2)';
        $calcFunction = $this->parse($css);

        /** @var CalcRuleValueList $value */
        $value = $calcFunction->getArguments()[0];
        $components = $value->getListComponents();

        self::assertCount(7, $components);
        self::assertSame('(', $components[0]);
        self::assertInstanceOf(Size::class, $components[1]); // 100%
        self::assertSame('-', $components[2]);
        self::assertInstanceOf(Size::class, $components[3]); // 20px
        self::assertSame(')', $components[4]);
        self::assertSame('*', $components[5]);
        self::assertInstanceOf(Size::class, $components[6]); // 2
    }

    /**
     * @return array<string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public function provideValidOperatorSyntax(): array
    {
        return [
            '+ op' => ['calc(100% + 20px)', 'calc(100% + 20px)'],
            '- op' => ['calc(100% - 20px)', 'calc(100% - 20px)'],
            '* op' => ['calc(100% * 20)', 'calc(100% * 20)'],
            '* op no space' => ['calc(100%*20)', 'calc(100% * 20)'],
            '/ op' => ['calc(100% / 20)', 'calc(100% / 20)'],
            '/ op no space' => ['calc(100%/20)', 'calc(100% / 20)'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideValidOperatorSyntax
     */
    public function parseValidOperators(string $css, string $rendered): void
    {
        $calcFunction = $this->parse($css);
        $output = $calcFunction->render(OutputFormat::create());
        self::assertSame($rendered, $output);
    }

    /**
     * @return array<string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public function provideMultiline(): array
    {
        return [
            'right newline' => ["calc(100% +\n20px)", 'calc(100% + 20px)'],
            'right and outer newline' => ["calc(\n100% +\n20px\n)", 'calc(100% + 20px)'],
            'left newline' => ["calc(100%\n+ 20px)", 'calc(100% + 20px)'],
            'both newline' => ["calc(100%\n+\n20px)", 'calc(100% + 20px)'],
            'tab whitespace' => ["calc(100%\t+\t20px)", 'calc(100% + 20px)'],
            '- op' => ["calc(100%\n-\n20px)", 'calc(100% - 20px)'],
            '/ op' => ["calc(100% /\n20)", 'calc(100% / 20)'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideMultiline
     */
    public function parseMultiline(string $css, string $rendered): void
    {
        $calcFunction = $this->parse($css);
        $output = $calcFunction->render(OutputFormat::create());
        self::assertSame($rendered, $output);
    }

    /**
     * @return array<string, array{0: non-empty-string}>
     */
    public function provideInvalidSyntax(): array
    {
        return [
            'missing space around -' => ['calc(100%-20px)'],
            'missing space around +' => ['calc(100%+20px)'],
            'invalid operator' => ['calc(100% ^ 20px)'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideInvalidSyntax
     */
    public function parseThrowsExceptionForInvalidSyntax(string $css): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->parse($css);
    }

    /**
     * @test
     */
    public function parseThrowsExceptionIfCalledWithWrongFunctionName(): void
    {
        $css = 'wrong(100% - 20px)';
        $parserState = new ParserState($css, Settings::create());

        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage('calc');
        CalcFunction::parse($parserState);
    }

    /**
     * Parse provided CSS as a CalcFunction
     */
    private function parse(string $css): CalcFunction
    {
        $parserState = new ParserState($css, Settings::create());

        $function = CalcFunction::parse($parserState);
        self::assertInstanceOf(CalcFunction::class, $function);
        return $function;
    }

    /**
     * @test
     */
    public function getArrayRepresentationThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $subject = new CalcFunction('calc', []);

        $subject->getArrayRepresentation();
    }
}
