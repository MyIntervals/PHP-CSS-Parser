<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Value\CalcFunction;

/**
 * @covers \Sabberworm\CSS\Value\CalcFunction
 */
final class CalcFunctionTest extends TestCase
{
    /**
     * @test
     */
    public function getArrayRepresentationThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $subject = new CalcFunction('calc', []);

        $subject->getArrayRepresentation();
    }
}
