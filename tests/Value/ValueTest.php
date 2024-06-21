<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\Value;

/**
 * @covers \Sabberworm\CSS\Value\Value
 */
final class ValueTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function provideArithmeticOperator(): array
    {
        $units = ['+', '-', '*', '/'];

        return \array_combine(
            $units,
            \array_map(
                function (string $unit): array {
                    return [$unit];
                },
                $units
            )
        );
    }

    /**
     * @test
     *
     * @dataProvider provideArithmeticOperator
     */
    public function parsesArithmeticInFunctions(string $operator): void
    {
        $subject = Value::parseValue(new ParserState('max(300px, 50vh ' . $operator . ' 10px);', Settings::create()));

        self::assertSame('max(300px,50vh ' . $operator . ' 10px)', (string) $subject);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
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

        $subject = Value::parseValue(new ParserState(\sprintf($parserTemplate, $expression), Settings::create()));

        self::assertSame(\sprintf($expectedResultTemplate, $expression), (string) $subject);
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
        $subject = Value::parseValue(new ParserState(
            'max(300px, ' . $leftOperand . ' + ' . $rightOperand . ');',
            Settings::create()
        ));

        self::assertSame('max(300px,' . $leftOperand . ' + ' . $rightOperand . ')', (string) $subject);
    }
}
