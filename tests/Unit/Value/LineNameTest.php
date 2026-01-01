<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Value\LineName;

/**
 * @covers \Sabberworm\CSS\Value\LineName
 * @covers \Sabberworm\CSS\Value\Value
 * @covers \Sabberworm\CSS\Value\ValueList
 */
final class LineNameTest extends TestCase
{
    /**
     * @test
     */
    public function getArrayRepresentationThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $subject = new LineName();

        $subject->getArrayRepresentation();
    }
}
