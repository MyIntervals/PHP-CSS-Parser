<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\Tests\Unit\Value\Fixtures\ConcreteValue;

/**
 * @covers \Sabberworm\CSS\Value\Value
 */
final class ValueTest extends TestCase
{
    /**
     * @test
     */
    public function implementsCSSElement(): void
    {
        $subject = new ConcreteValue();

        self::assertInstanceOf(CSSElement::class, $subject);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new ConcreteValue();

        $result = $subject->getArrayRepresentation();

        self::assertSame('ConcreteValue', $result['class']);
    }
}
