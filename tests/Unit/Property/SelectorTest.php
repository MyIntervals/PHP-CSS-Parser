<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Property\Selector;

/**
 * @covers \Sabberworm\CSS\Property\Selector
 */
final class SelectorTest extends TestCase
{
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
            'element' => ['a', 1],
            'element and descendant with pseudo-selector' => ['ol li::before', 3],
            'class' => ['.highlighted', 10],
            'element with class' => ['li.green', 11],
            'class with pseudo-selector' => ['.help:hover', 20],
            'ID' => ['#file', 100],
            'ID and descendant class' => ['#test .help', 110],
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
}
