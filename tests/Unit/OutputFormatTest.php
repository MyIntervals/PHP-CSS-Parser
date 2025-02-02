<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;

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
    public function getRGBHashNotationInitiallyReturnsTrue(): void
    {
        self::assertTrue($this->subject->getRGBHashNotation());
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

        self::assertSame($value, $this->subject->getRGBHashNotation());
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
    public function getSemicolonAfterLastRuleInitiallyReturnsTrue(): void
    {
        self::assertTrue($this->subject->getSemicolonAfterLastRule());
    }

    /**
     * @test
     *
     * @dataProvider provideBooleans
     */
    public function setSemicolonAfterLastRuleSetsSemicolonAfterLastRule(bool $value): void
    {
        $this->subject->setSemicolonAfterLastRule($value);

        self::assertSame($value, $this->subject->getSemicolonAfterLastRule());
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
    public function getBeforeAtRuleBlockInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getBeforeAtRuleBlock());
    }

    /**
     * @test
     */
    public function setBeforeAtRuleBlockSetsBeforeAtRuleBlock(): void
    {
        $value = ' ';
        $this->subject->setBeforeAtRuleBlock($value);

        self::assertSame($value, $this->subject->getBeforeAtRuleBlock());
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
    public function getAfterAtRuleBlockInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getAfterAtRuleBlock());
    }

    /**
     * @test
     */
    public function setAfterAtRuleBlockSetsAfterAtRuleBlock(): void
    {
        $value = ' ';
        $this->subject->setAfterAtRuleBlock($value);

        self::assertSame($value, $this->subject->getAfterAtRuleBlock());
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
    public function getBeforeDeclarationBlockInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getBeforeDeclarationBlock());
    }

    /**
     * @test
     */
    public function setBeforeDeclarationBlockSetsBeforeDeclarationBlock(): void
    {
        $value = ' ';
        $this->subject->setBeforeDeclarationBlock($value);

        self::assertSame($value, $this->subject->getBeforeDeclarationBlock());
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
    public function getAfterDeclarationBlockSelectorsInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getAfterDeclarationBlockSelectors());
    }

    /**
     * @test
     */
    public function setAfterDeclarationBlockSelectorsSetsAfterDeclarationBlockSelectors(): void
    {
        $value = ' ';
        $this->subject->setAfterDeclarationBlockSelectors($value);

        self::assertSame($value, $this->subject->getAfterDeclarationBlockSelectors());
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
    public function getAfterDeclarationBlockInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getAfterDeclarationBlock());
    }

    /**
     * @test
     */
    public function setAfterDeclarationBlockSetsAfterDeclarationBlock(): void
    {
        $value = ' ';
        $this->subject->setAfterDeclarationBlock($value);

        self::assertSame($value, $this->subject->getAfterDeclarationBlock());
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
    public function getIgnoreExceptionsInitiallyReturnsFalse(): void
    {
        self::assertFalse($this->subject->getIgnoreExceptions());
    }

    /**
     * @test
     *
     * @dataProvider provideBooleans
     */
    public function setIgnoreExceptionsSetsIgnoreExceptions(bool $value): void
    {
        $this->subject->setIgnoreExceptions($value);

        self::assertSame($value, $this->subject->getIgnoreExceptions());
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
    public function getRenderCommentsInitiallyReturnsFalse(): void
    {
        self::assertFalse($this->subject->getRenderComments());
    }

    /**
     * @test
     *
     * @dataProvider provideBooleans
     */
    public function setRenderCommentsSetsRenderComments(bool $value): void
    {
        $this->subject->setRenderComments($value);

        self::assertSame($value, $this->subject->getRenderComments());
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
    public function setIndentationLevelSetsIndentationLevel(): void
    {
        $value = 4;
        $this->subject->setIndentationLevel($value);

        self::assertSame($value, $this->subject->getIndentationLevel());
    }

    /**
     * @test
     */
    public function setIndentationLevelProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setIndentationLevel(4));
    }
}
