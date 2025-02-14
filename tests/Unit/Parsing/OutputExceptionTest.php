<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Parsing;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Parsing\OutputException;
use Sabberworm\CSS\Parsing\SourceException;

/**
 * @covers \Sabberworm\CSS\Parsing\OutputException
 */
final class OutputExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function extendsSourceException(): void
    {
        self::assertInstanceOf(SourceException::class, new OutputException('foo'));
    }

    /**
     * @test
     */
    public function getMessageReturnsMessageProvidedToConstructor(): void
    {
        $message = 'The cake is a lie.';
        $exception = new OutputException($message);

        self::assertStringContainsString($message, $exception->getMessage());
    }

    /**
     * @test
     */
    public function getLineNoByDefaultReturnsZero(): void
    {
        $exception = new OutputException('foo');

        self::assertSame(0, $exception->getLineNo());
    }

    /**
     * @test
     */
    public function getLineNoReturnsLineNumberProvidedToConstructor(): void
    {
        $lineNumber = 17;
        $exception = new OutputException('foo', $lineNumber);

        self::assertSame($lineNumber, $exception->getLineNo());
    }

    /**
     * @test
     */
    public function getMessageWithLineNumberProvidedIncludesLineNumber(): void
    {
        $lineNumber = 17;
        $exception = new OutputException('foo', $lineNumber);

        self::assertStringContainsString(' [line no: ' . $lineNumber . ']', $exception->getMessage());
    }

    /**
     * @test
     */
    public function canBeThrown(): void
    {
        $this->expectException(OutputException::class);

        throw new OutputException('foo');
    }
}
