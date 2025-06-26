<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Value\CSSString;
use Sabberworm\CSS\Value\PrimitiveValue;
use Sabberworm\CSS\Value\Value;

/**
 * @covers \Sabberworm\CSS\Value\CSSString
 * @covers \Sabberworm\CSS\Value\PrimitiveValue
 * @covers \Sabberworm\CSS\Value\Value
 */
final class CSSStringTest extends TestCase
{
    /**
     * @test
     */
    public function isPrimitiveValue(): void
    {
        $subject = new CSSString('');

        self::assertInstanceOf(PrimitiveValue::class, $subject);
    }

    /**
     * @test
     */
    public function isValue(): void
    {
        $subject = new CSSString('');

        self::assertInstanceOf(Value::class, $subject);
    }

    /**
     * @test
     */
    public function getStringReturnsStringProvidedToConstructor(): void
    {
        $string = 'coffee';
        $subject = new CSSString($string);

        self::assertSame($string, $subject->getString());
    }

    /**
     * @test
     */
    public function setStringSetsString(): void
    {
        $subject = new CSSString('');
        $string = 'coffee';

        $subject->setString($string);

        self::assertSame($string, $subject->getString());
    }

    /**
     * @test
     */
    public function getLineNumberByDefaultReturnsNull(): void
    {
        $subject = new CSSString('');

        self::assertNull($subject->getLineNumber());
    }

    /**
     * @test
     */
    public function getLineNumberReturnsLineNumberProvidedToConstructor(): void
    {
        $lineNumber = 42;
        $subject = new CSSString('', $lineNumber);

        self::assertSame($lineNumber, $subject->getLineNumber());
    }
}
