<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\OutputFormatter;

/**
 * @covers \Sabberworm\CSS\OutputFormat
 */
final class OutputFormatTest extends TestCase
{
    /**
     * @var OutputFormat
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new OutputFormat();
    }

    /**
     * @test
     */
    public function getStringQuotingTypeInitiallyReturnsDoubleQuote(): void
    {
        self::assertSame('"', $this->subject->getStringQuotingType());
    }

    /**
     * @test
     */
    public function setStringQuotingTypeSetsStringQuotingType(): void
    {
        $value = "'";
        $this->subject->setStringQuotingType($value);

        self::assertSame($value, $this->subject->getStringQuotingType());
    }

    /**
     * @test
     */
    public function setStringQuotingTypeProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setStringQuotingType('"'));
    }

    /**
     * @test
     */
    public function usesRgbHashNotationInitiallyReturnsTrue(): void
    {
        self::assertTrue($this->subject->usesRgbHashNotation());
    }

    /**
     * @return array<string, array{0: bool}>
     */
    public static function provideBooleans(): array
    {
        return [
            'true' => [true],
            'false' => [false],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideBooleans
     */
    public function setRGBHashNotationSetsRGBHashNotation(bool $value): void
    {
        $this->subject->setRGBHashNotation($value);

        self::assertSame($value, $this->subject->usesRgbHashNotation());
    }

    /**
     * @test
     */
    public function setRGBHashNotationProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setRGBHashNotation(true));
    }

    /**
     * @test
     */
    public function shouldRenderSemicolonAfterLastRuleInitiallyReturnsTrue(): void
    {
        self::assertTrue($this->subject->shouldRenderSemicolonAfterLastRule());
    }

    /**
     * @test
     *
     * @dataProvider provideBooleans
     */
    public function setSemicolonAfterLastRuleSetsSemicolonAfterLastRule(bool $value): void
    {
        $this->subject->setSemicolonAfterLastRule($value);

        self::assertSame($value, $this->subject->shouldRenderSemicolonAfterLastRule());
    }

    /**
     * @test
     */
    public function setSemicolonAfterLastRuleProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setSemicolonAfterLastRule(true));
    }

    /**
     * @test
     */
    public function getSpaceAfterRuleNameInitiallyReturnsSingleSpace(): void
    {
        self::assertSame(' ', $this->subject->getSpaceAfterRuleName());
    }

    /**
     * @test
     */
    public function setSpaceAfterRuleNameSetsSpaceAfterRuleName(): void
    {
        $value = "\n";
        $this->subject->setSpaceAfterRuleName($value);

        self::assertSame($value, $this->subject->getSpaceAfterRuleName());
    }

    /**
     * @test
     */
    public function setSpaceAfterRuleNameProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setSpaceAfterRuleName("\n"));
    }

    /**
     * @test
     */
    public function getSpaceBeforeRulesInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getSpaceBeforeRules());
    }

    /**
     * @test
     */
    public function setSpaceBeforeRulesSetsSpaceBeforeRules(): void
    {
        $value = ' ';
        $this->subject->setSpaceBeforeRules($value);

        self::assertSame($value, $this->subject->getSpaceBeforeRules());
    }

    /**
     * @test
     */
    public function setSpaceBeforeRulesProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setSpaceBeforeRules(' '));
    }

    /**
     * @test
     */
    public function getSpaceAfterRulesInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getSpaceAfterRules());
    }

    /**
     * @test
     */
    public function setSpaceAfterRulesSetsSpaceAfterRules(): void
    {
        $value = ' ';
        $this->subject->setSpaceAfterRules($value);

        self::assertSame($value, $this->subject->getSpaceAfterRules());
    }

    /**
     * @test
     */
    public function setSpaceAfterRulesProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setSpaceAfterRules(' '));
    }

    /**
     * @test
     */
    public function getSpaceBetweenRulesInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getSpaceBetweenRules());
    }

    /**
     * @test
     */
    public function setSpaceBetweenRulesSetsSpaceBetweenRules(): void
    {
        $value = ' ';
        $this->subject->setSpaceBetweenRules($value);

        self::assertSame($value, $this->subject->getSpaceBetweenRules());
    }

    /**
     * @test
     */
    public function setSpaceBetweenRulesProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setSpaceBetweenRules(' '));
    }

    /**
     * @test
     */
    public function getSpaceBeforeBlocksInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getSpaceBeforeBlocks());
    }

    /**
     * @test
     */
    public function setSpaceBeforeBlocksSetsSpaceBeforeBlocks(): void
    {
        $value = ' ';
        $this->subject->setSpaceBeforeBlocks($value);

        self::assertSame($value, $this->subject->getSpaceBeforeBlocks());
    }

    /**
     * @test
     */
    public function setSpaceBeforeBlocksProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setSpaceBeforeBlocks(' '));
    }

    /**
     * @test
     */
    public function getSpaceAfterBlocksInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getSpaceAfterBlocks());
    }

    /**
     * @test
     */
    public function setSpaceAfterBlocksSetsSpaceAfterBlocks(): void
    {
        $value = ' ';
        $this->subject->setSpaceAfterBlocks($value);

        self::assertSame($value, $this->subject->getSpaceAfterBlocks());
    }

    /**
     * @test
     */
    public function setSpaceAfterBlocksProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setSpaceAfterBlocks(' '));
    }

    /**
     * @test
     */
    public function getSpaceBetweenBlocksInitiallyReturnsNewline(): void
    {
        self::assertSame("\n", $this->subject->getSpaceBetweenBlocks());
    }

    /**
     * @test
     */
    public function setSpaceBetweenBlocksSetsSpaceBetweenBlocks(): void
    {
        $value = ' ';
        $this->subject->setSpaceBetweenBlocks($value);

        self::assertSame($value, $this->subject->getSpaceBetweenBlocks());
    }

    /**
     * @test
     */
    public function setSpaceBetweenBlocksProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setSpaceBetweenBlocks(' '));
    }

    /**
     * @test
     */
    public function getContentBeforeAtRuleBlockInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getContentBeforeAtRuleBlock());
    }

    /**
     * @test
     */
    public function setBeforeAtRuleBlockSetsBeforeAtRuleBlock(): void
    {
        $value = ' ';
        $this->subject->setBeforeAtRuleBlock($value);

        self::assertSame($value, $this->subject->getContentBeforeAtRuleBlock());
    }

    /**
     * @test
     */
    public function setBeforeAtRuleBlockProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setBeforeAtRuleBlock(' '));
    }

    /**
     * @test
     */
    public function getContentAfterAtRuleBlockInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getContentAfterAtRuleBlock());
    }

    /**
     * @test
     */
    public function setAfterAtRuleBlockSetsAfterAtRuleBlock(): void
    {
        $value = ' ';
        $this->subject->setAfterAtRuleBlock($value);

        self::assertSame($value, $this->subject->getContentAfterAtRuleBlock());
    }

    /**
     * @test
     */
    public function setAfterAtRuleBlockProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setAfterAtRuleBlock(' '));
    }

    /**
     * @test
     */
    public function getSpaceBeforeSelectorSeparatorInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getSpaceBeforeSelectorSeparator());
    }

    /**
     * @test
     */
    public function setSpaceBeforeSelectorSeparatorSetsSpaceBeforeSelectorSeparator(): void
    {
        $value = ' ';
        $this->subject->setSpaceBeforeSelectorSeparator($value);

        self::assertSame($value, $this->subject->getSpaceBeforeSelectorSeparator());
    }

    /**
     * @test
     */
    public function setSpaceBeforeSelectorSeparatorProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setSpaceBeforeSelectorSeparator(' '));
    }

    /**
     * @test
     */
    public function getSpaceAfterSelectorSeparatorInitiallyReturnsSpace(): void
    {
        self::assertSame(' ', $this->subject->getSpaceAfterSelectorSeparator());
    }

    /**
     * @test
     */
    public function setSpaceAfterSelectorSeparatorSetsSpaceAfterSelectorSeparator(): void
    {
        $value = '    ';
        $this->subject->setSpaceAfterSelectorSeparator($value);

        self::assertSame($value, $this->subject->getSpaceAfterSelectorSeparator());
    }

    /**
     * @test
     */
    public function setSpaceAfterSelectorSeparatorProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setSpaceAfterSelectorSeparator(' '));
    }

    /**
     * @test
     */
    public function getSpaceBeforeListArgumentSeparatorInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getSpaceBeforeListArgumentSeparator());
    }

    /**
     * @test
     */
    public function setSpaceBeforeListArgumentSeparatorSetsSpaceBeforeListArgumentSeparator(): void
    {
        $value = ' ';
        $this->subject->setSpaceBeforeListArgumentSeparator($value);

        self::assertSame($value, $this->subject->getSpaceBeforeListArgumentSeparator());
    }

    /**
     * @test
     */
    public function setSpaceBeforeListArgumentSeparatorProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setSpaceBeforeListArgumentSeparator(' '));
    }

    /**
     * @test
     */
    public function getSpaceBeforeListArgumentSeparatorsInitiallyReturnsEmptyArray(): void
    {
        self::assertSame([], $this->subject->getSpaceBeforeListArgumentSeparators());
    }

    /**
     * @test
     */
    public function setSpaceBeforeListArgumentSeparatorsSetsSpaceBeforeListArgumentSeparators(): void
    {
        $value = ['/' => ' '];
        $this->subject->setSpaceBeforeListArgumentSeparators($value);

        self::assertSame($value, $this->subject->getSpaceBeforeListArgumentSeparators());
    }

    /**
     * @test
     */
    public function setSpaceBeforeListArgumentSeparatorsProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setSpaceBeforeListArgumentSeparators([]));
    }

    /**
     * @test
     */
    public function getSpaceAfterListArgumentSeparatorInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getSpaceAfterListArgumentSeparator());
    }

    /**
     * @test
     */
    public function setSpaceAfterListArgumentSeparatorSetsSpaceAfterListArgumentSeparator(): void
    {
        $value = '    ';
        $this->subject->setSpaceAfterListArgumentSeparator($value);

        self::assertSame($value, $this->subject->getSpaceAfterListArgumentSeparator());
    }

    /**
     * @test
     */
    public function setSpaceAfterListArgumentSeparatorProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setSpaceAfterListArgumentSeparator(' '));
    }

    /**
     * @test
     */
    public function getSpaceAfterListArgumentSeparatorsInitiallyReturnsEmptyArray(): void
    {
        self::assertSame([], $this->subject->getSpaceAfterListArgumentSeparators());
    }

    /**
     * @test
     */
    public function setSpaceAfterListArgumentSeparatorsSetsSpaceAfterListArgumentSeparators(): void
    {
        $value = [',' => ' '];
        $this->subject->setSpaceAfterListArgumentSeparators($value);

        self::assertSame($value, $this->subject->getSpaceAfterListArgumentSeparators());
    }

    /**
     * @test
     */
    public function setSpaceAfterListArgumentSeparatorsProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setSpaceAfterListArgumentSeparators([]));
    }

    /**
     * @test
     */
    public function getSpaceBeforeOpeningBraceInitiallyReturnsSpace(): void
    {
        self::assertSame(' ', $this->subject->getSpaceBeforeOpeningBrace());
    }

    /**
     * @test
     */
    public function setSpaceBeforeOpeningBraceSetsSpaceBeforeOpeningBrace(): void
    {
        $value = "\t";
        $this->subject->setSpaceBeforeOpeningBrace($value);

        self::assertSame($value, $this->subject->getSpaceBeforeOpeningBrace());
    }

    /**
     * @test
     */
    public function setSpaceBeforeOpeningBraceProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setSpaceBeforeOpeningBrace(' '));
    }

    /**
     * @test
     */
    public function getContentBeforeDeclarationBlockInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getContentBeforeDeclarationBlock());
    }

    /**
     * @test
     */
    public function setBeforeDeclarationBlockSetsBeforeDeclarationBlock(): void
    {
        $value = ' ';
        $this->subject->setBeforeDeclarationBlock($value);

        self::assertSame($value, $this->subject->getContentBeforeDeclarationBlock());
    }

    /**
     * @test
     */
    public function setBeforeDeclarationBlockProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setBeforeDeclarationBlock(' '));
    }

    /**
     * @test
     */
    public function getContentAfterDeclarationBlockSelectorsInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getContentAfterDeclarationBlockSelectors());
    }

    /**
     * @test
     */
    public function setAfterDeclarationBlockSelectorsSetsAfterDeclarationBlockSelectors(): void
    {
        $value = ' ';
        $this->subject->setAfterDeclarationBlockSelectors($value);

        self::assertSame($value, $this->subject->getContentAfterDeclarationBlockSelectors());
    }

    /**
     * @test
     */
    public function setAfterDeclarationBlockSelectorsProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setAfterDeclarationBlockSelectors(' '));
    }

    /**
     * @test
     */
    public function getContentAfterDeclarationBlockInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getContentAfterDeclarationBlock());
    }

    /**
     * @test
     */
    public function setAfterDeclarationBlockSetsAfterDeclarationBlock(): void
    {
        $value = ' ';
        $this->subject->setAfterDeclarationBlock($value);

        self::assertSame($value, $this->subject->getContentAfterDeclarationBlock());
    }

    /**
     * @test
     */
    public function setAfterDeclarationBlockProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setAfterDeclarationBlock(' '));
    }

    /**
     * @test
     */
    public function getIndentationInitiallyReturnsTab(): void
    {
        self::assertSame("\t", $this->subject->getIndentation());
    }

    /**
     * @test
     */
    public function setIndentationSetsIndentation(): void
    {
        $value = ' ';
        $this->subject->setIndentation($value);

        self::assertSame($value, $this->subject->getIndentation());
    }

    /**
     * @test
     */
    public function setIndentationProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setIndentation(' '));
    }

    /**
     * @test
     */
    public function shouldIgnoreExceptionsInitiallyReturnsFalse(): void
    {
        self::assertFalse($this->subject->shouldIgnoreExceptions());
    }

    /**
     * @test
     *
     * @dataProvider provideBooleans
     */
    public function setIgnoreExceptionsSetsIgnoreExceptions(bool $value): void
    {
        $this->subject->setIgnoreExceptions($value);

        self::assertSame($value, $this->subject->shouldIgnoreExceptions());
    }

    /**
     * @test
     */
    public function setIgnoreExceptionsProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setIgnoreExceptions(true));
    }

    /**
     * @test
     */
    public function shouldRenderCommentsInitiallyReturnsFalse(): void
    {
        self::assertFalse($this->subject->shouldRenderComments());
    }

    /**
     * @test
     *
     * @dataProvider provideBooleans
     */
    public function setRenderCommentsSetsRenderComments(bool $value): void
    {
        $this->subject->setRenderComments($value);

        self::assertSame($value, $this->subject->shouldRenderComments());
    }

    /**
     * @test
     */
    public function setRenderCommentsProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setRenderComments(true));
    }

    /**
     * @test
     */
    public function getIndentationLevelInitiallyReturnsZero(): void
    {
        self::assertSame(0, $this->subject->getIndentationLevel());
    }

    /**
     * @test
     */
    public function indentWithTabsByDefaultSetsIndentationToOneTab(): void
    {
        $this->subject->indentWithTabs();

        self::assertSame("\t", $this->subject->getIndentation());
    }

    /**
     * @return array<string, array{0: int<0, max>, 1: string}>
     */
    public static function provideTabIndentation(): array
    {
        return [
            'zero tabs' => [0, ''],
            'one tab' => [1, "\t"],
            'two tabs' => [2, "\t\t"],
            'three tabs' => [3, "\t\t\t"],
        ];
    }

    /**
     * @test
     * @dataProvider provideTabIndentation
     */
    public function indentWithTabsSetsIndentationToTheProvidedNumberOfTabs(
        int $numberOfTabs,
        string $expectedIndentation
    ): void {
        $this->subject->indentWithTabs($numberOfTabs);

        self::assertSame($expectedIndentation, $this->subject->getIndentation());
    }

    /**
     * @test
     */
    public function indentWithTabsProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->indentWithTabs());
    }

    /**
     * @test
     */
    public function indentWithSpacesByDefaultSetsIndentationToTwoSpaces(): void
    {
        $this->subject->indentWithSpaces();

        self::assertSame('  ', $this->subject->getIndentation());
    }

    /**
     * @return array<string, array{0: int<0, max>, 1: string}>
     */
    public static function provideSpaceIndentation(): array
    {
        return [
            'zero spaces' => [0, ''],
            'one space' => [1, ' '],
            'two spaces' => [2, '  '],
            'three spaces' => [3, '   '],
            'four spaces' => [4, '    '],
        ];
    }

    /**
     * @test
     * @dataProvider provideSpaceIndentation
     */
    public function indentWithSpacesSetsIndentationToTheProvidedNumberOfSpaces(
        int $numberOfSpaces,
        string $expectedIndentation
    ): void {
        $this->subject->indentWithSpaces($numberOfSpaces);

        self::assertSame($expectedIndentation, $this->subject->getIndentation());
    }

    /**
     * @test
     */
    public function indentWithSpacesProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->indentWithSpaces());
    }

    /**
     * @test
     */
    public function nextLevelReturnsOutputFormatInstance(): void
    {
        self::assertInstanceOf(OutputFormat::class, $this->subject->nextLevel());
    }

    /**
     * @test
     */
    public function nextLevelReturnsDifferentInstance(): void
    {
        self::assertNotSame($this->subject, $this->subject->nextLevel());
    }

    /**
     * @test
     */
    public function nextLevelReturnsCloneWithSameProperties(): void
    {
        $space = '   ';
        $this->subject->setSpaceAfterRuleName($space);

        self::assertSame($space, $this->subject->nextLevel()->getSpaceAfterRuleName());
    }

    /**
     * @test
     */
    public function nextLevelReturnsInstanceWithIndentationLevelIncreasedByOne(): void
    {
        $originalIndentationLevel = $this->subject->getIndentationLevel();

        self::assertSame($originalIndentationLevel + 1, $this->subject->nextLevel()->getIndentationLevel());
    }

    /**
     * @test
     */
    public function nextLevelReturnsInstanceWithDifferentFormatterInstance(): void
    {
        $formatter = $this->subject->getFormatter();

        self::assertNotSame($formatter, $this->subject->nextLevel()->getFormatter());
    }

    /**
     * @test
     */
    public function beLenientSetsIgnoreExceptionsToTrue(): void
    {
        $this->subject->setIgnoreExceptions(false);

        $this->subject->beLenient();

        self::assertTrue($this->subject->shouldIgnoreExceptions());
    }

    /**
     * @test
     */
    public function getFormatterReturnsOutputFormatterInstance(): void
    {
        self::assertInstanceOf(OutputFormatter::class, $this->subject->getFormatter());
    }

    /**
     * @test
     */
    public function getFormatterCalledTwoTimesReturnsSameInstance(): void
    {
        $firstCallResult = $this->subject->getFormatter();
        $secondCallResult = $this->subject->getFormatter();

        self::assertSame($firstCallResult, $secondCallResult);
    }

    /**
     * @test
     */
    public function createReturnsOutputFormatInstance(): void
    {
        self::assertInstanceOf(OutputFormat::class, OutputFormat::create());
    }

    /**
     * @test
     */
    public function createCreatesInstanceWithDefaultSettings(): void
    {
        self::assertEquals(new OutputFormat(), OutputFormat::create());
    }

    /**
     * @test
     */
    public function createCalledTwoTimesReturnsDifferentInstances(): void
    {
        $firstCallResult = OutputFormat::create();
        $secondCallResult = OutputFormat::create();

        self::assertNotSame($firstCallResult, $secondCallResult);
    }

    /**
     * @test
     */
    public function createCompactReturnsOutputFormatInstance(): void
    {
        self::assertInstanceOf(OutputFormat::class, OutputFormat::createCompact());
    }

    /**
     * @test
     */
    public function createCompactCalledTwoTimesReturnsDifferentInstances(): void
    {
        $firstCallResult = OutputFormat::createCompact();
        $secondCallResult = OutputFormat::createCompact();

        self::assertNotSame($firstCallResult, $secondCallResult);
    }

    /**
     * @test
     */
    public function createCompactReturnsInstanceWithSpaceBeforeRulesSetToEmptyString(): void
    {
        $newInstance = OutputFormat::createCompact();

        self::assertSame('', $newInstance->getSpaceBeforeRules());
    }

    /**
     * @test
     */
    public function createCompactReturnsInstanceWithSpaceBetweenRulesSetToEmptyString(): void
    {
        $newInstance = OutputFormat::createCompact();

        self::assertSame('', $newInstance->getSpaceBetweenRules());
    }

    /**
     * @test
     */
    public function createCompactReturnsInstanceWithSpaceAfterRulesSetToEmptyString(): void
    {
        $newInstance = OutputFormat::createCompact();

        self::assertSame('', $newInstance->getSpaceAfterRules());
    }

    /**
     * @test
     */
    public function createCompactReturnsInstanceWithSpaceBeforeBlocksSetToEmptyString(): void
    {
        $newInstance = OutputFormat::createCompact();

        self::assertSame('', $newInstance->getSpaceBeforeBlocks());
    }

    /**
     * @test
     */
    public function createCompactReturnsInstanceWithSpaceBetweenBlocksSetToEmptyString(): void
    {
        $newInstance = OutputFormat::createCompact();

        self::assertSame('', $newInstance->getSpaceBetweenBlocks());
    }

    /**
     * @test
     */
    public function createCompactReturnsInstanceWithSpaceAfterBlocksSetToEmptyString(): void
    {
        $newInstance = OutputFormat::createCompact();

        self::assertSame('', $newInstance->getSpaceAfterBlocks());
    }

    /**
     * @test
     */
    public function createCompactReturnsInstanceWithSpaceAfterRuleNameSetToEmptyString(): void
    {
        $newInstance = OutputFormat::createCompact();

        self::assertSame('', $newInstance->getSpaceAfterRuleName());
    }

    /**
     * @test
     */
    public function createCompactReturnsInstanceWithSpaceBeforeOpeningBraceSetToEmptyString(): void
    {
        $newInstance = OutputFormat::createCompact();

        self::assertSame('', $newInstance->getSpaceBeforeOpeningBrace());
    }

    /**
     * @test
     */
    public function createCompactReturnsInstanceWithSpaceAfterSelectorSeparatorSetToEmptyString(): void
    {
        $newInstance = OutputFormat::createCompact();

        self::assertSame('', $newInstance->getSpaceAfterSelectorSeparator());
    }

    /**
     * @test
     */
    public function createCompactReturnsInstanceWithSpaceAfterListArgumentSeparatorsSetToEmptyArray(): void
    {
        $newInstance = OutputFormat::createCompact();

        self::assertSame([], $newInstance->getSpaceAfterListArgumentSeparators());
    }

    /**
     * @test
     */
    public function createCompactReturnsInstanceWithRenderSemicolonAfterLastRuleDisabled(): void
    {
        $newInstance = OutputFormat::createCompact();

        self::assertFalse($newInstance->shouldRenderSemicolonAfterLastRule());
    }

    /**
     * @test
     */
    public function createCompactReturnsInstanceWithRenderCommentsDisabled(): void
    {
        $newInstance = OutputFormat::createCompact();

        self::assertFalse($newInstance->shouldRenderComments());
    }

    /**
     * @test
     */
    public function createPrettyReturnsOutputFormatInstance(): void
    {
        self::assertInstanceOf(OutputFormat::class, OutputFormat::createPretty());
    }

    /**
     * @test
     */
    public function createPrettyCalledTwoTimesReturnsDifferentInstances(): void
    {
        $firstCallResult = OutputFormat::createPretty();
        $secondCallResult = OutputFormat::createPretty();

        self::assertNotSame($firstCallResult, $secondCallResult);
    }

    /**
     * @test
     */
    public function createPrettyReturnsInstanceWithSpaceBeforeRulesSetToNewline(): void
    {
        $newInstance = OutputFormat::createPretty();

        self::assertSame("\n", $newInstance->getSpaceBeforeRules());
    }

    /**
     * @test
     */
    public function createPrettyReturnsInstanceWithSpaceBetweenRulesSetToNewline(): void
    {
        $newInstance = OutputFormat::createPretty();

        self::assertSame("\n", $newInstance->getSpaceBetweenRules());
    }

    /**
     * @test
     */
    public function createPrettyReturnsInstanceWithSpaceAfterRulesSetToNewline(): void
    {
        $newInstance = OutputFormat::createPretty();

        self::assertSame("\n", $newInstance->getSpaceAfterRules());
    }

    /**
     * @test
     */
    public function createPrettyReturnsInstanceWithSpaceBeforeBlocksSetToNewline(): void
    {
        $newInstance = OutputFormat::createPretty();

        self::assertSame("\n", $newInstance->getSpaceBeforeBlocks());
    }

    /**
     * @test
     */
    public function createPrettyReturnsInstanceWithSpaceBetweenBlocksSetToTwoNewlines(): void
    {
        $newInstance = OutputFormat::createPretty();

        self::assertSame("\n\n", $newInstance->getSpaceBetweenBlocks());
    }

    /**
     * @test
     */
    public function createPrettyReturnsInstanceWithSpaceAfterBlocksSetToNewline(): void
    {
        $newInstance = OutputFormat::createPretty();

        self::assertSame("\n", $newInstance->getSpaceAfterBlocks());
    }

    /**
     * @test
     */
    public function createPrettyReturnsInstanceWithSpaceAfterRuleNameSetToSpace(): void
    {
        $newInstance = OutputFormat::createPretty();

        self::assertSame(' ', $newInstance->getSpaceAfterRuleName());
    }

    /**
     * @test
     */
    public function createPrettyReturnsInstanceWithSpaceBeforeOpeningBraceSetToSpace(): void
    {
        $newInstance = OutputFormat::createPretty();

        self::assertSame(' ', $newInstance->getSpaceBeforeOpeningBrace());
    }

    /**
     * @test
     */
    public function createPrettyReturnsInstanceWithSpaceAfterSelectorSeparatorSetToSpace(): void
    {
        $newInstance = OutputFormat::createPretty();

        self::assertSame(' ', $newInstance->getSpaceAfterSelectorSeparator());
    }

    /**
     * @test
     */
    public function createPrettyReturnsInstanceWithSpaceAfterListArgumentSeparatorsSetToSpaceForCommaOnly(): void
    {
        $newInstance = OutputFormat::createPretty();

        self::assertSame([',' => ' '], $newInstance->getSpaceAfterListArgumentSeparators());
    }

    /**
     * @test
     */
    public function createPrettyReturnsInstanceWithRenderCommentsEnabled(): void
    {
        $newInstance = OutputFormat::createPretty();

        self::assertTrue($newInstance->shouldRenderComments());
    }
}
