<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\Selector;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Property\Selector;

/**
 * @covers \Sabberworm\CSS\Property\Selector
 */
final class SelectorTest extends TestCase
{
    /**
     * @test
     */
    public function renderWithVirginOutputFormatRendersSelectorPassedToConstructor(): void
    {
        $pattern = 'a';
        $subject = new Selector($pattern);

        self::assertSame($pattern, $subject->render(new OutputFormat()));
    }

    /**
     * @test
     */
    public function renderWithDefaultOutputFormatRendersSelectorPassedToConstructor(): void
    {
        $pattern = 'a';
        $subject = new Selector($pattern);

        self::assertSame($pattern, $subject->render(OutputFormat::create()));
    }

    /**
     * @test
     */
    public function renderWithCompactOutputFormatRendersSelectorPassedToConstructor(): void
    {
        $pattern = 'a';
        $subject = new Selector($pattern);

        self::assertSame($pattern, $subject->render(OutputFormat::createCompact()));
    }

    /**
     * @test
     */
    public function renderWithPrettyOutputFormatRendersSelectorPassedToConstructor(): void
    {
        $pattern = 'a';
        $subject = new Selector($pattern);

        self::assertSame($pattern, $subject->render(OutputFormat::createPretty()));
    }
}
