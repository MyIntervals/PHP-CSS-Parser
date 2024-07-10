<?php

namespace Sabberworm\CSS\Tests\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\Value;
use Sabberworm\CSS\Value\ValueList;
use Sabberworm\CSS\Value\Expression;
use Sabberworm\CSS\Rule\Rule;

/**
 * @covers \Sabberworm\CSS\Value\Expression
 */
final class ExpressionTest extends TestCase
{
    /**
     * @return array<0, array{string: string}>
     */
    public static function provideExpressions(): array
    {
        return [
            [
                'input' => '(vh - 10) / 2',
                'expected_output' => '(vh - 10)/2',
                'expression_index' => 0,
            ],
            [
                'input' => 'max(5, (vh - 10))',
                'expected_output' => 'max(5,(vh - 10))',
                'expression_index' => 1
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideExpressions
     */
    public function parseExpressions(string $input, string $expected, int $expression_index): void
    {
        $val = Value::parseValue(
            new ParserState($input, Settings::create()),
            $this->getDelimiters('height')
        );

        self::assertInstanceOf(ValueList::class, $val);
        self::assertInstanceOf(Expression::class, $val->getListComponents()[$expression_index]);
        self::assertSame($expected, (string) $val);
    }

    private function getDelimiters(string $rule): array
    {
        $closure = function($rule) {
            return self::listDelimiterForRule($rule);
        };

        $getter = $closure->bindTo(null, Rule::class);
        return $getter($rule);
    }
}
