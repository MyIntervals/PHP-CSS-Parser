<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Value\CSSString;
use Sabberworm\CSS\Value\PrimitiveValue;
use Sabberworm\CSS\Value\URL;
use Sabberworm\CSS\Value\Value;

/**
 * @covers \Sabberworm\CSS\Value\PrimitiveValue
 * @covers \Sabberworm\CSS\Value\URL
 * @covers \Sabberworm\CSS\Value\Value
 */
final class URLTest extends TestCase
{
    /**
     * @test
     */
    public function isPrimitiveValue(): void
    {
        $subject = new URL(new CSSString('http://example.com'));

        self::assertInstanceOf(PrimitiveValue::class, $subject);
    }

    /**
     * @test
     */
    public function isValue(): void
    {
        $subject = new URL(new CSSString('http://example.com'));

        self::assertInstanceOf(Value::class, $subject);
    }

    /**
     * @test
     */
    public function getUrlByDefaultReturnsUrlProvidedToConstructor(): void
    {
        $subject = new CSSString('http://example.com');

        self::assertSame($subject, (new URL($subject))->getURL());
    }

    /**
     * @test
     */
    public function setUrlReplacesUrl(): void
    {
        $subject = new URL(new CSSString('http://example.com'));

        $newUrl = new CSSString('http://example.org');
        $subject->setURL($newUrl);

        self::assertSame($newUrl, $subject->getURL());
    }

    /**
     * @test
     */
    public function getLineNumberByDefaultReturnsNull(): void
    {
        $subject = new URL(new CSSString('http://example.com'));

        self::assertNull($subject->getLineNumber());
    }

    /**
     * @test
     */
    public function getLineNumberReturnsLineNumberProvidedToConstructor(): void
    {
        $lineNumber = 42;
        $subject = new URL(new CSSString('http://example.com'), $lineNumber);

        self::assertSame($lineNumber, $subject->getLineNumber());
    }
}
