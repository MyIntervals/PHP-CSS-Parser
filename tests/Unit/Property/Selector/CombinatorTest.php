<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property\Selector;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Property\Selector\Combinator;
use Sabberworm\CSS\Property\Selector\Component;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\Settings;

/**
 * @covers \Sabberworm\CSS\Property\Selector\Combinator
 *
 * @phpstan-import-type ValidCombinatorValue from Combinator
 */
final class CombinatorTest extends TestCase
{
    /**
     * @test
     */
    public function implementsRenderable(): void
    {
        self::assertInstanceOf(Renderable::class, new Combinator('>'));
    }

    /**
     * @test
     */
    public function implementsSelectorComponent(): void
    {
        self::assertInstanceOf(Component::class, new Combinator('>'));
    }

    /**
     * @return array<non-empty-string, array{0: ValidCombinatorValue, 1: non-empty-string}>
     */
    public static function provideValidValueAndDefaultRendering(): array
    {
        return [
            'descendent' => [' ', ' '],
            'child' => ['>', ' > '],
            'next sibling' => ['+', ' + '],
            'subsequent sibling' => ['~', ' ~ '],
        ];
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideInvalidValue(): array
    {
        return [
            'other symbol' => ['@'],
            'uppercase letter' => ['Z'],
            'lowercase letter' => ['j'],
            'number' => ['1'],
            'sequence' => ['abc?123'],
        ];
    }

    /**
     * @test
     *
     * @param ValidCombinatorValue $combinator
     *
     * @dataProvider provideValidValueAndDefaultRendering
     */
    public function parsesValidCombinator(string $combinator): void
    {
        $result = Combinator::parse(new ParserState($combinator, Settings::create()));

        self::assertSame($combinator, $result->getArrayRepresentation()['value']);
    }

    /**
     * @test
     *
     * @param non-empty-string $selectorComponent
     *
     * @dataProvider provideInvalidValue
     */
    public function parseThrowsExceptionWithInvalidCombinator(string $selectorComponent): void
    {
        $this->expectException(UnexpectedTokenException::class);

        Combinator::parse(new ParserState($selectorComponent, Settings::create()));
    }

    /**
     * @test
     *
     * @param ValidCombinatorValue $combinator
     *
     * @dataProvider provideValidValueAndDefaultRendering
     */
    public function parsesCombinatorWithCommentBefore(string $combinator): void
    {
        $result = Combinator::parse(new ParserState('/*comment*/' . $combinator, Settings::create()));

        self::assertSame($combinator, $result->getArrayRepresentation()['value']);
    }

    /**
     * @test
     *
     * @param ValidCombinatorValue $combinator
     *
     * @dataProvider provideValidValueAndDefaultRendering
     */
    public function parsesCombinatorWithCommentAfter(string $combinator): void
    {
        $result = Combinator::parse(new ParserState($combinator . '/*comment*/', Settings::create()));

        self::assertSame($combinator, $result->getArrayRepresentation()['value']);
    }

    /**
     * @test
     *
     * @param ValidCombinatorValue $combinator
     *
     * @dataProvider provideValidValueAndDefaultRendering
     */
    public function parseExtractsCommentBefore(string $combinator): void
    {
        $result = [];
        Combinator::parse(new ParserState('/*comment*/' . $combinator, Settings::create()), $result);

        self::assertSame('comment', $result[0]->getArrayRepresentation()['contents']);
    }

    /**
     * @test
     *
     * @param ValidCombinatorValue $combinator
     *
     * @dataProvider provideValidValueAndDefaultRendering
     */
    public function parseExtractsCommentAfter(string $combinator): void
    {
        $result = [];
        Combinator::parse(new ParserState($combinator . '/*comment*/', Settings::create()), $result);

        self::assertSame('comment', $result[0]->getArrayRepresentation()['contents']);
    }

    /**
     * @test
     *
     * @param ValidCombinatorValue $value
     *
     * @dataProvider provideValidValueAndDefaultRendering
     */
    public function constructsWithValueProvided(string $value): void
    {
        $subject = new Combinator($value);

        self::assertSame($value, $subject->getArrayRepresentation()['value']);
    }

    /**
     * @test
     *
     * @param non-empty-string $value
     *
     * @dataProvider provideInvalidValue
     */
    public function constructorThrowsExceptionWithInvalidValue(string $value): void
    {
        $this->expectException(\UnexpectedValueException::class);

        new Combinator($value);
    }

    /**
     * @test
     *
     * @param ValidCombinatorValue $value
     *
     * @dataProvider provideValidValueAndDefaultRendering
     */
    public function setValueSetsValueProvided(string $value): void
    {
        $subject = new Combinator('>');

        $subject->setValue($value);

        self::assertSame($value, $subject->getArrayRepresentation()['value']);
    }

    /**
     * @test
     *
     * @param non-empty-string $value
     *
     * @dataProvider provideInvalidValue
     */
    public function setValueThrowsExceptionWithInvalidValue(string $value): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $subject = new Combinator('>');

        $subject->setValue($value);
    }

    /**
     * @test
     *
     * @param ValidCombinatorValue $value
     *
     * @dataProvider provideValidValueAndDefaultRendering
     */
    public function getValueReturnsValueProvidedToConstructor(string $value): void
    {
        $subject = new Combinator($value);

        $result = $subject->getValue();

        self::assertSame($value, $result);
    }

    /**
     * @test
     *
     * @param ValidCombinatorValue $value
     *
     * @dataProvider provideValidValueAndDefaultRendering
     */
    public function getValueReturnsValueProvidedToSetValue(string $value): void
    {
        $subject = new Combinator('>');
        $subject->setValue($value);

        $result = $subject->getValue();

        self::assertSame($value, $result);
    }

    /**
     * @test
     *
     * @param ValidCombinatorValue $value
     *
     * @dataProvider provideValidValueAndDefaultRendering
     */
    public function hasNoSpecificity(string $value): void
    {
        $subject = new Combinator($value);

        self::assertSame(0, $subject->getSpecificity());
    }

    /**
     * @test
     *
     * @param ValidCombinatorValue $value
     * @param non-empty-string $expectedRendering
     *
     * @dataProvider provideValidValueAndDefaultRendering
     */
    public function renderReturnsDefaultRenderingForValueProvided(string $value, string $expectedRendering): void
    {
        $subject = new Combinator($value);

        self::assertSame($expectedRendering, $subject->render(OutputFormat::create()));
    }

    /**
     * @return array<non-empty-string, array{0: string}>
     */
    public static function provideSpacing(): array
    {
        return [
            'empty string' => [''],
            'space' => [' '],
            'newline' => ["\n"],
            'carriage return' => ["\r"],
            'tab' => ["\t"],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideSpacing
     */
    public function renderIncludesSpacingSetInOutputFormat(string $spacing): void
    {
        $subject = new Combinator('>');
        $outputFormat = (OutputFormat::create())->setSpaceAroundSelectorCombinator($spacing);

        $result = $subject->render($outputFormat);

        $expectedRendering = $spacing . '>' . $spacing;
        self::assertSame($expectedRendering, $result);
    }

    /**
     * @test
     *
     * @dataProvider provideSpacing
     */
    public function renderReturnsSpacingSetInOutputFormatForDescendentCombinatorOrSpaceIfNeeded(string $spacing): void
    {
        $subject = new Combinator(' ');
        $outputFormat = (OutputFormat::create())->setSpaceAroundSelectorCombinator($spacing);

        $result = $subject->render($outputFormat);

        $expectedRendering = $spacing !== '' ? $spacing : ' ';
        self::assertSame($expectedRendering, $result);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new Combinator('>');

        $result = $subject->getArrayRepresentation();

        self::assertSame('Combinator', $result['class']);
    }

    /**
     * @test
     *
     * @param ValidCombinatorValue $value
     *
     * @dataProvider provideValidValueAndDefaultRendering
     */
    public function getArrayRepresentationIncludesValue(string $value): void
    {
        $subject = new Combinator($value);

        $result = $subject->getArrayRepresentation();

        self::assertSame($value, $result['value']);
    }
}
