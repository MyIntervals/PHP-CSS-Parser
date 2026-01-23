<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Renderable;

/**
 * @covers \Sabberworm\CSS\Property\Selector
 */
final class SelectorTest extends TestCase
{
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
     * @return array<string, array{0: non-empty-string, 1: int<0, max>}>
     */
    public static function provideSelectorsAndSpecificities(): array
    {
        return [
            'type' => ['a', 1],
            'class' => ['.highlighted', 10],
            'type with class' => ['li.green', 11],
            'pseudo-class' => [':hover', 10],
            'type with pseudo-class' => ['a:hover', 11],
            'class with pseudo-class' => ['.help:hover', 20],
            'ID' => ['#file', 100],
            'ID and descendent class' => ['#test .help', 110],
            'type with ID' => ['h2#my-mug', 101],
            'pseudo-element' => ['::before', 1],
            'type with pseudo-element' => ['li::before', 2],
            'type and descendent type with pseudo-element' => ['ol li::before', 3],
            '`not`' => [':not(#your-mug)', 100],
            // TODO, broken: The specificity should be the highest of the `:not` arguments, not the sum.
            '`not` with multiple arguments' => [':not(#your-mug, .their-mug)', 110],
            'attribute with `"`' => ['[alt="{}()[]\\"\',"]', 10],
            'attribute with `\'`' => ['[alt=\'{}()[]"\\\',\']', 10],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $selector
     * @param int<0, max> $expectedSpecificity
     *
     * @dataProvider provideSelectorsAndSpecificities
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
     * @dataProvider provideSelectorsAndSpecificities
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
     * @dataProvider provideSelectorsAndSpecificities
     */
    public function isValidForValidSelectorReturnsTrue(string $selector): void
    {
        self::assertTrue(Selector::isValid($selector));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function provideInvalidSelectors(): array
    {
        return [
            // This is currently broken.
            // 'empty string' => [''],
            'percent sign' => ['%'],
            // This is currently broken.
            // 'hash only' => ['#'],
            // This is currently broken.
            // 'dot only' => ['.'],
            'slash' => ['/'],
            'less-than sign' => ['<'],
            // This is currently broken.
            // 'whitespace only' => [" \t\n\r"],
        ];
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
    public function getArrayRepresentationThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $subject = new Selector('a');

        $subject->getArrayRepresentation();
    }
}
