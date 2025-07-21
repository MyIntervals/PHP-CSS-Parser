<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\RuleSet;

/**
 * @covers \Sabberworm\CSS\RuleSet\RuleSet
 */
final class RuleSetTest extends TestCase
{
    /**
     * @var RuleSet
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new RuleSet();
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
        $this->setRulesFromPropertyNamesAndValues($propertyNamesAndValuesToSet);

        $result = $this->subject->render(OutputFormat::create());

        self::assertSame($expectedCss, $result);
    }

    /**
     * @test
     */
    public function renderWithCompactOutputFormatReturnsCssWithoutWhitespaceOrTrailingSemicolon(): void
    {
        $this->setRulesFromPropertyNamesAndValues([
            ['name' => 'color', 'value' => 'green'],
            ['name' => 'display', 'value' => 'block'],
        ]);

        $result = $this->subject->render(OutputFormat::createCompact());

        self::assertSame('color:green;display:block', $result);
    }

    /**
     * @test
     */
    public function renderWithPrettyOutputFormatReturnsCssWithNewlinesAroundIndentedDeclarations(): void
    {
        $this->setRulesFromPropertyNamesAndValues([
            ['name' => 'color', 'value' => 'green'],
            ['name' => 'display', 'value' => 'block'],
        ]);

        $result = $this->subject->render(OutputFormat::createPretty());

        self::assertSame("\n\tcolor: green;\n\tdisplay: block;\n", $result);
    }

    /**
     * @param list<array{name: string, value: string}> $propertyNamesAndValues
     */
    private function setRulesFromPropertyNamesAndValues(array $propertyNamesAndValues): void
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
            $propertyNamesAndValues
        );
        $this->subject->setRules($rulesToSet);
    }
}
