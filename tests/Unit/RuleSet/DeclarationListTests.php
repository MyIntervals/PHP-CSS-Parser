<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Property\Declaration;
use Sabberworm\CSS\RuleSet\DeclarationList;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * This trait provides test methods for unit-testing classes that implement `DeclarationList`.
 * It can be `use`d in a `TestCase` which has a `$subject` property that is an instance of the implementing class
 * (the class under test), `setUp()` with default values.
 *
 * @phpstan-require-extends TestCase
 */
trait DeclarationListTests
{
    /**
     * @test
     */
    public function implementsDeclarationList(): void
    {
        self::assertInstanceOf(DeclarationList::class, $this->subject);
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
    public function addDeclarationWithoutPositionWithoutSiblingAddsDeclarationAfterInitialDeclarations(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $declarationToAdd = new Declaration($propertyNameToAdd);
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        $this->subject->addDeclaration($declarationToAdd);

        $declarations = $this->subject->getDeclarations();
        self::assertSame($declarationToAdd, \end($declarations));
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     */
    public function addDeclarationWithoutPositionWithoutSiblingSetsValidLineNumber(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $declarationToAdd = new Declaration($propertyNameToAdd);
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        $this->subject->addDeclaration($declarationToAdd);

        self::assertIsInt($declarationToAdd->getLineNumber(), 'line number not set');
        self::assertGreaterThanOrEqual(1, $declarationToAdd->getLineNumber(), 'line number not valid');
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     */
    public function addDeclarationWithoutPositionWithoutSiblingSetsValidColumnNumber(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $declarationToAdd = new Declaration($propertyNameToAdd);
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        $this->subject->addDeclaration($declarationToAdd);

        self::assertIsInt($declarationToAdd->getColumnNumber(), 'column number not set');
        self::assertGreaterThanOrEqual(0, $declarationToAdd->getColumnNumber(), 'column number not valid');
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     *
     * @param list<string> $initialPropertyNames
     */
    public function addDeclarationWithOnlyLineNumberWithoutSiblingAddsDeclaration(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $declarationToAdd = new Declaration($propertyNameToAdd);
        $declarationToAdd->setPosition(42);
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        $this->subject->addDeclaration($declarationToAdd);

        self::assertContains($declarationToAdd, $this->subject->getDeclarations());
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     *
     * @param list<string> $initialPropertyNames
     */
    public function addDeclarationWithOnlyLineNumberWithoutSiblingSetsColumnNumber(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $declarationToAdd = new Declaration($propertyNameToAdd);
        $declarationToAdd->setPosition(42);
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        $this->subject->addDeclaration($declarationToAdd);

        self::assertIsInt($declarationToAdd->getColumnNumber(), 'column number not set');
        self::assertGreaterThanOrEqual(0, $declarationToAdd->getColumnNumber(), 'column number not valid');
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     *
     * @param list<string> $initialPropertyNames
     */
    public function addDeclarationWithOnlyLineNumberWithoutSiblingPreservesLineNumber(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $declarationToAdd = new Declaration($propertyNameToAdd);
        $declarationToAdd->setPosition(42);
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        $this->subject->addDeclaration($declarationToAdd);

        self::assertSame(42, $declarationToAdd->getLineNumber(), 'line number not preserved');
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     *
     * @param list<string> $initialPropertyNames
     */
    public function addDeclarationWithOnlyColumnNumberWithoutSiblingAddsDeclarationAfterInitialDeclarations(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $declarationToAdd = new Declaration($propertyNameToAdd);
        $declarationToAdd->setPosition(null, 42);
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        $this->subject->addDeclaration($declarationToAdd);

        $declarations = $this->subject->getDeclarations();
        self::assertSame($declarationToAdd, \end($declarations));
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     *
     * @param list<string> $initialPropertyNames
     */
    public function addDeclarationWithOnlyColumnNumberWithoutSiblingSetsLineNumber(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $declarationToAdd = new Declaration($propertyNameToAdd);
        $declarationToAdd->setPosition(null, 42);
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        $this->subject->addDeclaration($declarationToAdd);

        self::assertIsInt($declarationToAdd->getLineNumber(), 'line number not set');
        self::assertGreaterThanOrEqual(1, $declarationToAdd->getLineNumber(), 'line number not valid');
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     *
     * @param list<string> $initialPropertyNames
     */
    public function addDeclarationWithOnlyColumnNumberWithoutSiblingPreservesColumnNumber(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $declarationToAdd = new Declaration($propertyNameToAdd);
        $declarationToAdd->setPosition(null, 42);
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        $this->subject->addDeclaration($declarationToAdd);

        self::assertSame(42, $declarationToAdd->getColumnNumber(), 'column number not preserved');
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     *
     * @param list<string> $initialPropertyNames
     */
    public function addDeclarationWithCompletePositionWithoutSiblingAddsDeclaration(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $declarationToAdd = new Declaration($propertyNameToAdd);
        $declarationToAdd->setPosition(42, 64);
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        $this->subject->addDeclaration($declarationToAdd);

        self::assertContains($declarationToAdd, $this->subject->getDeclarations());
    }

    /**
     * @test
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     *
     * @param list<string> $initialPropertyNames
     */
    public function addDeclarationWithCompletePositionWithoutSiblingPreservesPosition(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $declarationToAdd = new Declaration($propertyNameToAdd);
        $declarationToAdd->setPosition(42, 64);
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        $this->subject->addDeclaration($declarationToAdd);

        self::assertSame(42, $declarationToAdd->getLineNumber(), 'line number not preserved');
        self::assertSame(64, $declarationToAdd->getColumnNumber(), 'column number not preserved');
    }

    /**
     * @return array<string, array{0: non-empty-list<non-empty-string>, 1: int<0, max>}>
     */
    public static function provideInitialPropertyNamesAndIndexOfOne(): array
    {
        $initialPropertyNamesSets = self::providePropertyNames();

        // Provide sets with each possible index for the initially set `Declaration`s.
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
    public function addDeclarationWithSiblingInsertsDeclarationBeforeSibling(
        array $initialPropertyNames,
        int $siblingIndex,
        string $propertyNameToAdd
    ): void {
        $declarationToAdd = new Declaration($propertyNameToAdd);
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);
        $sibling = $this->subject->getDeclarations()[$siblingIndex];

        $this->subject->addDeclaration($declarationToAdd, $sibling);

        $declarations = $this->subject->getDeclarations();
        $siblingPosition = \array_search($sibling, $declarations, true);
        self::assertIsInt($siblingPosition);
        self::assertSame($siblingPosition - 1, \array_search($declarationToAdd, $declarations, true));
    }

    /**
     * @test
     *
     * @param non-empty-list<string> $initialPropertyNames
     * @param int<0, max> $siblingIndex
     *
     * @dataProvider provideInitialPropertyNamesAndSiblingIndexAndPropertyNameToAdd
     */
    public function addDeclarationWithSiblingSetsValidLineNumber(
        array $initialPropertyNames,
        int $siblingIndex,
        string $propertyNameToAdd
    ): void {
        $declarationToAdd = new Declaration($propertyNameToAdd);
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);
        $sibling = $this->subject->getDeclarations()[$siblingIndex];

        $this->subject->addDeclaration($declarationToAdd, $sibling);

        self::assertIsInt($declarationToAdd->getLineNumber(), 'line number not set');
        self::assertGreaterThanOrEqual(1, $declarationToAdd->getLineNumber(), 'line number not valid');
    }

    /**
     * @test
     *
     * @param non-empty-list<string> $initialPropertyNames
     * @param int<0, max> $siblingIndex
     *
     * @dataProvider provideInitialPropertyNamesAndSiblingIndexAndPropertyNameToAdd
     */
    public function addDeclarationWithSiblingSetsValidColumnNumber(
        array $initialPropertyNames,
        int $siblingIndex,
        string $propertyNameToAdd
    ): void {
        $declarationToAdd = new Declaration($propertyNameToAdd);
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);
        $sibling = $this->subject->getDeclarations()[$siblingIndex];

        $this->subject->addDeclaration($declarationToAdd, $sibling);

        self::assertIsInt($declarationToAdd->getColumnNumber(), 'column number not set');
        self::assertGreaterThanOrEqual(0, $declarationToAdd->getColumnNumber(), 'column number not valid');
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     */
    public function addDeclarationWithSiblingNotInSetAddsDeclarationAfterInitialDeclarations(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $declarationToAdd = new Declaration($propertyNameToAdd);
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        // `display` is sometimes in `$initialPropertyNames` and sometimes the `$propertyNameToAdd`.
        // Choosing this for the bogus sibling allows testing all combinations of whether it is or isn't.
        $this->subject->addDeclaration($declarationToAdd, new Declaration('display'));

        $declarations = $this->subject->getDeclarations();
        self::assertSame($declarationToAdd, \end($declarations));
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     */
    public function addDeclarationWithSiblingNotInSetSetsValidLineNumber(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $declarationToAdd = new Declaration($propertyNameToAdd);
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        // `display` is sometimes in `$initialPropertyNames` and sometimes the `$propertyNameToAdd`.
        // Choosing this for the bogus sibling allows testing all combinations of whether it is or isn't.
        $this->subject->addDeclaration($declarationToAdd, new Declaration('display'));

        self::assertIsInt($declarationToAdd->getLineNumber(), 'line number not set');
        self::assertGreaterThanOrEqual(1, $declarationToAdd->getLineNumber(), 'line number not valid');
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     */
    public function addDeclarationWithSiblingNotInSetSetsValidColumnNumber(
        array $initialPropertyNames,
        string $propertyNameToAdd
    ): void {
        $declarationToAdd = new Declaration($propertyNameToAdd);
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        // `display` is sometimes in `$initialPropertyNames` and sometimes the `$propertyNameToAdd`.
        // Choosing this for the bogus sibling allows testing all combinations of whether it is or isn't.
        $this->subject->addDeclaration($declarationToAdd, new Declaration('display'));

        self::assertIsInt($declarationToAdd->getColumnNumber(), 'column number not set');
        self::assertGreaterThanOrEqual(0, $declarationToAdd->getColumnNumber(), 'column number not valid');
    }

    /**
     * @test
     *
     * @param non-empty-list<string> $initialPropertyNames
     * @param int<0, max> $indexToRemove
     *
     * @dataProvider provideInitialPropertyNamesAndIndexOfOne
     */
    public function removeDeclarationRemovesDeclarationInSet(array $initialPropertyNames, int $indexToRemove): void
    {
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);
        $declarationToRemove = $this->subject->getDeclarations()[$indexToRemove];

        $this->subject->removeDeclaration($declarationToRemove);

        self::assertNotContains($declarationToRemove, $this->subject->getDeclarations());
    }

    /**
     * @test
     *
     * @param non-empty-list<string> $initialPropertyNames
     * @param int<0, max> $indexToRemove
     *
     * @dataProvider provideInitialPropertyNamesAndIndexOfOne
     */
    public function removeDeclarationRemovesExactlyOneDeclaration(array $initialPropertyNames, int $indexToRemove): void
    {
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);
        $declarationToRemove = $this->subject->getDeclarations()[$indexToRemove];

        $this->subject->removeDeclaration($declarationToRemove);

        self::assertCount(\count($initialPropertyNames) - 1, $this->subject->getDeclarations());
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     *
     * @dataProvider provideInitialPropertyNamesAndAnotherPropertyName
     */
    public function removeDeclarationWithDeclarationNotInSetKeepsSetUnchanged(
        array $initialPropertyNames,
        string $propertyNameToRemove
    ): void {
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);
        $initialDeclarations = $this->subject->getDeclarations();
        $declarationToRemove = new Declaration($propertyNameToRemove);

        $this->subject->removeDeclaration($declarationToRemove);

        self::assertSame($initialDeclarations, $this->subject->getDeclarations());
    }

    /**
     * @return array<string, array{0: list<string>, 1: string, 2: list<string>}>
     */
    public static function providePropertyNamesAndPropertyNameToRemoveAndExpectedRemainingPropertyNames(): array
    {
        return [
            'removing single declaration' => [
                ['color'],
                'color',
                [],
            ],
            'removing first declaration' => [
                ['color', 'display'],
                'color',
                ['display'],
            ],
            'removing last declaration' => [
                ['color', 'display'],
                'display',
                ['color'],
            ],
            'removing middle declaration' => [
                ['color', 'display', 'width'],
                'display',
                ['color', 'width'],
            ],
            'removing multiple declarations' => [
                ['color', 'color'],
                'color',
                [],
            ],
            'removing multiple declarations with another kept' => [
                ['color', 'color', 'display'],
                'color',
                ['display'],
            ],
            'removing nonexistent declaration from empty list' => [
                [],
                'color',
                [],
            ],
            'removing nonexistent declaration from nonempty list' => [
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
    public function removeMatchingDeclarationsRemovesDeclarationsWithPropertyName(
        array $initialPropertyNames,
        string $propertyNameToRemove
    ): void {
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        $this->subject->removeMatchingDeclarations($propertyNameToRemove);

        self::assertArrayNotHasKey($propertyNameToRemove, $this->subject->getDeclarationsAssociative());
    }

    /**
     * @test
     *
     * @param list<string> $initialPropertyNames
     * @param list<string> $expectedRemainingPropertyNames
     *
     * @dataProvider providePropertyNamesAndPropertyNameToRemoveAndExpectedRemainingPropertyNames
     */
    public function removeMatchingDeclarationsWithPropertyNameKeepsOtherDeclarations(
        array $initialPropertyNames,
        string $propertyNameToRemove,
        array $expectedRemainingPropertyNames
    ): void {
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        $this->subject->removeMatchingDeclarations($propertyNameToRemove);

        $remainingDeclarations = $this->subject->getDeclarationsAssociative();
        if ($expectedRemainingPropertyNames === []) {
            self::assertSame([], $remainingDeclarations);
        }
        foreach ($expectedRemainingPropertyNames as $expectedPropertyName) {
            self::assertArrayHasKey($expectedPropertyName, $remainingDeclarations);
        }
    }

    /**
     * @return array<string, array{0: list<string>, 1: string, 2: list<string>}>
     */
    public static function providePropertyNamesAndPropertyNamePrefixToRemoveAndExpectedRemainingPropertyNames(): array
    {
        return [
            'removing shorthand declaration' => [
                ['font'],
                'font',
                [],
            ],
            'removing longhand declaration' => [
                ['font-size'],
                'font',
                [],
            ],
            'removing shorthand and longhand declaration' => [
                ['font', 'font-size'],
                'font',
                [],
            ],
            'removing shorthand declaration with another kept' => [
                ['font', 'color'],
                'font',
                ['color'],
            ],
            'removing longhand declaration with another kept' => [
                ['font-size', 'color'],
                'font',
                ['color'],
            ],
            'keeping other declarations whose property names begin with the same characters' => [
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
    public function removeMatchingDeclarationsRemovesDeclarationsWithPropertyNamePrefix(
        array $initialPropertyNames,
        string $propertyNamePrefix
    ): void {
        $propertyNamePrefixWithHyphen = $propertyNamePrefix . '-';
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        $this->subject->removeMatchingDeclarations($propertyNamePrefixWithHyphen);

        $remainingDeclarations = $this->subject->getDeclarationsAssociative();
        self::assertArrayNotHasKey($propertyNamePrefix, $remainingDeclarations);
        foreach (\array_keys($remainingDeclarations) as $remainingPropertyName) {
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
    public function removeMatchingDeclarationsWithPropertyNamePrefixKeepsOtherDeclarations(
        array $initialPropertyNames,
        string $propertyNamePrefix,
        array $expectedRemainingPropertyNames
    ): void {
        $propertyNamePrefixWithHyphen = $propertyNamePrefix . '-';
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        $this->subject->removeMatchingDeclarations($propertyNamePrefixWithHyphen);

        $remainingDeclarations = $this->subject->getDeclarationsAssociative();
        if ($expectedRemainingPropertyNames === []) {
            self::assertSame([], $remainingDeclarations);
        }
        foreach ($expectedRemainingPropertyNames as $expectedPropertyName) {
            self::assertArrayHasKey($expectedPropertyName, $remainingDeclarations);
        }
    }

    /**
     * @test
     *
     * @param list<string> $propertyNamesToRemove
     *
     * @dataProvider providePropertyNames
     */
    public function removeAllDeclarationsRemovesAllDeclarations(array $propertyNamesToRemove): void
    {
        $this->setDeclarationsFromPropertyNames($propertyNamesToRemove);

        $this->subject->removeAllDeclarations();

        self::assertSame([], $this->subject->getDeclarations());
    }

    /**
     * @test
     *
     * @param list<string> $propertyNamesToSet
     *
     * @dataProvider providePropertyNames
     */
    public function setDeclarationsOnVirginSetsDeclarationsWithoutPositionInOrder(array $propertyNamesToSet): void
    {
        $declarationsToSet = self::createDeclarationsFromPropertyNames($propertyNamesToSet);

        $this->subject->setDeclarations($declarationsToSet);

        self::assertSame($declarationsToSet, $this->subject->getDeclarations());
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
    public function setDeclarationsReplacesDeclarations(array $initialPropertyNames, array $propertyNamesToSet): void
    {
        $declarationsToSet = self::createDeclarationsFromPropertyNames($propertyNamesToSet);
        $this->setDeclarationsFromPropertyNames($initialPropertyNames);

        $this->subject->setDeclarations($declarationsToSet);

        self::assertSame($declarationsToSet, $this->subject->getDeclarations());
    }

    /**
     * @test
     */
    public function setDeclarationsWithDeclarationWithoutPositionSetsValidLineNumber(): void
    {
        $declarationToSet = new Declaration('color');

        $this->subject->setDeclarations([$declarationToSet]);

        self::assertIsInt($declarationToSet->getLineNumber(), 'line number not set');
        self::assertGreaterThanOrEqual(1, $declarationToSet->getLineNumber(), 'line number not valid');
    }

    /**
     * @test
     */
    public function setDeclarationsWithDeclarationWithoutPositionSetsValidColumnNumber(): void
    {
        $declarationToSet = new Declaration('color');

        $this->subject->setDeclarations([$declarationToSet]);

        self::assertIsInt($declarationToSet->getColumnNumber(), 'column number not set');
        self::assertGreaterThanOrEqual(0, $declarationToSet->getColumnNumber(), 'column number not valid');
    }

    /**
     * @test
     */
    public function setDeclarationsWithDeclarationWithOnlyLineNumberSetsColumnNumber(): void
    {
        $declarationToSet = new Declaration('color');
        $declarationToSet->setPosition(42);

        $this->subject->setDeclarations([$declarationToSet]);

        self::assertIsInt($declarationToSet->getColumnNumber(), 'column number not set');
        self::assertGreaterThanOrEqual(0, $declarationToSet->getColumnNumber(), 'column number not valid');
    }

    /**
     * @test
     */
    public function setDeclarationsWithDeclarationWithOnlyLineNumberPreservesLineNumber(): void
    {
        $declarationToSet = new Declaration('color');
        $declarationToSet->setPosition(42);

        $this->subject->setDeclarations([$declarationToSet]);

        self::assertSame(42, $declarationToSet->getLineNumber(), 'line number not preserved');
    }

    /**
     * @test
     */
    public function setDeclarationsWithDeclarationWithOnlyColumnNumberSetsLineNumber(): void
    {
        $declarationToSet = new Declaration('color');
        $declarationToSet->setPosition(null, 42);

        $this->subject->setDeclarations([$declarationToSet]);

        self::assertIsInt($declarationToSet->getLineNumber(), 'line number not set');
        self::assertGreaterThanOrEqual(1, $declarationToSet->getLineNumber(), 'line number not valid');
    }

    /**
     * @test
     */
    public function setDeclarationsWithDeclarationWithOnlyColumnNumberPreservesColumnNumber(): void
    {
        $declarationToSet = new Declaration('color');
        $declarationToSet->setPosition(null, 42);

        $this->subject->setDeclarations([$declarationToSet]);

        self::assertSame(42, $declarationToSet->getColumnNumber(), 'column number not preserved');
    }

    /**
     * @test
     */
    public function setDeclarationsWithDeclarationWithCompletePositionPreservesPosition(): void
    {
        $declarationToSet = new Declaration('color');
        $declarationToSet->setPosition(42, 64);

        $this->subject->setDeclarations([$declarationToSet]);

        self::assertSame(42, $declarationToSet->getLineNumber(), 'line number not preserved');
        self::assertSame(64, $declarationToSet->getColumnNumber(), 'column number not preserved');
    }

    /**
     * @test
     *
     * @param list<string> $propertyNamesToSet
     *
     * @dataProvider providePropertyNames
     */
    public function getDeclarationsReturnsDeclarationsSet(array $propertyNamesToSet): void
    {
        $declarationsToSet = self::createDeclarationsFromPropertyNames($propertyNamesToSet);
        $this->subject->setDeclarations($declarationsToSet);

        $result = $this->subject->getDeclarations();

        self::assertSame($declarationsToSet, $result);
    }

    /**
     * @test
     */
    public function getDeclarationsOrdersByLineNumber(): void
    {
        $first = (new Declaration('color'))->setPosition(1, 64);
        $second = (new Declaration('display'))->setPosition(19, 42);
        $third = (new Declaration('color'))->setPosition(55, 11);
        $this->subject->setDeclarations([$third, $second, $first]);

        $result = $this->subject->getDeclarations();

        self::assertSame([$first, $second, $third], $result);
    }

    /**
     * @test
     */
    public function getDeclarationsOrdersDeclarationsWithSameLineNumberByColumnNumber(): void
    {
        $first = (new Declaration('color'))->setPosition(1, 11);
        $second = (new Declaration('display'))->setPosition(1, 42);
        $third = (new Declaration('color'))->setPosition(1, 64);
        $this->subject->setDeclarations([$third, $second, $first]);

        $result = $this->subject->getDeclarations();

        self::assertSame([$first, $second, $third], $result);
    }

    /**
     * @return array<string, array{0: list<string>, 1: string, 2: list<string>}>
     */
    public static function providePropertyNamesAndSearchPatternAndMatchingPropertyNames(): array
    {
        return [
            'single declaration matched' => [
                ['color'],
                'color',
                ['color'],
            ],
            'first declaration matched' => [
                ['color', 'display'],
                'color',
                ['color'],
            ],
            'last declaration matched' => [
                ['color', 'display'],
                'display',
                ['display'],
            ],
            'middle declaration matched' => [
                ['color', 'display', 'width'],
                'display',
                ['display'],
            ],
            'multiple declarations for the same property matched' => [
                ['color', 'color'],
                'color',
                ['color'],
            ],
            'multiple declarations for the same property matched in haystack' => [
                ['color', 'display', 'color', 'width'],
                'color',
                ['color'],
            ],
            'shorthand declaration matched' => [
                ['font'],
                'font-',
                ['font'],
            ],
            'longhand declaration matched' => [
                ['font-size'],
                'font-',
                ['font-size'],
            ],
            'shorthand and longhand declaration matched' => [
                ['font', 'font-size'],
                'font-',
                ['font', 'font-size'],
            ],
            'shorthand declaration matched in haystack' => [
                ['font', 'color'],
                'font-',
                ['font'],
            ],
            'longhand declaration matched in haystack' => [
                ['font-size', 'color'],
                'font-',
                ['font-size'],
            ],
            'declarations whose property names begin with the same characters not matched with pattern match' => [
                ['contain', 'container', 'container-type'],
                'contain-',
                ['contain'],
            ],
            'declarations whose property names begin with the same characters not matched with exact match' => [
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
    public function getDeclarationsWithPatternReturnsAllMatchingDeclarations(
        array $propertyNamesToSet,
        string $searchPattern,
        array $matchingPropertyNames
    ): void {
        $declarationsToSet = self::createDeclarationsFromPropertyNames($propertyNamesToSet);
        // Use `array_values` to ensure canonical numeric array, since `array_filter` does not reindex.
        $matchingDeclarations = \array_values(
            \array_filter(
                $declarationsToSet,
                static function (Declaration $declaration) use ($matchingPropertyNames): bool {
                    return \in_array($declaration->getPropertyName(), $matchingPropertyNames, true);
                }
            )
        );
        $this->subject->setDeclarations($declarationsToSet);

        $result = $this->subject->getDeclarations($searchPattern);

        // `Declaration`s without pre-set positions are returned in the order set.  This is tested separately.
        self::assertSame($matchingDeclarations, $result);
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
    public function getDeclarationsWithNonMatchingPatternReturnsEmptyArray(
        array $propertyNamesToSet,
        string $searchPattern
    ): void {
        $this->setDeclarationsFromPropertyNames($propertyNamesToSet);

        $result = $this->subject->getDeclarations($searchPattern);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getDeclarationsWithPatternOrdersDeclarationsByPosition(): void
    {
        $first = (new Declaration('color'))->setPosition(1, 42);
        $second = (new Declaration('color'))->setPosition(1, 64);
        $third = (new Declaration('color'))->setPosition(55, 7);
        $this->subject->setDeclarations([$third, $second, $first]);

        $result = $this->subject->getDeclarations('color');

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
    public function getDeclarationsAssocReturnsAllDeclarationsWithDistinctPropertyNames(array $propertyNamesToSet): void
    {
        $declarationsToSet = self::createDeclarationsFromPropertyNames($propertyNamesToSet);
        $this->subject->setDeclarations($declarationsToSet);

        $result = $this->subject->getDeclarationsAssociative();

        self::assertSame($declarationsToSet, \array_values($result));
    }

    /**
     * @test
     */
    public function getDeclarationsAssocReturnsLastDeclarationWithSamePropertyName(): void
    {
        $firstDeclaration = new Declaration('color');
        $lastDeclaration = new Declaration('color');
        $this->subject->setDeclarations([$firstDeclaration, $lastDeclaration]);

        $result = $this->subject->getDeclarationsAssociative();

        self::assertSame([$lastDeclaration], \array_values($result));
    }

    /**
     * @test
     */
    public function getDeclarationsAssocOrdersDeclarationsByPosition(): void
    {
        $first = (new Declaration('color'))->setPosition(1, 42);
        $second = (new Declaration('display'))->setPosition(1, 64);
        $third = (new Declaration('width'))->setPosition(55, 7);
        $this->subject->setDeclarations([$third, $second, $first]);

        $result = $this->subject->getDeclarationsAssociative();

        self::assertSame([$first, $second, $third], \array_values($result));
    }

    /**
     * @test
     */
    public function getDeclarationsAssocKeysDeclarationsByPropertyName(): void
    {
        $this->subject->setDeclarations([new Declaration('color'), new Declaration('display')]);

        $result = $this->subject->getDeclarationsAssociative();

        foreach ($result as $key => $declaration) {
            self::assertSame($declaration->getPropertyName(), $key);
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
    public function getDeclarationsAssocWithPatternReturnsAllMatchingPropertyNames(
        array $propertyNamesToSet,
        string $searchPattern,
        array $matchingPropertyNames
    ): void {
        $this->setDeclarationsFromPropertyNames($propertyNamesToSet);

        $result = $this->subject->getDeclarationsAssociative($searchPattern);

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
    public function getDeclarationsAssocWithNonMatchingPatternReturnsEmptyArray(
        array $propertyNamesToSet,
        string $searchPattern
    ): void {
        $this->setDeclarationsFromPropertyNames($propertyNamesToSet);

        $result = $this->subject->getDeclarationsAssociative($searchPattern);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getDeclarationsAssocWithPatternOrdersDeclarationsByPosition(): void
    {
        $first = (new Declaration('font'))->setPosition(1, 42);
        $second = (new Declaration('font-family'))->setPosition(1, 64);
        $third = (new Declaration('font-weight'))->setPosition(55, 7);
        $this->subject->setDeclarations([$third, $second, $first]);

        $result = $this->subject->getDeclarations('font-');

        self::assertSame([$first, $second, $third], \array_values($result));
    }

    /**
     * @param list<string> $propertyNames
     */
    private function setDeclarationsFromPropertyNames(array $propertyNames): void
    {
        $this->subject->setDeclarations(self::createDeclarationsFromPropertyNames($propertyNames));
    }

    /**
     * @param list<string> $propertyNames
     *
     * @return list<Declaration>
     */
    private static function createDeclarationsFromPropertyNames(array $propertyNames): array
    {
        return \array_map(
            function (string $propertyName): Declaration {
                return new Declaration($propertyName);
            },
            $propertyNames
        );
    }
}
