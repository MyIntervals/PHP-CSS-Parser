<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Tests\Unit\Value\Fixtures\ConcreteValue;
use Sabberworm\CSS\Value\CSSFunction;
use Sabberworm\CSS\Value\Value;

/**
 * @covers \Sabberworm\CSS\Value\Value
 */
final class ValueTest extends TestCase
{
    /**
     * the default set of delimiters for parsing most values
     *
     * @see \Sabberworm\CSS\Rule\Rule::listDelimiterForRule
     *
     * @var list<non-empty-string>
     */
    private const DEFAULT_DELIMITERS = [',', ' ', '/'];

    /**
     * @test
     */
    public function implementsCSSElement(): void
    {
        $subject = new ConcreteValue();

        self::assertInstanceOf(CSSElement::class, $subject);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function provideArithmeticOperator(): array
    {
        return [
            '+' => ['+'],
            '-' => ['-'],
            '*' => ['*'],
            '/' => ['/'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideArithmeticOperator
     */
    public function parsesArithmeticInFunctions(string $operator): void
    {
        $subject = Value::parseValue(
            new ParserState('max(300px, 50vh ' . $operator . ' 10px);', Settings::create()),
            self::DEFAULT_DELIMITERS
        );

        self::assertInstanceOf(CSSFunction::class, $subject);
        self::assertSame('max(300px,50vh ' . $operator . ' 10px)', $subject->render(OutputFormat::createCompact()));
    }

    /**
     * @return array<string, array{'to be parsed': non-empty-string, 'expected': non-empty-string}>
     * The first datum is a template for the parser (using `sprintf` insertion marker `%s` for some expression).
     * The second is for the expected result, which may have whitespace and trailing semicolon removed.
     */
    public static function provideCssFunctionTemplates(): array
    {
        return [
            'calc' => [
                'to be parsed' => 'calc(%s);',
                'expected' => 'calc(%s)',
            ],
            'max' => [
                'to be parsed' => 'max(300px, %s);',
                'expected' => 'max(300px,%s)',
            ],
            'clamp' => [
                'to be parsed' => 'clamp(2.19rem, %s, 2.5rem);',
                'expected' => 'clamp(2.19rem,%s,2.5rem)',
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideCssFunctionTemplates
     */
    public function parsesArithmeticWithMultipleOperatorsInFunctions(
        string $parserTemplate,
        string $expectedResultTemplate
    ): void {
        static $expression = '300px + 10% + 10vw';

        $subject = Value::parseValue(
            new ParserState(\sprintf($parserTemplate, $expression), Settings::create()),
            self::DEFAULT_DELIMITERS
        );

        self::assertInstanceOf(CSSFunction::class, $subject);
        self::assertSame(
            \sprintf($expectedResultTemplate, $expression),
            $subject->render(OutputFormat::createCompact())
        );
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function provideMalformedLengthOperands(): array
    {
        return [
            'LHS missing number' => ['vh', '10px'],
            'RHS missing number' => ['50vh', 'px'],
            'LHS missing unit' => ['50', '10px'],
            'RHS missing unit' => ['50vh', '10'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideMalformedLengthOperands
     */
    public function parsesArithmeticWithMalformedOperandsInFunctions(string $leftOperand, string $rightOperand): void
    {
        $subject = Value::parseValue(
            new ParserState('max(300px, ' . $leftOperand . ' + ' . $rightOperand . ');', Settings::create()),
            self::DEFAULT_DELIMITERS
        );

        self::assertInstanceOf(CSSFunction::class, $subject);
        self::assertSame(
            'max(300px,' . $leftOperand . ' + ' . $rightOperand . ')',
            $subject->render(OutputFormat::createCompact())
        );
    }
}
