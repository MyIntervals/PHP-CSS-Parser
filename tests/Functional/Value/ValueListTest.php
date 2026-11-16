<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Tests\Unit\Value\Fixtures\ConcreteValueList;

/**
 * @covers \Sabberworm\CSS\Value\ValueList
 */
final class ValueListTest extends TestCase
{
    /**
     * @test
     */
    public function renderJoinsComponentsWithSeparator(): void
    {
        $subject = new ConcreteValueList(['Arial', 'sans-serif'], ',');

        self::assertSame('Arial,sans-serif', $subject->render(OutputFormat::create()));
    }

    /**
     * @test
     */
    public function renderWithPrettyFormatAddsSpaceAfterComma(): void
    {
        $subject = new ConcreteValueList(['Arial', 'sans-serif'], ',');

        self::assertSame('Arial, sans-serif', $subject->render(OutputFormat::createPretty()));
    }
}
