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
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new LineName();

        $result = $subject->getArrayRepresentation();

        self::assertSame('LineName', $result['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationCanIncludeOneStringComponent(): void
    {
        $name = 'main-start';
        $subject = new LineName([$name]);

        $result = $subject->getArrayRepresentation();

        self::assertSame($name, $result['components'][0]['value']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationCanIncludesMultipleStringComponents(): void
    {
        $name1 = 'main-start';
        $name2 = 'main-end';
        $subject = new LineName([$name1, $name2]);

        $result = $subject->getArrayRepresentation();

        self::assertSame($name1, $result['components'][0]['value']);
        self::assertSame($name2, $result['components'][1]['value']);
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
