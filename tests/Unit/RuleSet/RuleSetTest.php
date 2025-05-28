<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\RuleContainer;
use Sabberworm\CSS\Tests\Unit\RuleSet\Fixtures\ConcreteRuleSet;
use TRegx\PhpUnit\DataProviders\DataProvider;

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
     * @return array<string, array{0: list<non-empty-string>}>
     */
    public static function providePropertyNamesToBeSetInitially(): array
    {
        return [
            'no properties' => [[]],
            'one property' => [['color']],
            'two different properties' => [['color', 'display']],
            'two of the same property' => [['color', 'color']],
        ];
    }

    /**
     * @return array<string, array{0: non-empty-string}>
     */
    public static function providePropertyNameToAdd(): array
    {
        return [
            'property name `color` maybe matching that of existing declaration' => ['color'],
            'property name `display` maybe matching that of existing declaration' => ['display'],
            'property name `width` not matching that of existing declaration' => ['width'],
        ];
    }

    /**
     * @return DataProvider<string, array{0: list<string>, 1: string}>
     */
    public static function provideInitialPropertyNamesAndPropertyNameToAdd(): DataProvider
    {
        return DataProvider::cross(self::providePropertyNamesToBeSetInitially(), self::providePropertyNameToAdd());
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     *
     * @dataProvider provideInitialPropertyNamesAndPropertyNameToAdd
     */
    public function addRuleWithoutSiblingAddsRuleAfterInitialRulesAndSetsValidLineAndColumnNumbers(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->addRule($ruleToAdd);

        $rules = $this->subject->getRules();
        self::assertSame($ruleToAdd, \end($rules));
        self::assertIsInt($ruleToAdd->getLineNumber(), 'line number not set');
        self::assertGreaterThanOrEqual(1, $ruleToAdd->getLineNumber(), 'line number not valid');
        self::assertIsInt($ruleToAdd->getColumnNumber(), 'column number not set');
        self::assertGreaterThanOrEqual(0, $ruleToAdd->getColumnNumber(), 'column number not valid');
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndPropertyNameToAdd
     *
     * @param list<string> $initialPropertyNames
     */
    public function addRuleWithOnlyLineNumberAddsRuleAndSetsColumnNumberPreservingLineNumber(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $ruleToAdd->setPosition(42);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->addRule($ruleToAdd);

        self::assertContains($ruleToAdd, $this->subject->getRules());
        self::assertIsInt($ruleToAdd->getColumnNumber(), 'column number not set');
        self::assertGreaterThanOrEqual(0, $ruleToAdd->getColumnNumber(), 'column number not valid');
        self::assertSame(42, $ruleToAdd->getLineNumber(), 'line number not preserved');
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndPropertyNameToAdd
     *
     * @param list<string> $initialPropertyNames
     */
    public function addRuleWithOnlyColumnNumberAddsRuleAndSetsLineNumberPreservingColumnNumber(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        self::markTestSkipped('currently broken - does not preserve column number');

        $ruleToAdd = new Rule($propertyNameToAdd);
        $ruleToAdd->setPosition(null, 42);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->addRule($ruleToAdd);

        self::assertContains($ruleToAdd, $this->subject->getRules());
        self::assertIsInt($ruleToAdd->getLineNumber(), 'line number not set');
        self::assertGreaterThanOrEqual(1, $ruleToAdd->getLineNumber(), 'line number not valid');
        self::assertSame(42, $ruleToAdd->getColumnNumber(), 'column number not preserved');
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndPropertyNameToAdd
     *
     * @param list<string> $initialPropertyNames
     */
    public function addRuleWithCompletePositionAddsRuleAndPreservesPosition(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $ruleToAdd->setPosition(42, 64);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->addRule($ruleToAdd);

        self::assertContains($ruleToAdd, $this->subject->getRules());
        self::assertSame(42, $ruleToAdd->getLineNumber(), 'line number not preserved');
        self::assertSame(64, $ruleToAdd->getColumnNumber(), 'column number not preserved');
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
     * @test
     *
     * @param list<string> $propertyNamesToRemove
     *
     * @dataProvider providePropertyNamesToBeSetInitially
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
