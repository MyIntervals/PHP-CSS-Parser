<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\RuleContainer;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * This trait provides test methods for unit-testing classes that implement `RuleContainer`.
 * It can be `use`d in a `TestCase` which has a `$subject` property that is an instance of the implementing class
 * (the class under test), `setUp()` with default values.
 *
 * @phpstan-require-extends TestCase
 */
trait RuleContainerTest
{
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
    public static function providePropertyNames(): array
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
    public static function provideAnotherPropertyName(): array
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
    public static function provideInitialPropertyNamesAndAnotherPropertyName(): DataProvider
    {
        return DataProvider::cross(self::providePropertyNames(), self::provideAnotherPropertyName());
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     */
    public function addRuleWithoutPositionWithoutSiblingAddsRuleAfterInitialRules(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->addRule($ruleToAdd);

        $rules = $this->subject->getRules();
        self::assertSame($ruleToAdd, \end($rules));
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     */
    public function addRuleWithoutPositionWithoutSiblingSetsValidLineNumber(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->addRule($ruleToAdd);

        self::assertIsInt($ruleToAdd->getLineNumber(), 'line number not set');
        self::assertGreaterThanOrEqual(1, $ruleToAdd->getLineNumber(), 'line number not valid');
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     */
    public function addRuleWithoutPositionWithoutSiblingSetsValidColumnNumber(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->addRule($ruleToAdd);

        self::assertIsInt($ruleToAdd->getColumnNumber(), 'column number not set');
        self::assertGreaterThanOrEqual(0, $ruleToAdd->getColumnNumber(), 'column number not valid');
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     *
     * @param list<string> $initialPropertyNames
     */
    public function addRuleWithOnlyLineNumberWithoutSiblingAddsRule(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $ruleToAdd->setPosition(42);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->addRule($ruleToAdd);

        self::assertContains($ruleToAdd, $this->subject->getRules());
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     *
     * @param list<string> $initialPropertyNames
     */
    public function addRuleWithOnlyLineNumberWithoutSiblingSetsColumnNumber(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $ruleToAdd->setPosition(42);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->addRule($ruleToAdd);

        self::assertIsInt($ruleToAdd->getColumnNumber(), 'column number not set');
        self::assertGreaterThanOrEqual(0, $ruleToAdd->getColumnNumber(), 'column number not valid');
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     *
     * @param list<string> $initialPropertyNames
     */
    public function addRuleWithOnlyLineNumberWithoutSiblingPreservesLineNumber(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $ruleToAdd->setPosition(42);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->addRule($ruleToAdd);

        self::assertSame(42, $ruleToAdd->getLineNumber(), 'line number not preserved');
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     *
     * @param list<string> $initialPropertyNames
     */
    public function addRuleWithOnlyColumnNumberWithoutSiblingAddsRuleAfterInitialRules(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $ruleToAdd->setPosition(null, 42);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->addRule($ruleToAdd);

        $rules = $this->subject->getRules();
        self::assertSame($ruleToAdd, \end($rules));
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     *
     * @param list<string> $initialPropertyNames
     */
    public function addRuleWithOnlyColumnNumberWithoutSiblingSetsLineNumber(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $ruleToAdd->setPosition(null, 42);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->addRule($ruleToAdd);

        self::assertIsInt($ruleToAdd->getLineNumber(), 'line number not set');
        self::assertGreaterThanOrEqual(1, $ruleToAdd->getLineNumber(), 'line number not valid');
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     *
     * @param list<string> $initialPropertyNames
     */
    public function addRuleWithOnlyColumnNumberWithoutSiblingPreservesColumnNumber(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $ruleToAdd->setPosition(null, 42);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->addRule($ruleToAdd);

        self::assertSame(42, $ruleToAdd->getColumnNumber(), 'column number not preserved');
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     *
     * @param list<string> $initialPropertyNames
     */
    public function addRuleWithCompletePositionWithoutSiblingAddsRule(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $ruleToAdd->setPosition(42, 64);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->addRule($ruleToAdd);

        self::assertContains($ruleToAdd, $this->subject->getRules());
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     *
     * @param list<string> $initialPropertyNames
     */
    public function addRuleWithCompletePositionWithoutSiblingPreservesPosition(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $ruleToAdd->setPosition(42, 64);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->addRule($ruleToAdd);

        self::assertSame(42, $ruleToAdd->getLineNumber(), 'line number not preserved');
        self::assertSame(64, $ruleToAdd->getColumnNumber(), 'column number not preserved');
    }

    /**
     * @return array<string, array{0: non-empty-list<non-empty-string>, 1: int<0, max>}>
     */
    public static function provideInitialPropertyNamesAndIndexOfOne(): array
    {
        $initialPropertyNamesSets = self::providePropertyNames();

        // Provide sets with each possible index for the initially set `Rule`s.
        $initialPropertyNamesAndIndexSets = [];
        foreach ($initialPropertyNamesSets as $setName => $data) {
            $initialPropertyNames = $data[0];
            for ($index = 0; $index < \count($initialPropertyNames); ++$index) {
                $initialPropertyNamesAndIndexSets[$setName . ', index ' . $index] =
                    [$initialPropertyNames, $index];
            }
        }

        return $initialPropertyNamesAndIndexSets;
    }

    /**
     * @return DataProvider<string, array{0: non-empty-list<string>, 1: int<0, max>, 2: string}>
     */
    public static function provideInitialPropertyNamesAndSiblingIndexAndPropertyNameToAdd(): DataProvider
    {
        return DataProvider::cross(
            self::provideInitialPropertyNamesAndIndexOfOne(),
            self::provideAnotherPropertyName()
        );
    }

    /**
     * @test
     *
     * @param non-empty-list<string> $initialPropertyNames
     * @param int<0, max> $siblingIndex
     *
     * @dataProvider provideInitialPropertyNamesAndSiblingIndexAndPropertyNameToAdd
     */
    public function addRuleWithSiblingInsertsRuleBeforeSibling(
        array $initialPropertyNames,
        int $siblingIndex,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $this->setRulesFromPropertyNames($initialPropertyNames);
        $sibling = $this->subject->getRules()[$siblingIndex];

        $this->subject->addRule($ruleToAdd, $sibling);

        $rules = $this->subject->getRules();
        $siblingPosition = \array_search($sibling, $rules, true);
        self::assertIsInt($siblingPosition);
        self::assertSame($siblingPosition - 1, \array_search($ruleToAdd, $rules, true));
    }

    /**
     * @test
     *
     * @param non-empty-list<string> $initialPropertyNames
     * @param int<0, max> $siblingIndex
     *
     * @dataProvider provideInitialPropertyNamesAndSiblingIndexAndPropertyNameToAdd
     */
    public function addRuleWithSiblingSetsValidLineNumber(
        array $initialPropertyNames,
        int $siblingIndex,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $this->setRulesFromPropertyNames($initialPropertyNames);
        $sibling = $this->subject->getRules()[$siblingIndex];

        $this->subject->addRule($ruleToAdd, $sibling);

        self::assertIsInt($ruleToAdd->getLineNumber(), 'line number not set');
        self::assertGreaterThanOrEqual(1, $ruleToAdd->getLineNumber(), 'line number not valid');
    }

    /**
     * @test
     *
     * @param non-empty-list<string> $initialPropertyNames
     * @param int<0, max> $siblingIndex
     *
     * @dataProvider provideInitialPropertyNamesAndSiblingIndexAndPropertyNameToAdd
     */
    public function addRuleWithSiblingSetsValidColumnNumber(
        array $initialPropertyNames,
        int $siblingIndex,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $this->setRulesFromPropertyNames($initialPropertyNames);
        $sibling = $this->subject->getRules()[$siblingIndex];

        $this->subject->addRule($ruleToAdd, $sibling);

        self::assertIsInt($ruleToAdd->getColumnNumber(), 'column number not set');
        self::assertGreaterThanOrEqual(0, $ruleToAdd->getColumnNumber(), 'column number not valid');
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     */
    public function addRuleWithSiblingNotInSetAddsRuleAfterInitialRules(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        // `display` is sometimes in `$initialPropertyNames` and sometimes the `$propertyNameToAdd`.
        // Choosing this for the bogus sibling allows testing all combinations of whether it is or isn't.
        $this->subject->addRule($ruleToAdd, new Rule('display'));

        $rules = $this->subject->getRules();
        self::assertSame($ruleToAdd, \end($rules));
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     */
    public function addRuleWithSiblingNotInSetSetsValidLineNumber(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        // `display` is sometimes in `$initialPropertyNames` and sometimes the `$propertyNameToAdd`.
        // Choosing this for the bogus sibling allows testing all combinations of whether it is or isn't.
        $this->subject->addRule($ruleToAdd, new Rule('display'));

        self::assertIsInt($ruleToAdd->getLineNumber(), 'line number not set');
        self::assertGreaterThanOrEqual(1, $ruleToAdd->getLineNumber(), 'line number not valid');
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     */
    public function addRuleWithSiblingNotInSetSetsValidColumnNumber(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $ruleToAdd = new Rule($propertyNameToAdd);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        // `display` is sometimes in `$initialPropertyNames` and sometimes the `$propertyNameToAdd`.
        // Choosing this for the bogus sibling allows testing all combinations of whether it is or isn't.
        $this->subject->addRule($ruleToAdd, new Rule('display'));

        self::assertIsInt($ruleToAdd->getColumnNumber(), 'column number not set');
        self::assertGreaterThanOrEqual(0, $ruleToAdd->getColumnNumber(), 'column number not valid');
    }

    /**
     * @test
     *
     * @param non-empty-list<string> $initialPropertyNames
     * @param int<0, max> $indexToRemove
     *
     * @dataProvider provideInitialPropertyNamesAndIndexOfOne
     */
    public function removeRuleRemovesRuleInSet(array $initialPropertyNames, int $indexToRemove): void
    {
        $this->setRulesFromPropertyNames($initialPropertyNames);
        $ruleToRemove = $this->subject->getRules()[$indexToRemove];

        $this->subject->removeRule($ruleToRemove);

        self::assertNotContains($ruleToRemove, $this->subject->getRules());
    }

    /**
     * @test
     *
     * @param non-empty-list<string> $initialPropertyNames
     * @param int<0, max> $indexToRemove
     *
     * @dataProvider provideInitialPropertyNamesAndIndexOfOne
     */
    public function removeRuleRemovesExactlyOneRule(array $initialPropertyNames, int $indexToRemove): void
    {
        $this->setRulesFromPropertyNames($initialPropertyNames);
        $ruleToRemove = $this->subject->getRules()[$indexToRemove];

        $this->subject->removeRule($ruleToRemove);

        self::assertCount(\count($initialPropertyNames) - 1, $this->subject->getRules());
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     */
    public function removeRuleWithRuleNotInSetKeepsSetUnchanged(
        array $initialPropertyNames,
        string $propertyNameToRemove
    ): void {
        $this->setRulesFromPropertyNames($initialPropertyNames);
        $initialRules = $this->subject->getRules();
        $ruleToRemove = new Rule($propertyNameToRemove);

        $this->subject->removeRule($ruleToRemove);

        self::assertSame($initialRules, $this->subject->getRules());
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
     *
     * @dataProvider providePropertyNamesAndPropertyNameToRemoveAndExpectedRemainingPropertyNames
     */
    public function removeMatchingRulesRemovesRulesWithPropertyName(
        array $initialPropertyNames,
        string $propertyNameToRemove
    ): void {
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->removeMatchingRules($propertyNameToRemove);

        self::assertArrayNotHasKey($propertyNameToRemove, $this->subject->getRulesAssoc());
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     * @param list<string> $expectedRemainingPropertyNames
     *
     * @dataProvider providePropertyNamesAndPropertyNameToRemoveAndExpectedRemainingPropertyNames
     */
    public function removeMatchingRulesWithPropertyNameKeepsOtherRules(
        array $initialPropertyNames,
        string $propertyNameToRemove,
        array $expectedRemainingPropertyNames
    ): void {
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->removeMatchingRules($propertyNameToRemove);

        $remainingRules = $this->subject->getRulesAssoc();
        if ($expectedRemainingPropertyNames === []) {
            self::assertSame([], $remainingRules);
        }
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
     *
     * @dataProvider providePropertyNamesAndPropertyNamePrefixToRemoveAndExpectedRemainingPropertyNames
     */
    public function removeMatchingRulesRemovesRulesWithPropertyNamePrefix(
        array $initialPropertyNames,
        string $propertyNamePrefix
    ): void {
        $propertyNamePrefixWithHyphen = $propertyNamePrefix . '-';
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->removeMatchingRules($propertyNamePrefixWithHyphen);

        $remainingRules = $this->subject->getRulesAssoc();
        self::assertArrayNotHasKey($propertyNamePrefix, $remainingRules);
        foreach (\array_keys($remainingRules) as $remainingPropertyName) {
            self::assertStringStartsNotWith($propertyNamePrefixWithHyphen, $remainingPropertyName);
        }
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     * @param list<string> $expectedRemainingPropertyNames
     *
     * @dataProvider providePropertyNamesAndPropertyNamePrefixToRemoveAndExpectedRemainingPropertyNames
     */
    public function removeMatchingRulesWithPropertyNamePrefixKeepsOtherRules(
        array $initialPropertyNames,
        string $propertyNamePrefix,
        array $expectedRemainingPropertyNames
    ): void {
        $propertyNamePrefixWithHyphen = $propertyNamePrefix . '-';
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->removeMatchingRules($propertyNamePrefixWithHyphen);

        $remainingRules = $this->subject->getRulesAssoc();
        if ($expectedRemainingPropertyNames === []) {
            self::assertSame([], $remainingRules);
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
     * @dataProvider providePropertyNames
     */
    public function removeAllRulesRemovesAllRules(array $propertyNamesToRemove): void
    {
        $this->setRulesFromPropertyNames($propertyNamesToRemove);

        $this->subject->removeAllRules();

        self::assertSame([], $this->subject->getRules());
    }

    /**
     * @test
     *
     * @param list<string> $propertyNamesToSet
     *
     * @dataProvider providePropertyNames
     */
    public function setRulesOnVirginSetsRulesWithoutPositionInOrder(array $propertyNamesToSet): void
    {
        $rulesToSet = self::createRulesFromPropertyNames($propertyNamesToSet);

        $this->subject->setRules($rulesToSet);

        self::assertSame($rulesToSet, $this->subject->getRules());
    }

    /**
     * @return DataProvider<string, array{0: list<string>, 1: list<string>}>
     */
    public static function provideInitialPropertyNamesAndPropertyNamesToSet(): DataProvider
    {
        return DataProvider::cross(self::providePropertyNames(), self::providePropertyNames());
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     * @param list<string> $propertyNamesToSet
     *
     * @dataProvider provideInitialPropertyNamesAndPropertyNamesToSet
     */
    public function setRulesReplacesRules(array $initialPropertyNames, array $propertyNamesToSet): void
    {
        $rulesToSet = self::createRulesFromPropertyNames($propertyNamesToSet);
        $this->setRulesFromPropertyNames($initialPropertyNames);

        $this->subject->setRules($rulesToSet);

        self::assertSame($rulesToSet, $this->subject->getRules());
    }

    /**
     * @test
     */
    public function setRulesWithRuleWithoutPositionSetsValidLineNumber(): void
    {
        $ruleToSet = new Rule('color');

        $this->subject->setRules([$ruleToSet]);

        self::assertIsInt($ruleToSet->getLineNumber(), 'line number not set');
        self::assertGreaterThanOrEqual(1, $ruleToSet->getLineNumber(), 'line number not valid');
    }

    /**
     * @test
     */
    public function setRulesWithRuleWithoutPositionSetsValidColumnNumber(): void
    {
        $ruleToSet = new Rule('color');

        $this->subject->setRules([$ruleToSet]);

        self::assertIsInt($ruleToSet->getColumnNumber(), 'column number not set');
        self::assertGreaterThanOrEqual(0, $ruleToSet->getColumnNumber(), 'column number not valid');
    }

    /**
     * @test
     */
    public function setRulesWithRuleWithOnlyLineNumberSetsColumnNumber(): void
    {
        $ruleToSet = new Rule('color');
        $ruleToSet->setPosition(42);

        $this->subject->setRules([$ruleToSet]);

        self::assertIsInt($ruleToSet->getColumnNumber(), 'column number not set');
        self::assertGreaterThanOrEqual(0, $ruleToSet->getColumnNumber(), 'column number not valid');
    }

    /**
     * @test
     */
    public function setRulesWithRuleWithOnlyLineNumberPreservesLineNumber(): void
    {
        $ruleToSet = new Rule('color');
        $ruleToSet->setPosition(42);

        $this->subject->setRules([$ruleToSet]);

        self::assertSame(42, $ruleToSet->getLineNumber(), 'line number not preserved');
    }

    /**
     * @test
     */
    public function setRulesWithRuleWithOnlyColumnNumberSetsLineNumber(): void
    {
        $ruleToSet = new Rule('color');
        $ruleToSet->setPosition(null, 42);

        $this->subject->setRules([$ruleToSet]);

        self::assertIsInt($ruleToSet->getLineNumber(), 'line number not set');
        self::assertGreaterThanOrEqual(1, $ruleToSet->getLineNumber(), 'line number not valid');
    }

    /**
     * @test
     */
    public function setRulesWithRuleWithOnlyColumnNumberPreservesColumnNumber(): void
    {
        $ruleToSet = new Rule('color');
        $ruleToSet->setPosition(null, 42);

        $this->subject->setRules([$ruleToSet]);

        self::assertSame(42, $ruleToSet->getColumnNumber(), 'column number not preserved');
    }

    /**
     * @test
     */
    public function setRulesWithRuleWithCompletePositionPreservesPosition(): void
    {
        $ruleToSet = new Rule('color');
        $ruleToSet->setPosition(42, 64);

        $this->subject->setRules([$ruleToSet]);

        self::assertSame(42, $ruleToSet->getLineNumber(), 'line number not preserved');
        self::assertSame(64, $ruleToSet->getColumnNumber(), 'column number not preserved');
    }

    /**
     * @test
     *
     * @param list<string> $propertyNamesToSet
     *
     * @dataProvider providePropertyNames
     */
    public function getRulesReturnsRulesSet(array $propertyNamesToSet): void
    {
        $rulesToSet = self::createRulesFromPropertyNames($propertyNamesToSet);
        $this->subject->setRules($rulesToSet);

        $result = $this->subject->getRules();

        self::assertSame($rulesToSet, $result);
    }

    /**
     * @test
     */
    public function getRulesOrdersByLineNumber(): void
    {
        $first = (new Rule('color'))->setPosition(1, 64);
        $second = (new Rule('display'))->setPosition(19, 42);
        $third = (new Rule('color'))->setPosition(55, 11);
        $this->subject->setRules([$third, $second, $first]);

        $result = $this->subject->getRules();

        self::assertSame([$first, $second, $third], $result);
    }

    /**
     * @test
     */
    public function getRulesOrdersRulesWithSameLineNumberByColumnNumber(): void
    {
        $first = (new Rule('color'))->setPosition(1, 11);
        $second = (new Rule('display'))->setPosition(1, 42);
        $third = (new Rule('color'))->setPosition(1, 64);
        $this->subject->setRules([$third, $second, $first]);

        $result = $this->subject->getRules();

        self::assertSame([$first, $second, $third], $result);
    }

    /**
     * @return array<string, array{0: list<string>, 1: string, 2: list<string>}>
     */
    public static function providePropertyNamesAndSearchPatternAndMatchingPropertyNames(): array
    {
        return [
            'single rule matched' => [
                ['color'],
                'color',
                ['color'],
            ],
            'first rule matched' => [
                ['color', 'display'],
                'color',
                ['color'],
            ],
            'last rule matched' => [
                ['color', 'display'],
                'display',
                ['display'],
            ],
            'middle rule matched' => [
                ['color', 'display', 'width'],
                'display',
                ['display'],
            ],
            'multiple rules for the same property matched' => [
                ['color', 'color'],
                'color',
                ['color'],
            ],
            'multiple rules for the same property matched in haystack' => [
                ['color', 'display', 'color', 'width'],
                'color',
                ['color'],
            ],
            'shorthand rule matched' => [
                ['font'],
                'font-',
                ['font'],
            ],
            'longhand rule matched' => [
                ['font-size'],
                'font-',
                ['font-size'],
            ],
            'shorthand and longhand rule matched' => [
                ['font', 'font-size'],
                'font-',
                ['font', 'font-size'],
            ],
            'shorthand rule matched in haystack' => [
                ['font', 'color'],
                'font-',
                ['font'],
            ],
            'longhand rule matched in haystack' => [
                ['font-size', 'color'],
                'font-',
                ['font-size'],
            ],
            'rules whose property names begin with the same characters not matched with pattern match' => [
                ['contain', 'container', 'container-type'],
                'contain-',
                ['contain'],
            ],
            'rules whose property names begin with the same characters not matched with exact match' => [
                ['contain', 'container', 'container-type'],
                'contain',
                ['contain'],
            ],
        ];
    }

    /**
     * @test
     *
     * @param list<string> $propertyNamesToSet
     * @param list<string> $matchingPropertyNames
     *
     * @dataProvider providePropertyNamesAndSearchPatternAndMatchingPropertyNames
     */
    public function getRulesWithPatternReturnsAllMatchingRules(
        array $propertyNamesToSet,
        string $searchPattern,
        array $matchingPropertyNames
    ): void {
        $rulesToSet = self::createRulesFromPropertyNames($propertyNamesToSet);
        // Use `array_values` to ensure canonical numeric array, since `array_filter` does not reindex.
        $matchingRules = \array_values(
            \array_filter(
                $rulesToSet,
                static function (Rule $rule) use ($matchingPropertyNames): bool {
                    return \in_array($rule->getRule(), $matchingPropertyNames, true);
                }
            )
        );
        $this->subject->setRules($rulesToSet);

        $result = $this->subject->getRules($searchPattern);

        // `Rule`s without pre-set positions are returned in the order set.  This is tested separately.
        self::assertSame($matchingRules, $result);
    }

    /**
     * @return array<string, array{0: list<string>, 1: string}>
     */
    public static function providePropertyNamesAndNonMatchingSearchPattern(): array
    {
        return [
            'no match in empty list' => [
                [],
                'color',
            ],
            'no match for different property' => [
                ['color'],
                'display',
            ],
            'no match for property not in list' => [
                ['color', 'display'],
                'width',
            ],
        ];
    }

    /**
     * @test
     *
     * @param list<string> $propertyNamesToSet
     *
     * @dataProvider providePropertyNamesAndNonMatchingSearchPattern
     */
    public function getRulesWithNonMatchingPatternReturnsEmptyArray(
        array $propertyNamesToSet,
        string $searchPattern
    ): void {
        $this->setRulesFromPropertyNames($propertyNamesToSet);

        $result = $this->subject->getRules($searchPattern);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getRulesWithPatternOrdersRulesByPosition(): void
    {
        $first = (new Rule('color'))->setPosition(1, 42);
        $second = (new Rule('color'))->setPosition(1, 64);
        $third = (new Rule('color'))->setPosition(55, 7);
        $this->subject->setRules([$third, $second, $first]);

        $result = $this->subject->getRules('color');

        self::assertSame([$first, $second, $third], $result);
    }

    /**
     * @return array<string, array{0: list<non-empty-string>}>
     */
    public static function provideDistinctPropertyNames(): array
    {
        return [
            'no properties' => [[]],
            'one property' => [['color']],
            'two properties' => [['color', 'display']],
        ];
    }

    /**
     * @test
     *
     * @param list<string> $propertyNamesToSet
     *
     * @dataProvider provideDistinctPropertyNames
     */
    public function getRulesAssocReturnsAllRulesWithDistinctPropertyNames(array $propertyNamesToSet): void
    {
        $rulesToSet = self::createRulesFromPropertyNames($propertyNamesToSet);
        $this->subject->setRules($rulesToSet);

        $result = $this->subject->getRulesAssoc();

        self::assertSame($rulesToSet, \array_values($result));
    }

    /**
     * @test
     */
    public function getRulesAssocReturnsLastRuleWithSamePropertyName(): void
    {
        $firstRule = new Rule('color');
        $lastRule = new Rule('color');
        $this->subject->setRules([$firstRule, $lastRule]);

        $result = $this->subject->getRulesAssoc();

        self::assertSame([$lastRule], \array_values($result));
    }

    /**
     * @test
     */
    public function getRulesAssocOrdersRulesByPosition(): void
    {
        $first = (new Rule('color'))->setPosition(1, 42);
        $second = (new Rule('display'))->setPosition(1, 64);
        $third = (new Rule('width'))->setPosition(55, 7);
        $this->subject->setRules([$third, $second, $first]);

        $result = $this->subject->getRulesAssoc();

        self::assertSame([$first, $second, $third], \array_values($result));
    }

    /**
     * @test
     */
    public function getRulesAssocKeysRulesByPropertyName(): void
    {
        $this->subject->setRules([new Rule('color'), new Rule('display')]);

        $result = $this->subject->getRulesAssoc();

        foreach ($result as $key => $rule) {
            self::assertSame($rule->getRule(), $key);
        }
    }

    /**
     * @test
     *
     * @param list<string> $propertyNamesToSet
     * @param list<string> $matchingPropertyNames
     *
     * @dataProvider providePropertyNamesAndSearchPatternAndMatchingPropertyNames
     */
    public function getRulesAssocWithPatternReturnsAllMatchingPropertyNames(
        array $propertyNamesToSet,
        string $searchPattern,
        array $matchingPropertyNames
    ): void {
        $this->setRulesFromPropertyNames($propertyNamesToSet);

        $result = $this->subject->getRulesAssoc($searchPattern);

        $resultPropertyNames = \array_keys($result);
        \sort($matchingPropertyNames);
        \sort($resultPropertyNames);
        self::assertSame($matchingPropertyNames, $resultPropertyNames);
    }

    /**
     * @test
     *
     * @param list<string> $propertyNamesToSet
     *
     * @dataProvider providePropertyNamesAndNonMatchingSearchPattern
     */
    public function getRulesAssocWithNonMatchingPatternReturnsEmptyArray(
        array $propertyNamesToSet,
        string $searchPattern
    ): void {
        $this->setRulesFromPropertyNames($propertyNamesToSet);

        $result = $this->subject->getRulesAssoc($searchPattern);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getRulesAssocWithPatternOrdersRulesByPosition(): void
    {
        $first = (new Rule('font'))->setPosition(1, 42);
        $second = (new Rule('font-family'))->setPosition(1, 64);
        $third = (new Rule('font-weight'))->setPosition(55, 7);
        $this->subject->setRules([$third, $second, $first]);

        $result = $this->subject->getRules('font-');

        self::assertSame([$first, $second, $third], \array_values($result));
    }

    /**
     * @param list<string> $propertyNames
     */
    private function setRulesFromPropertyNames(array $propertyNames): void
    {
        $this->subject->setRules(self::createRulesFromPropertyNames($propertyNames));
    }

    /**
     * @param list<string> $propertyNames
     *
     * @return list<Rule>
     */
    private static function createRulesFromPropertyNames(array $propertyNames): array
    {
        return \array_map(
            function (string $propertyName): Rule {
                return new Rule($propertyName);
            },
            $propertyNames
        );
    }
}
