<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\RuleContainer;
use Sabberworm\CSS\Tests\Unit\RuleSet\Fixtures\ConcreteRuleSet;

/**
 * @covers \Sabberworm\CSS\RuleSet\RuleSet
 */
final class RuleSetTest extends TestCase
{
    /**
     * @var ConcreteRuleSet
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ConcreteRuleSet();
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
     * @test
     */
    public function implementsRuleContainer(): void
    {
        self::assertInstanceOf(RuleContainer::class, $this->subject);
    }

    /**
     * @return array<string, array{0: list<Rule>, 1: string, 2: list<string>}>
     */
    public static function provideRulesAndPropertyNameToRemoveAndExpectedRemainingPropertyNames(): array
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
     * @param list<string> $expectedRemainingPropertyNames
     *
     * @dataProvider provideRulesAndPropertyNameToRemoveAndExpectedRemainingPropertyNames
     */
    public function removeMatchingRulesRemovesRulesByPropertyNameAndKeepsOthers(
        array $rules,
        string $propertyName,
        array $expectedRemainingPropertyNames
    ): void {
        $this->subject->setRules($rules);

        $this->subject->removeMatchingRules($propertyName);

        $remainingRules = $this->subject->getRulesAssoc();
        self::assertArrayNotHasKey($propertyName, $remainingRules);
        foreach ($expectedRemainingPropertyNames as $expectedPropertyName) {
            self::assertArrayHasKey($expectedPropertyName, $remainingRules);
        }
    }

    /**
     * @return array<string, array{0: list<Rule>, 1: string, 2: list<string>}>
     */
    public static function provideRulesAndPropertyNamePrefixToRemoveAndExpectedRemainingPropertyNames(): array
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
     * @param list<string> $expectedRemainingPropertyNames
     *
     * @dataProvider provideRulesAndPropertyNamePrefixToRemoveAndExpectedRemainingPropertyNames
     */
    public function removeMatchingRulesRemovesRulesByPropertyNamePrefixAndKeepsOthers(
        array $rules,
        string $propertyNamePrefix,
        array $expectedRemainingPropertyNames
    ): void {
        $propertyNamePrefixWithHyphen = $propertyNamePrefix . '-';
        $this->subject->setRules($rules);

        $this->subject->removeMatchingRules($propertyNamePrefixWithHyphen);

        $remainingRules = $this->subject->getRulesAssoc();
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
    public static function provideRulesToRemove(): array
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
    public function removeAllRulesRemovesAllRules(array $rules): void
    {
        $this->subject->setRules($rules);

        $this->subject->removeAllRules();

        self::assertSame([], $this->subject->getRules());
    }
}
