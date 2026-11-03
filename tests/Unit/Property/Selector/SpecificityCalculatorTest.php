<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property\Selector;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Property\Selector\SpecificityCalculator;
use Sabberworm\CSS\Tests\Support\Traits\SelectorDataProviders;

/**
 * @covers \Sabberworm\CSS\Property\Selector\SpecificityCalculator
 */
final class SpecificityCalculatorTest extends TestCase
{
    use SelectorDataProviders;

    protected function tearDown(): void
    {
        SpecificityCalculator::clearCache();
    }

    /**
     * @test
     *
     * @param non-empty-string $selector
     * @param int<0, max> $expectedSpecificity
     *
     * @dataProvider provideSelectorAndSpecificity
     */
    public function calculateReturnsSpecificityForProvidedSelector(
        string $selector,
        int $expectedSpecificity
    ): void {
        self::assertSame($expectedSpecificity, SpecificityCalculator::calculate($selector));
    }

    /**
     * @test
     *
     * @param non-empty-string $selector
     * @param int<0, max> $expectedSpecificity
     *
     * @dataProvider provideSelectorAndSpecificity
     */
    public function calculateAfterClearingCacheReturnsSpecificityForProvidedSelector(
        string $selector,
        int $expectedSpecificity
    ): void {
        SpecificityCalculator::clearCache();

        self::assertSame($expectedSpecificity, SpecificityCalculator::calculate($selector));
    }

    /**
     * @test
     */
    public function calculateCalledTwoTimesReturnsSameSpecificityForProvidedSelector(): void
    {
        $selector = '#test .help';

        $firstResult = SpecificityCalculator::calculate($selector);
        $secondResult = SpecificityCalculator::calculate($selector);

        self::assertSame($firstResult, $secondResult);
    }

    /**
     * @test
     */
    public function calculateCalledReturnsSameSpecificityForProvidedSelectorBeforeAndAfterClearingCache(): void
    {
        $selector = '#test .help';

        $firstResult = SpecificityCalculator::calculate($selector);
        SpecificityCalculator::clearCache();
        $secondResult = SpecificityCalculator::calculate($selector);

        self::assertSame($firstResult, $secondResult);
    }
}
