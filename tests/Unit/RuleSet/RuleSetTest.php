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
     * @return array<string, array{0: list<string>, 1: string, 2: list<string>}>
     */
    public static function providePropertyNamesAndPropertyNameToRemoveAndExpectedRemainingPropertyNames(): array
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
     * @param list<string> $expectedRemainingPropertyNames
     *
     * @dataProvider providePropertyNamesAndPropertyNameToRemoveAndExpectedRemainingPropertyNames
     */
    public function removeMatchingRulesRemovesRulesByPropertyNameAndKeepsOthers(
        array $initialPropertyNames,
        string $propertyNameToRemove,
        array $expectedRemainingPropertyNames
    ): void {
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->removeMatchingRules($propertyNameToRemove);

        $remainingRules = $this->subject->getRulesAssoc();
        self::assertArrayNotHasKey($propertyNameToRemove, $remainingRules);
        foreach ($expectedRemainingPropertyNames as $expectedPropertyName) {
            self::assertArrayHasKey($expectedPropertyName, $remainingRules);
        }
    }

    /**
     * @return array<string, array{0: list<string>, 1: string, 2: list<string>}>
     */
    public static function providePropertyNamesAndPropertyNamePrefixToRemoveAndExpectedRemainingPropertyNames(): array
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
     * @param list<string> $expectedRemainingPropertyNames
     *
     * @dataProvider providePropertyNamesAndPropertyNamePrefixToRemoveAndExpectedRemainingPropertyNames
     */
    public function removeMatchingRulesRemovesRulesByPropertyNamePrefixAndKeepsOthers(
        array $initialPropertyNames,
        string $propertyNamePrefix,
        array $expectedRemainingPropertyNames
    ): void {
        $propertyNamePrefixWithHyphen = $propertyNamePrefix . '-';
        $this->setRulesFromPropertyNames($initialPropertyNames);

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
     * @return array<string, array{0: list<string>}>
     */
    public static function providePropertyNamesToRemove(): array
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
    public function removeAllRulesRemovesAllRules(array $propertyNamesToRemove): void
    {
        $this->setRulesFromPropertyNames($propertyNamesToRemove);

        $this->subject->removeAllRules();

        self::assertSame([], $this->subject->getRules());
    }

    /**
     * @param list<string> $propertyNames
     */
    private function setRulesFromPropertyNames(array $propertyNames): void
    {
        $this->subject->setRules(\array_map(
            static function (string $propertyName): Rule {
                return new Rule($propertyName);
            },
            $propertyNames
        ));
    }
}
