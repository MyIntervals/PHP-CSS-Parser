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

        self::assertStringContainsString($message, $exception->getMessage());
    }

    /**
     * @test
     */
    public function getLineNoByDefaultReturnsZero(): void
    {
        $exception = new SourceException('foo');

        self::assertSame(0, $exception->getLineNo());
    }

    /**
     * @test
     */
    public function getLineNoReturnsLineNumberProvidedToConstructor(): void
    {
        $lineNumber = 17;
        $exception = new SourceException('foo', $lineNumber);

        self::assertSame($lineNumber, $exception->getLineNo());
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
    public function canBeThrown(): void
    {
        $this->expectException(SourceException::class);

        throw new SourceException('foo');
    }
}
