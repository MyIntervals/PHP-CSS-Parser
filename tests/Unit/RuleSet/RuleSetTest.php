<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\Tests\Unit\RuleSet\Fixtures\ConcreteRuleSet;

/**
 * @covers \Sabberworm\CSS\RuleSet\RuleSet
 */
final class RuleSetTest extends TestCase
{
    /**
     * @test
     *
     * @return void
     */
    public function implementsCSSElement()
    {
        $subject = new ConcreteRuleSet();

        self::assertInstanceOf(CSSElement::class, $subject);
    }

    /**
     * @return array<string, array{0: list<Rule>, 1: string, 2: list<string>}>
     */
    public static function provideRulesAndPropertyNameToRemoveAndExpectedRemainingPropertyNames()
    {
        return [
            'removing single rule' => [
                [new Rule('color')],
                'color',
                [],
            ],
            'removing first rule' => [
                [new Rule('color'), new Rule('display')],
                'color',
                ['display'],
            ],
            'removing last rule' => [
                [new Rule('color'), new Rule('display')],
                'display',
                ['color'],
            ],
            'removing middle rule' => [
                [new Rule('color'), new Rule('display'), new Rule('width')],
                'display',
                ['color', 'width'],
            ],
            'removing multiple rules' => [
                [new Rule('color'), new Rule('color')],
                'color',
                [],
            ],
            'removing multiple rules with another kept' => [
                [new Rule('color'), new Rule('color'), new Rule('display')],
                'color',
                ['display'],
            ],
            'removing nonexistent rule from empty list' => [
                [],
                'color',
                [],
            ],
            'removing nonexistent rule from nonempty list' => [
                [new Rule('color'), new Rule('display')],
                'width',
                ['color', 'display'],
            ],
        ];
    }

    /**
     * @test
     *
     * @param list<Rule> $rules
     * @param string $propertyName
     * @param list<string> $expectedRemainingPropertyNames
     *
     * @dataProvider provideRulesAndPropertyNameToRemoveAndExpectedRemainingPropertyNames
     */
    public function removeMatchingRulesRemovesRulesByPropertyNameAndKeepsOthers(
        array $rules,
        $propertyName,
        array $expectedRemainingPropertyNames
    ) {
        $subject = new ConcreteRuleSet();
        $subject->setRules($rules);

        $subject->removeMatchingRules($propertyName);

        $remainingRules = $subject->getRulesAssoc();
        self::assertArrayNotHasKey($propertyName, $remainingRules);
        foreach ($expectedRemainingPropertyNames as $expectedPropertyName) {
            self::assertArrayHasKey($expectedPropertyName, $remainingRules);
        }
    }

    /**
     * @return array<string, array{0: list<Rule>, 1: string, 2: list<string>}>
     */
    public static function provideRulesAndPropertyNamePrefixToRemoveAndExpectedRemainingPropertyNames()
    {
        return [
            'removing shorthand rule' => [
                [new Rule('font')],
                'font',
                [],
            ],
            'removing longhand rule' => [
                [new Rule('font-size')],
                'font',
                [],
            ],
            'removing shorthand and longhand rule' => [
                [new Rule('font'), new Rule('font-size')],
                'font',
                [],
            ],
            'removing shorthand rule with another kept' => [
                [new Rule('font'), new Rule('color')],
                'font',
                ['color'],
            ],
            'removing longhand rule with another kept' => [
                [new Rule('font-size'), new Rule('color')],
                'font',
                ['color'],
            ],
            'keeping other rules whose property names begin with the same characters' => [
                [new Rule('contain'), new Rule('container'), new Rule('container-type')],
                'contain',
                ['container', 'container-type'],
            ],
        ];
    }

    /**
     * @test
     *
     * @param list<Rule> $rules
     * @param string $propertyNamePrefix
     * @param list<string> $expectedRemainingPropertyNames
     *
     * @dataProvider provideRulesAndPropertyNamePrefixToRemoveAndExpectedRemainingPropertyNames
     */
    public function removeMatchingRulesRemovesRulesByPropertyNamePrefixAndKeepsOthers(
        array $rules,
        $propertyNamePrefix,
        array $expectedRemainingPropertyNames
    ) {
        $propertyNamePrefixWithHyphen = $propertyNamePrefix . '-';
        $subject = new ConcreteRuleSet();
        $subject->setRules($rules);

        $subject->removeMatchingRules($propertyNamePrefixWithHyphen);

        $remainingRules = $subject->getRulesAssoc();
        self::assertArrayNotHasKey($propertyNamePrefix, $remainingRules);
        foreach (\array_keys($remainingRules) as $remainingPropertyName) {
            self::assertStringStartsNotWith($propertyNamePrefixWithHyphen, $remainingPropertyName);
        }
        foreach ($expectedRemainingPropertyNames as $expectedPropertyName) {
            self::assertArrayHasKey($expectedPropertyName, $remainingRules);
        }
    }

    /**
     * @return array<string, array{0: list<Rule>}>
     */
    public static function provideRulesToRemove()
    {
        return [
            'no rules' => [[]],
            'one rule' => [[new Rule('color')]],
            'two rules for different properties' => [[new Rule('color'), new Rule('display')]],
            'two rules for the same property' => [[new Rule('color'), new Rule('color')]],
        ];
    }

    /**
     * @test
     *
     * @param list<Rule> $rules
     *
     * @dataProvider provideRulesToRemove
     */
    public function removeAllRulesRemovesAllRules(array $rules)
    {
        $subject = new ConcreteRuleSet();
        $subject->setRules($rules);

        $subject->removeAllRules();

        self::assertSame([], $subject->getRules());
    }
}
