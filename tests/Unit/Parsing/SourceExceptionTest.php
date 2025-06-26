<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Parsing;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Parsing\SourceException;

/**
 * @covers \Sabberworm\CSS\Parsing\SourceException
 */
final class SourceExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function getMessageReturnsMessageProvidedToConstructor(): void
    {
        $message = 'The cake is a lie.';
        $exception = new SourceException($message);

        self::assertSame($message, $exception->getMessage());
    }

    /**
     * @test
     */
    public function getLineNumberByDefaultReturnsNull(): void
    {
        $subject = new SourceException('foo');

        self::assertNull($subject->getLineNumber());
    }

    /**
     * @test
     */
    public function getLineNumberReturnsLineNumberProvidedToConstructor(): void
    {
        $lineNumber = 42;
        $subject = new SourceException('foo', $lineNumber);

        self::assertSame($lineNumber, $subject->getLineNumber());
    }

    /**
     * @test
     */
    public function getMessageWithLineNumberProvidedIncludesLineNumber(): void
    {
        $lineNumber = 17;
        $exception = new SourceException('foo', $lineNumber);

        self::assertStringContainsString(' [line no: ' . $lineNumber . ']', $exception->getMessage());
    }

    /**
     * @test
     */
    public function getMessageWithLineNumberProvidedIncludesMessage(): void
    {
        $message = 'There is no flatware.';
        $exception = new SourceException($message, 17);

        self::assertStringContainsString($message, $exception->getMessage());
    }

    /**
     * @test
     */
    public function canBeThrown(): void
    {
        $this->expectException(SourceException::class);

        throw new SourceException('foo');
    }
}
