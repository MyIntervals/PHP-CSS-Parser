<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\OutputFormatter;
use Sabberworm\CSS\Renderable;

/**
 * @covers \Sabberworm\CSS\OutputFormatter
 */
final class OutputFormatterTest extends TestCase
{
    /**
     * @var OutputFormatter
     */
    private $subject;

    /**
     * @var OutputFormat
     */
    private $outputFormat;

    protected function setUp(): void
    {
        $this->outputFormat = new OutputFormat();
        $this->subject = new OutputFormatter($this->outputFormat);
    }

    /**
     * @test
     */
    public function spaceAfterRuleNameReturnsSpaceAfterRuleNameFromOutputFormat(): void
    {
        $space = '        ';
        $this->outputFormat->setSpaceAfterRuleName($space);

        self::assertSame($space, $this->subject->spaceAfterRuleName());
    }

    /**
     * @test
     */
    public function spaceBeforeRulesReturnsSpaceBeforeRulesFromOutputFormat(): void
    {
        $space = '        ';
        $this->outputFormat->setSpaceBeforeRules($space);

        self::assertSame($space, $this->subject->spaceBeforeRules());
    }

    /**
     * @test
     */
    public function spaceAfterRulesReturnsSpaceAfterRulesFromOutputFormat(): void
    {
        $space = '        ';
        $this->outputFormat->setSpaceAfterRules($space);

        self::assertSame($space, $this->subject->spaceAfterRules());
    }

    /**
     * @test
     */
    public function spaceBetweenRulesReturnsSpaceBetweenRulesFromOutputFormat(): void
    {
        $space = '        ';
        $this->outputFormat->setSpaceBetweenRules($space);

        self::assertSame($space, $this->subject->spaceBetweenRules());
    }

    /**
     * @test
     */
    public function spaceBeforeBlocksReturnsSpaceBeforeBlocksFromOutputFormat(): void
    {
        $space = '        ';
        $this->outputFormat->setSpaceBeforeBlocks($space);

        self::assertSame($space, $this->subject->spaceBeforeBlocks());
    }

    /**
     * @test
     */
    public function spaceAfterBlocksReturnsSpaceAfterBlocksFromOutputFormat(): void
    {
        $space = '        ';
        $this->outputFormat->setSpaceAfterBlocks($space);

        self::assertSame($space, $this->subject->spaceAfterBlocks());
    }

    /**
     * @test
     */
    public function spaceBetweenBlocksReturnsSpaceBetweenBlocksFromOutputFormat(): void
    {
        $space = '        ';
        $this->outputFormat->setSpaceBetweenBlocks($space);

        self::assertSame($space, $this->subject->spaceBetweenBlocks());
    }

    /**
     * @test
     */
    public function spaceBeforeSelectorSeparatorReturnsSpaceBeforeSelectorSeparatorFromOutputFormat(): void
    {
        $space = '        ';
        $this->outputFormat->setSpaceBeforeSelectorSeparator($space);

        self::assertSame($space, $this->subject->spaceBeforeSelectorSeparator());
    }

    /**
     * @test
     */
    public function spaceAfterSelectorSeparatorReturnsSpaceAfterSelectorSeparatorFromOutputFormat(): void
    {
        $space = '        ';
        $this->outputFormat->setSpaceAfterSelectorSeparator($space);

        self::assertSame($space, $this->subject->spaceAfterSelectorSeparator());
    }

    /**
     * @test
     */
    public function spaceBeforeListArgumentSeparatorReturnsSpaceSetForSpecificSeparator(): void
    {
        $separator = ',';
        $space = '        ';
        $this->outputFormat->setSpaceBeforeListArgumentSeparators([$separator => $space]);
        $defaultSpace = "\t\t\t\t";
        $this->outputFormat->setSpaceBeforeListArgumentSeparator($defaultSpace);

        self::assertSame($space, $this->subject->spaceBeforeListArgumentSeparator($separator));
    }

    /**
     * @test
     */
    public function spaceBeforeListArgumentSeparatorWithoutSpecificSettingReturnsDefaultSpace(
    ): void {
        $space = '        ';
        $this->outputFormat->setSpaceBeforeListArgumentSeparators([',' => $space]);
        $defaultSpace = "\t\t\t\t";
        $this->outputFormat->setSpaceBeforeListArgumentSeparator($defaultSpace);

        self::assertSame($defaultSpace, $this->subject->spaceBeforeListArgumentSeparator(';'));
    }

    /**
     * @test
     */
    public function spaceAfterListArgumentSeparatorReturnsSpaceSetForSpecificSeparator(): void
    {
        $separator = ',';
        $space = '        ';
        $this->outputFormat->setSpaceAfterListArgumentSeparators([$separator => $space]);
        $defaultSpace = "\t\t\t\t";
        $this->outputFormat->setSpaceAfterListArgumentSeparator($defaultSpace);

        self::assertSame($space, $this->subject->spaceAfterListArgumentSeparator($separator));
    }

    /**
     * @test
     */
    public function spaceAfterListArgumentSeparatorForNoExistingSpaceAfterProvidedSeparatorReturnsDefaultSeparator(
    ): void {
        $space = '        ';
        $this->outputFormat->setSpaceAfterListArgumentSeparators([',' => $space]);
        $defaultSpace = "\t\t\t\t";
        $this->outputFormat->setSpaceAfterListArgumentSeparator($defaultSpace);

        self::assertSame($defaultSpace, $this->subject->spaceAfterListArgumentSeparator(';'));
    }

    /**
     * @test
     */
    public function spaceBeforeOpeningBraceReturnsSpaceBeforeOpeningBraceFromOutputFormat(): void
    {
        $space = '        ';
        $this->outputFormat->setSpaceBeforeOpeningBrace($space);

        self::assertSame($space, $this->subject->spaceBeforeOpeningBrace());
    }

    /**
     * @test
     */
    public function implodeForEmptyValuesReturnsEmptyString(): void
    {
        $values = [];

        $result = $this->subject->implode(', ', $values);

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function implodeWithOneStringValueReturnsStringValue(): void
    {
        $value = 'tea';
        $values = [$value];

        $result = $this->subject->implode(', ', $values);

        self::assertSame($value, $result);
    }

    /**
     * @test
     */
    public function implodeWithMultipleStringValuesReturnsValuesSeparatedBySeparator(): void
    {
        $value1 = 'tea';
        $value2 = 'coffee';
        $values = [$value1, $value2];
        $separator = ', ';

        $result = $this->subject->implode($separator, $values);

        self::assertSame($value1 . $separator . $value2, $result);
    }

    /**
     * @test
     */
    public function implodeWithOneRenderableReturnsRenderedRenderable(): void
    {
        $renderable = $this->createMock(Renderable::class);
        $renderedRenderable = 'tea';
        $renderable->method('render')->with($this->outputFormat)->willReturn($renderedRenderable);
        $values = [$renderable];

        $result = $this->subject->implode(', ', $values);

        self::assertSame($renderedRenderable, $result);
    }

    /**
     * @test
     */
    public function implodeWithMultipleRenderablesReturnsRenderedRenderablesSeparatedBySeparator(): void
    {
        $renderable1 = $this->createMock(Renderable::class);
        $renderedRenderable1 = 'tea';
        $renderable1->method('render')->with($this->outputFormat)->willReturn($renderedRenderable1);
        $renderable2 = $this->createMock(Renderable::class);
        $renderedRenderable2 = 'tea';
        $renderable2->method('render')->with($this->outputFormat)->willReturn($renderedRenderable2);
        $values = [$renderable1, $renderable2];
        $separator = ', ';

        $result = $this->subject->implode($separator, $values);

        self::assertSame($renderedRenderable1 . $separator . $renderedRenderable2, $result);
    }

    /**
     * @test
     */
    public function implodeWithIncreaseLevelFalseUsesDefaultIndentationLevelForRendering(): void
    {
        $renderable = $this->createMock(Renderable::class);
        $renderedRenderable = 'tea';
        $renderable->method('render')->with($this->outputFormat)->willReturn($renderedRenderable);
        $values = [$renderable];

        $result = $this->subject->implode(', ', $values, false);

        self::assertSame($renderedRenderable, $result);
    }

    /**
     * @test
     */
    public function implodeWithIncreaseLevelTrueIncreasesIndentationLevelForRendering(): void
    {
        $renderable = $this->createMock(Renderable::class);
        $renderedRenderable = 'tea';
        $renderable->method('render')->with($this->outputFormat->nextLevel())->willReturn($renderedRenderable);
        $values = [$renderable];

        $result = $this->subject->implode(', ', $values, true);

        self::assertSame($renderedRenderable, $result);
    }
}
