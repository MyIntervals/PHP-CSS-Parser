<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Property\Selector\Combinator;
use Sabberworm\CSS\Property\Selector\Component;
use Sabberworm\CSS\Property\Selector\CompoundSelector;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\Tests\Support\Traits\SelectorDataProviders;

/**
 * @covers \Sabberworm\CSS\Property\Selector
 */
final class SelectorTest extends TestCase
{
    use SelectorDataProviders;

    /**
     * @test
     */
    public function implementsRenderable(): void
    {
        $subject = new Selector('a');

        self::assertInstanceOf(Renderable::class, $subject);
    }

    /**
     * @test
     */
    public function getSelectorByDefaultReturnsSelectorProvidedToConstructor(): void
    {
        $selector = 'a';
        $subject = new Selector($selector);

        self::assertSame($selector, $subject->getSelector());
    }

    /**
     * @test
     */
    public function setSelectorOverwritesSelectorProvidedToConstructor(): void
    {
        $subject = new Selector('a');

        $selector = 'input';
        $subject->setSelector($selector);

        self::assertSame($selector, $subject->getSelector());
    }

    /**
     * @return array<non-empty-string, array{0: string}>
     */
    public static function provideInvalidSelectors(): array
    {
        return [
            'empty string' => [''],
            'space' => [' '],
            'tab' => ["\t"],
            'line feed' => ["\n"],
            'carriage return' => ["\r"],
            'percent sign' => ['%'],
            // This is currently broken.
            // 'hash only' => ['#'],
            // This is currently broken.
            // 'dot only' => ['.'],
            'slash' => ['/'],
            'less-than sign' => ['<'],
        ];
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideInvalidSelectorsForParse(): array
    {
        return [
            'a `:not` missing the closing brace' => [':not(a'],
            'a `:not` missing the opening brace' => [':not a)'],
            'attribute value missing closing single quote' => ['a[href=\'#top]'],
            'attribute value missing closing double quote' => ['a[href="#top]'],
            'attribute value with mismatched quotes, single quote opening' => ['a[href=\'#top"]'],
            'attribute value with mismatched quotes, double quote opening' => ['a[href="#top\']'],
            'real square bracket after an escaped backslash' => ['a\\\\[[href]'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideInvalidSelectors
     * @dataProvider provideInvalidSelectorsForParse
     */
    public function constructorThrowsExceptionWithInvalidSelector(string $selector): void
    {
        $this->expectException(UnexpectedTokenException::class);

        new Selector($selector);
    }

    /**
     * @test
     *
     * @dataProvider provideInvalidSelectors
     * @dataProvider provideInvalidSelectorsForParse
     */
    public function setSelectorThrowsExceptionWithInvalidSelector(string $selector): void
    {
        $this->expectException(UnexpectedTokenException::class);

        $subject = new Selector('a');

        $subject->setSelector($selector);
    }

    /**
     * @return array<
     *             non-empty-string,
     *             array{
     *                 0: non-empty-list<Component>,
     *                 1: non-empty-list<array{class: non-empty-string, value: non-empty-string}>
     *             }
     *         >
     */
    public static function provideComponentsAndArrayRepresentation(): array
    {
        return [
            'simple selector' => [
                [new CompoundSelector('p')],
                [
                    [
                        'class' => 'CompoundSelector',
                        'value' => 'p',
                    ],
                ],
            ],
            'selector with combinator' => [
                [
                    new CompoundSelector('ul'),
                    new Combinator('>'),
                    new CompoundSelector('li'),
                ],
                [
                    [
                        'class' => 'CompoundSelector',
                        'value' => 'ul',
                    ],
                    [
                        'class' => 'Combinator',
                        'value' => '>',
                    ],
                    [
                        'class' => 'CompoundSelector',
                        'value' => 'li',
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-list<Component> $components
     * @param non-empty-list<array{class: non-empty-string, value: non-empty-string}> $expectedRepresenation
     *
     * @dataProvider provideComponentsAndArrayRepresentation
     */
    public function constructsWithComponentsProvided(array $components, array $expectedRepresenation): void
    {
        $subject = new Selector($components);

        $representation = $subject->getArrayRepresentation()['components'];
        self::assertSame($expectedRepresenation, $representation);
    }

    /**
     * @test
     */
    public function setComponentsProvidesFluentInterface(): void
    {
        $subject = new Selector([new CompoundSelector('p')]);

        $result = $subject->setComponents([new CompoundSelector('li')]);

        self::assertSame($subject, $result);
    }

    /**
     * @test
     *
     * @param non-empty-list<Component> $components
     * @param non-empty-list<array{class: non-empty-string, value: non-empty-string}> $expectedRepresenation
     *
     * @dataProvider provideComponentsAndArrayRepresentation
     */
    public function setComponentsSetsComponentsProvided(array $components, array $expectedRepresenation): void
    {
        $subject = new Selector([new CompoundSelector('p')]);

        $subject->setComponents($components);

        $representation = $subject->getArrayRepresentation()['components'];
        self::assertSame($expectedRepresenation, $representation);
    }

    /**
     * @test
     *
     * @param non-empty-list<Component> $components
     *
     * @dataProvider provideComponentsAndArrayRepresentation
     */
    public function getComponentsReturnsComponentsProvidedToConstructor(array $components): void
    {
        $subject = new Selector($components);

        $result = $subject->getComponents();

        self::assertSame($components, $result);
    }

    /**
     * @test
     *
     * @param non-empty-list<Component> $components
     *
     * @dataProvider provideComponentsAndArrayRepresentation
     */
    public function getComponentsReturnsComponentsSet(array $components): void
    {
        $subject = new Selector([new CompoundSelector('p')]);
        $subject->setComponents($components);

        $result = $subject->getComponents();

        self::assertSame($components, $result);
    }

    /**
     * @test
     *
     * @param non-empty-string $selector
     * @param int<0, max> $expectedSpecificity
     *
     * @dataProvider provideSelectorAndSpecificity
     */
    public function getSpecificityByDefaultReturnsSpecificityOfSelectorProvidedToConstructor(
        string $selector,
        int $expectedSpecificity
    ): void {
        $subject = new Selector($selector);

        self::assertSame($expectedSpecificity, $subject->getSpecificity());
    }

    /**
     * @test
     *
     * @param non-empty-string $selector
     * @param int<0, max> $expectedSpecificity
     *
     * @dataProvider provideSelectorAndSpecificity
     */
    public function getSpecificityReturnsSpecificityOfSelectorLastProvidedViaSetSelector(
        string $selector,
        int $expectedSpecificity
    ): void {
        $subject = new Selector('p');

        $subject->setSelector($selector);

        self::assertSame($expectedSpecificity, $subject->getSpecificity());
    }

    /**
     * @test
     *
     * @dataProvider provideSelectorAndSpecificity
     * @dataProvider provideSelectorWithEscapedQuotes
     */
    public function isValidForValidSelectorReturnsTrue(string $selector): void
    {
        self::assertTrue(Selector::isValid($selector));
    }

    /**
     * @test
     *
     * @dataProvider provideInvalidSelectors
     */
    public function isValidForInvalidSelectorReturnsFalse(string $selector): void
    {
        self::assertFalse(Selector::isValid($selector));
    }

    /**
     * @test
     */
    public function isValidThrowsForSelectorThatIsNotValidUtf8(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The selector is not valid UTF-8.');
        $this->expectExceptionCode(1787284398);

        Selector::isValid("a\xFF");
    }

    /**
     * @test
     */
    public function cleansUpSpacesWithinSelector(): void
    {
        $selector = 'p   >    small';

        $subject = new Selector($selector);

        self::assertSame('p > small', $subject->getSelector());
    }

    /**
     * @test
     */
    public function cleansUpTabsWithinSelector(): void
    {
        $selector = "p\t>\tsmall";

        $subject = new Selector($selector);

        self::assertSame('p > small', $subject->getSelector());
    }

    /**
     * @test
     */
    public function cleansUpNewLineWithinSelector(): void
    {
        $selector = "p\n>\nsmall";

        $subject = new Selector($selector);

        self::assertSame('p > small', $subject->getSelector());
    }


    /**
     * @test
     */
    public function doesNotCleanupSpacesWithinAttributeSelector(): void
    {
        $subject = new Selector('a[title="extra  space"]');

        self::assertSame('a[title="extra  space"]', $subject->getSelector());
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new Selector([new CompoundSelector('p')]);

        $result = $subject->getArrayRepresentation();

        self::assertSame('Selector', $result['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesComponent(): void
    {
        $subject = new Selector([new CompoundSelector('p.test')]);

        $result = $subject->getArrayRepresentation();

        self::assertSame('p.test', $result['components'][0]['value']);
    }
}
