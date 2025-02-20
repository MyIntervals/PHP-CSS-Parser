<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\Comment\Commentable;
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
    public function spaceBeforeListArgumentSeparatorWithoutSpecificSettingReturnsDefaultSpace(): void
    {
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
    public function spaceAfterListArgumentSeparatorWithoutSpecificSettingReturnsDefaultSpace(): void
    {
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
        $renderedRenderable2 = 'coffee';
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

    /**
     * @return array<string, array{0: string}>
     */
    public function provideUnchangedStringForRemoveLastSemicolon(): array
    {
        return [
            'empty string' => [''],
            'string without semicolon' => ['earl-grey: hot'],
            'string with trailing semicolon' => ['Earl Grey: hot;'],
            'string with semicolon in the middle' => ['Earl Grey: hot; Coffee: Americano'],
            'string with semicolons in the middle and trailing' => ['Earl Grey: hot; Coffee: Americano;'],
        ];
    }

    /**
     * @test
     * @dataProvider provideUnchangedStringForRemoveLastSemicolon
     */
    public function removeLastSemicolonWithSemicolonAfterLastRuleEnabledReturnsUnchangedArgument(string $string): void
    {
        $this->outputFormat->setSemicolonAfterLastRule(true);

        $result = $this->subject->removeLastSemicolon($string);

        self::assertSame($string, $result);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public function provideChangedStringForRemoveLastSemicolon(): array
    {
        return [
            'empty string' => ['', ''],
            'non-empty string without semicolon' => ['Earl Grey: hot', 'Earl Grey: hot'],
            'just 1 semicolon' => [';', ''],
            'just 2 semicolons' => [';;', ';'],
            'string with trailing semicolon' => ['Earl Grey: hot;', 'Earl Grey: hot'],
            'string with semicolon in the middle' => [
                'Earl Grey: hot; Coffee: Americano',
                'Earl Grey: hot Coffee: Americano',
            ],
            'string with semicolon in the middle and trailing' => [
                'Earl Grey: hot; Coffee: Americano;',
                'Earl Grey: hot; Coffee: Americano',
            ],
            'string with 2 semicolons in the middle' => ['tea; coffee; Club-Mate', 'tea; coffee Club-Mate'],
            'string with 2 semicolons in the middle surrounded by spaces' => [
                'Earl Grey: hot ; Coffee: Americano ; Club-Mate: cold',
                'Earl Grey: hot ; Coffee: Americano  Club-Mate: cold',
            ],
            'string with 2 adjacent semicolons in the middle' => [
                'Earl Grey: hot;; Coffee: Americano',
                'Earl Grey: hot; Coffee: Americano',
            ],
            'string with 3 adjacent semicolons in the middle' => [
                'Earl Grey: hot;;; Coffee: Americano',
                'Earl Grey: hot;; Coffee: Americano',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideChangedStringForRemoveLastSemicolon
     */
    public function removeLastSemicolonWithSemicolonAfterLastRuleDisabledRemovesLastSemicolon(
        string $input,
        string $expected
    ): void {
        $this->outputFormat->setSemicolonAfterLastRule(false);

        $result = $this->subject->removeLastSemicolon($input);

        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function commentsWithEmptyCommentableAndRenderCommentsDisabledDoesNotReturnSpaceBetweenBlocks(): void
    {
        $this->outputFormat->setRenderComments(false);
        $spaceBetweenBlocks = ' between-space ';
        $this->outputFormat->setSpaceBetweenBlocks($spaceBetweenBlocks);

        $commentable = $this->createMock(Commentable::class);
        $commentable->method('getComments')->willReturn([]);

        $result = $this->subject->comments($commentable);

        self::assertStringNotContainsString($spaceBetweenBlocks, $result);
    }

    /**
     * @test
     */
    public function commentsWithEmptyCommentableAndRenderCommentsDisabledDoesNotReturnSpaceAfterBlocks(): void
    {
        $this->outputFormat->setRenderComments(false);
        $spaceAfterBlocks = ' after-space ';
        $this->outputFormat->setSpaceAfterBlocks($spaceAfterBlocks);

        $commentable = $this->createMock(Commentable::class);
        $commentable->method('getComments')->willReturn([]);

        $result = $this->subject->comments($commentable);

        self::assertStringNotContainsString($spaceAfterBlocks, $result);
    }

    /**
     * @test
     */
    public function commentsWithEmptyCommentableAndRenderCommentsDisabledReturnsEmptyString(): void
    {
        $this->outputFormat->setRenderComments(false);

        $commentable = $this->createMock(Commentable::class);
        $commentable->method('getComments')->willReturn([]);

        $result = $this->subject->comments($commentable);

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function commentsWithEmptyCommentableAndRenderCommentsEnabledDoesNotReturnSpaceBetweenBlocks(): void
    {
        $this->outputFormat->setRenderComments(true);
        $spaceBetweenBlocks = ' between-space ';
        $this->outputFormat->setSpaceBetweenBlocks($spaceBetweenBlocks);

        $commentable = $this->createMock(Commentable::class);
        $commentable->method('getComments')->willReturn([]);

        $result = $this->subject->comments($commentable);

        self::assertStringNotContainsString($spaceBetweenBlocks, $result);
    }

    /**
     * @test
     */
    public function commentsWithEmptyCommentableAndRenderCommentsEnabledDoesNotReturnSpaceAfterBlocks(): void
    {
        $this->outputFormat->setRenderComments(true);
        $spaceAfterBlocks = ' after-space ';
        $this->outputFormat->setSpaceAfterBlocks($spaceAfterBlocks);

        $commentable = $this->createMock(Commentable::class);
        $commentable->method('getComments')->willReturn([]);

        $result = $this->subject->comments($commentable);

        self::assertStringNotContainsString($spaceAfterBlocks, $result);
    }

    /**
     * @test
     */
    public function commentsWithEmptyCommentableAndRenderCommentsEnabledReturnsEmptyString(): void
    {
        $this->outputFormat->setRenderComments(true);

        $commentable = $this->createMock(Commentable::class);
        $commentable->method('getComments')->willReturn([]);

        $result = $this->subject->comments($commentable);

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function commentsWithCommentableWithOneCommentAndRenderCommentsDisabledReturnsEmptyString(): void
    {
        $this->outputFormat->setRenderComments(false);

        $commentText = 'I am a teapot.';
        $comment = new Comment($commentText);
        $commentable = $this->createMock(Commentable::class);
        $commentable->method('getComments')->willReturn([$comment]);

        $result = $this->subject->comments($commentable);

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function commentsWithCommentableWithOneCommentRendersComment(): void
    {
        $this->outputFormat->setRenderComments(true);

        $commentText = 'I am a teapot.';
        $comment = new Comment($commentText);
        $commentable = $this->createMock(Commentable::class);
        $commentable->method('getComments')->willReturn([$comment]);

        $result = $this->subject->comments($commentable);

        self::assertStringContainsString('/*' . $commentText . '*/', $result);
    }

    /**
     * @test
     */
    public function commentsWithCommentableWithOneCommentPutsSpaceAfterBlocksAfterRenderedComment(): void
    {
        $this->outputFormat->setRenderComments(true);
        $afterSpace = ' after-space ';
        $this->outputFormat->setSpaceAfterBlocks($afterSpace);

        $commentText = 'I am a teapot.';
        $comment = new Comment($commentText);
        $commentable = $this->createMock(Commentable::class);
        $commentable->method('getComments')->willReturn([$comment]);

        $result = $this->subject->comments($commentable);

        self::assertSame('/*' . $commentText . '*/' . $afterSpace, $result);
    }

    /**
     * @test
     */
    public function commentsWithCommentableWithTwoCommentsPutsSpaceAfterBlocksAfterLastRenderedComment(): void
    {
        $this->outputFormat->setRenderComments(true);
        $afterSpace = ' after-space ';
        $this->outputFormat->setSpaceAfterBlocks($afterSpace);

        $commentText1 = 'I am a teapot.';
        $comment1 = new Comment($commentText1);
        $commentText2 = 'But I am not.';
        $comment2 = new Comment($commentText2);
        $commentable = $this->createMock(Commentable::class);
        $commentable->method('getComments')->willReturn([$comment1, $comment2]);

        $result = $this->subject->comments($commentable);

        self::assertStringContainsString('/*' . $commentText2 . '*/' . $afterSpace, $result);
    }

    /**
     * @test
     */
    public function commentsWithCommentableWithTwoCommentsSeparatesCommentsBySpaceBetweenBlocks(): void
    {
        $this->outputFormat->setRenderComments(true);
        $betweenSpace = ' between-space ';
        $this->outputFormat->setSpaceBetweenBlocks($betweenSpace);

        $commentText1 = 'I am a teapot.';
        $comment1 = new Comment($commentText1);
        $commentText2 = 'But I am not.';
        $comment2 = new Comment($commentText2);
        $commentable = $this->createMock(Commentable::class);
        $commentable->method('getComments')->willReturn([$comment1, $comment2]);

        $result = $this->subject->comments($commentable);

        $expected = '/*' . $commentText1 . '*/' . $betweenSpace . '/*' . $commentText2 . '*/';
        self::assertStringContainsString($expected, $result);
    }

    /**
     * @test
     */
    public function commentsWithCommentableWithMoreThanTwoCommentsPutsSpaceAfterBlocksAfterLastRenderedComment(): void
    {
        $this->outputFormat->setRenderComments(true);
        $afterSpace = ' after-space ';
        $this->outputFormat->setSpaceAfterBlocks($afterSpace);

        $commentText1 = 'I am a teapot.';
        $comment1 = new Comment($commentText1);
        $commentText2 = 'But I am not.';
        $comment2 = new Comment($commentText2);
        $commentText3 = 'So what am I then?';
        $comment3 = new Comment($commentText3);
        $commentable = $this->createMock(Commentable::class);
        $commentable->method('getComments')->willReturn([$comment1, $comment2, $comment3]);

        $result = $this->subject->comments($commentable);

        self::assertStringContainsString('/*' . $commentText3 . '*/' . $afterSpace, $result);
    }

    /**
     * @test
     */
    public function commentsWithCommentableWithMoreThanTwoCommentsSeparatesCommentsBySpaceBetweenBlocks(): void
    {
        $this->outputFormat->setRenderComments(true);
        $betweenSpace = ' between-space ';
        $this->outputFormat->setSpaceBetweenBlocks($betweenSpace);

        $commentText1 = 'I am a teapot.';
        $comment1 = new Comment($commentText1);
        $commentText2 = 'But I am not.';
        $comment2 = new Comment($commentText2);
        $commentText3 = 'So what am I then?';
        $comment3 = new Comment($commentText3);
        $commentable = $this->createMock(Commentable::class);
        $commentable->method('getComments')->willReturn([$comment1, $comment2, $comment3]);

        $result = $this->subject->comments($commentable);

        $expected = '/*' . $commentText1 . '*/'
            . $betweenSpace . '/*' . $commentText2 . '*/'
            . $betweenSpace . '/*' . $commentText3 . '*/';
        self::assertStringContainsString($expected, $result);
    }
}
