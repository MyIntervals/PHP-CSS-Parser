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
        $value = 'x';
        $this->subject->setStringQuotingType($value);

        self::assertSame($value, $this->subject->getStringQuotingType());
    }

    /**
     * @test
     */
    public function setStringQuotingTypeProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setStringQuotingType('x'));
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
        $value = '    ';
        $this->subject->setSpaceAfterRuleName($value);

        self::assertSame($value, $this->subject->getSpaceAfterRuleName());
    }

    /**
     * @test
     */
    public function setSpaceAfterRuleNameProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->setSpaceAfterRuleName('    '));
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
}
