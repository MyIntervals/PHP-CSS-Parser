<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\RuleSet;
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
     * @return array<string, array{0: list<string>, 1: string, 2: list<string>}>
     */
    public static function providePropertyNamesAndPropertyNameToRemoveAndExpectedRemainingPropertyNames()
    {
        return [
            'removing single rule' => [
                ['color'],
                'color',
                [],
            ],
            'removing first rule' => [
                ['color', 'display'],
                'color',
                ['display'],
            ],
            'removing last rule' => [
                ['color', 'display'],
                'display',
                ['color'],
            ],
            'removing middle rule' => [
                ['color', 'display', 'width'],
                'display',
                ['color', 'width'],
            ],
            'removing multiple rules' => [
                ['color', 'color'],
                'color',
                [],
            ],
            'removing multiple rules with another kept' => [
                ['color', 'color', 'display'],
                'color',
                ['display'],
            ],
            'removing nonexistent rule from empty list' => [
                [],
                'color',
                [],
            ],
            'removing nonexistent rule from nonempty list' => [
                ['color', 'display'],
                'width',
                ['color', 'display'],
            ],
        ];
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     * @param string $propertyNameToRemove
     * @param list<string> $expectedRemainingPropertyNames
     *
     * @dataProvider providePropertyNamesAndPropertyNameToRemoveAndExpectedRemainingPropertyNames
     */
    public function removeMatchingRulesRemovesRulesByPropertyNameAndKeepsOthers(
        array $initialPropertyNames,
        $propertyNameToRemove,
        array $expectedRemainingPropertyNames
    ) {
        $subject = new ConcreteRuleSet();
        self::setRulesFromPropertyNames($subject, $initialPropertyNames);

        $subject->removeMatchingRules($propertyNameToRemove);

        $remainingRules = $subject->getRulesAssoc();
        self::assertArrayNotHasKey($propertyNameToRemove, $remainingRules);
        foreach ($expectedRemainingPropertyNames as $expectedPropertyName) {
            self::assertArrayHasKey($expectedPropertyName, $remainingRules);
        }
    }

    /**
     * @return array<string, array{0: list<string>, 1: string, 2: list<string>}>
     */
    public static function providePropertyNamesAndPropertyNamePrefixToRemoveAndExpectedRemainingPropertyNames()
    {
        return [
            'removing shorthand rule' => [
                ['font'],
                'font',
                [],
            ],
            'removing longhand rule' => [
                ['font-size'],
                'font',
                [],
            ],
            'removing shorthand and longhand rule' => [
                ['font', 'font-size'],
                'font',
                [],
            ],
            'removing shorthand rule with another kept' => [
                ['font', 'color'],
                'font',
                ['color'],
            ],
            'removing longhand rule with another kept' => [
                ['font-size', 'color'],
                'font',
                ['color'],
            ],
            'keeping other rules whose property names begin with the same characters' => [
                ['contain', 'container', 'container-type'],
                'contain',
                ['container', 'container-type'],
            ],
        ];
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     * @param string $propertyNamePrefix
     * @param list<string> $expectedRemainingPropertyNames
     *
     * @dataProvider providePropertyNamesAndPropertyNamePrefixToRemoveAndExpectedRemainingPropertyNames
     */
    public function removeMatchingRulesRemovesRulesByPropertyNamePrefixAndKeepsOthers(
        array $initialPropertyNames,
        $propertyNamePrefix,
        array $expectedRemainingPropertyNames
    ) {
        $propertyNamePrefixWithHyphen = $propertyNamePrefix . '-';
        $subject = new ConcreteRuleSet();
        self::setRulesFromPropertyNames($subject, $initialPropertyNames);

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
     * @return array<string, array{0: list<string>}>
     */
    public static function providePropertyNamesToRemove()
    {
        return [
            'no properties' => [[]],
            'one property' => [['color']],
            'two different properties' => [['color', 'display']],
            'two of the same property' => [['color', 'color']],
        ];
    }

    /**
     * @test
     *
     * @param list<string> $propertyNamesToRemove
     *
     * @dataProvider providePropertyNamesToRemove
     */
    public function removeAllRulesRemovesAllRules(array $propertyNamesToRemove)
    {
        $subject = new ConcreteRuleSet();
        self::setRulesFromPropertyNames($subject, $propertyNamesToRemove);

        $subject->removeAllRules();

        self::assertSame([], $subject->getRules());
    }

    /**
     * @param list<string> $propertyNames
     */
    private static function setRulesFromPropertyNames(RuleSet $subject, array $propertyNames)
    {
        $subject->setRules(\array_map(
            function ($propertyName) {
                return new Rule($propertyName);
            },
            $propertyNames
        ));
    }
}
