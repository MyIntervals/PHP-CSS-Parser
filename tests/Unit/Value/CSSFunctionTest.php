<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Value\CSSFunction;

/**
 * @covers \Sabberworm\CSS\Value\CSSFunction
 * @covers \Sabberworm\CSS\Value\Value
 * @covers \Sabberworm\CSS\Value\ValueList
 */
final class CSSFunctionTest extends TestCase
{
    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new CSSFunction('filter', []);

        $result = $subject->getArrayRepresentation();

        self::assertSame('CSSFunction', $result['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesFunctionName(): void
    {
        $subject = new CSSFunction('filter', []);

        $result = $subject->getArrayRepresentation();

        self::assertSame('filter', $result['name']);
    }
}
