<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Value\LineName;
use Sabberworm\CSS\Value\Size;

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
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new LineName();

        $result = $subject->getArrayRepresentation();

        self::assertSame('LineName', $result['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesStringComponent(): void
    {
        $subject = new LineName(['Helvetica']);

        $result = $subject->getArrayRepresentation();

        self::assertSame('Helvetica', $result['components'][0]['value']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesValueComponent(): void
    {
        $subject = new LineName([new Size(1)]);

        $result = $subject->getArrayRepresentation();

        self::assertSame('Size', $result['components'][0]['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesMultipleMixedComponents(): void
    {
        $subject = new LineName([new Size(1), '+', new Size(2)]);

        $result = $subject->getArrayRepresentation();

        self::assertSame('Size', $result['components'][0]['class']);
        self::assertSame('+', $result['components'][1]['value']);
        self::assertSame('Size', $result['components'][2]['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesSpaceSeparator(): void
    {
        $subject = new LineName();

        $result = $subject->getArrayRepresentation();

        self::assertSame(' ', $result['separator']);
    }
}
