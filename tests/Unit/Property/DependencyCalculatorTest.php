<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Property\DependencyCalculator;

/**
 * @covers \Sabberworm\CSS\Property\DependencyCalculator
 */
final class DependencyCalculatorTest extends TestCase
{
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
    public function calculateSpecificityReturnsSpecificityForProvidedSelector(
        string $selector,
        int $expectedSpecificity
    ): void {
        self::assertSame($expectedSpecificity, DependencyCalculator::calculateSpecificity($selector));
    }
}
