<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Value\Color;

/**
 * @covers \Sabberworm\CSS\Value\Color
 */
final class ColorTest extends TestCase
{
    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new Color(['r' => '10', 'g' => '20', 'b' => '30']);

        $result = $subject->getArrayRepresentation();

        self::assertSame('Color', $result['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesValueKeysAsName(): void
    {
        $subject = new Color(['r' => '10', 'g' => '20', 'b' => '30']);

        $result = $subject->getArrayRepresentation();

        self::assertSame('rgb', $result['name']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesValueAsComponents(): void
    {
        $subject = new Color(['r' => '10', 'g' => '20', 'b' => '30']);

        $result = $subject->getArrayRepresentation();

        $expected = [
            'r' => ['class' => 'string', 'value' => '10'],
            'g' => ['class' => 'string', 'value' => '20'],
            'b' => ['class' => 'string', 'value' => '30'],
        ];
        self::assertSame($expected, $result['components']);
    }
}
