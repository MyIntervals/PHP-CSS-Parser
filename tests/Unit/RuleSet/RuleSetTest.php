<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\RuleSet;

/**
 * @covers \Sabberworm\CSS\RuleSet\RuleSet
 */
final class RuleSetTest extends TestCase
{
    use RuleContainerTest;

    /**
     * @var RuleSet
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new RuleSet();
    }

    /**
     * @test
     */
    public function implementsCSSElement(): void
    {
        self::assertInstanceOf(CSSElement::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsCSSListItem(): void
    {
        self::assertInstanceOf(CSSListItem::class, $this->subject);
    }

    /**
     * @return array<string, array{0: list<array{name: string, value: string}>, 1: string}>
     */
    public static function providePropertyNamesAndValuesAndExpectedCss(): array
    {
        return [
            'no properties' => [[], ''],
            'one property' => [
                [['name' => 'color', 'value' => 'green']],
                'color: green;',
            ],
            'two different properties' => [
                [
                    ['name' => 'color', 'value' => 'green'],
                    ['name' => 'display', 'value' => 'block'],
                ],
                'color: green;display: block;',
            ],
            'two of the same property' => [
                [
                    ['name' => 'color', 'value' => '#40A040'],
                    ['name' => 'color', 'value' => 'rgba(0, 128, 0, 0.25)'],
                ],
                'color: #40A040;color: rgba(0, 128, 0, 0.25);',
            ],
        ];
    }

    /**
     * @test
     *
     * @param list<array{name: string, value: string}> $propertyNamesAndValuesToSet
     *
     * @dataProvider providePropertyNamesAndValuesAndExpectedCss
     */
    public function renderReturnsCssForRulesSet(array $propertyNamesAndValuesToSet, string $expectedCss): void
    {
        $rulesToSet = \array_map(
            /**
             * @param array{name: string, value: string} $nameAndValue
             */
            static function (array $nameAndValue): Rule {
                $rule = new Rule($nameAndValue['name']);
                $rule->setValue($nameAndValue['value']);
                return $rule;
            },
            $propertyNamesAndValuesToSet
        );
        $this->subject->setRules($rulesToSet);

        $result = $this->subject->render(OutputFormat::create());

        self::assertSame($expectedCss, $result);
    }
}
