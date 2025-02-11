<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Parsing;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;

/**
 * @covers \Sabberworm\CSS\Parsing\UnexpectedEOFException
 */
final class UnexpectedEOFExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function getLineNoByDefaultReturnsZero(): void
    {
        $exception = new UnexpectedEOFException('expected', 'found');

        self::assertSame(0, $exception->getLineNo());
    }

    /**
     * @test
     */
    public function getLineNoReturnsLineNumberProvidedToConstructor(): void
    {
        $lineNumber = 17;
        $exception = new UnexpectedEOFException('expected', 'found', 'literal', $lineNumber);

        self::assertSame($lineNumber, $exception->getLineNo());
    }

    /**
     * @test
     */
    public function getMessageForLineNumberProvidedIncludesMessage(): void
    {
        $lineNumber = 17;
        $exception = new UnexpectedEOFException('expected', 'found', 'literal', $lineNumber);

        self::assertStringContainsString(' [line no: ' . $lineNumber . ']', $exception->getMessage());
    }

    /**
     * @test
     */
    public function canBeThrown(): void
    {
        $this->expectException(UnexpectedEOFException::class);

        throw new UnexpectedEOFException('expected', 'found');
    }

    /**
     * @test
     */
    public function messageByDefaultRefersToTokenNotFound(): void
    {
        $expected = 'tea';
        $found = 'coffee';

        $exception = new UnexpectedEOFException($expected, $found);

        $expectedMessage = 'Token “' . $expected . '” (literal) not found. Got “' . $found . '”.';
        self::assertStringContainsString($expectedMessage, $exception->getMessage());
    }

    /**
     * @test
     */
    public function messageForInvalidMatchTypeRefersToTokenNotFound(): void
    {
        $expected = 'tea';
        $found = 'coffee';

        $exception = new UnexpectedEOFException($expected, $found, 'coding');

        $expectedMessage = 'Token “' . $expected . '” (coding) not found. Got “' . $found . '”.';
        self::assertStringContainsString($expectedMessage, $exception->getMessage());
    }

    /**
     * @test
     */
    public function messageForLiteralMatchTypeRefersToTokenNotFound(): void
    {
        $expected = 'tea';
        $found = 'coffee';

        $exception = new UnexpectedEOFException($expected, $found, 'literal');

        $expectedMessage = 'Token “' . $expected . '” (literal) not found. Got “' . $found . '”.';
        self::assertStringContainsString($expectedMessage, $exception->getMessage());
    }

    /**
     * @test
     */
    public function messageForSearchMatchTypeRefersToNoResults(): void
    {
        $expected = 'tea';
        $found = 'coffee';

        $exception = new UnexpectedEOFException($expected, $found, 'search');

        $expectedMessage = 'Search for “' . $expected . '” returned no results. Context: “' . $found . '”.';
        self::assertStringContainsString($expectedMessage, $exception->getMessage());
    }

    /**
     * @test
     */
    public function messageForCountMatchTypeRefersToNumberOfCharacters(): void
    {
        $expected = 'tea';
        $found = 'coffee';

        $exception = new UnexpectedEOFException($expected, $found, 'count');

        $expectedMessage = 'Next token was expected to have ' . $expected . ' chars. Context: “' . $found . '”.';
        self::assertStringContainsString($expectedMessage, $exception->getMessage());
    }

    /**
     * @test
     */
    public function messageForIdentifierMatchTypeRefersToIdentifier(): void
    {
        $expected = 'tea';
        $found = 'coffee';

        $exception = new UnexpectedEOFException($expected, $found, 'identifier');

        $expectedMessage = 'Identifier expected. Got “' . $found . '”';
        self::assertStringContainsString($expectedMessage, $exception->getMessage());
    }

    /**
     * @test
     */
    public function messageForCustomMatchTypeMentionsExpectedAndFound(): void
    {
        $expected = 'tea';
        $found = 'coffee';

        $exception = new UnexpectedEOFException($expected, $found, 'custom');

        $expectedMessage = $expected . ' ' . $found;
        self::assertStringContainsString($expectedMessage, $exception->getMessage());
    }

    /**
     * @test
     */
    public function messageForCustomMatchTypeTrimsMessage(): void
    {
        $expected = 'tea';
        $found = 'coffee';

        $exception = new UnexpectedEOFException(' ' . $expected, $found . ' ', 'custom');

        $expectedMessage = $expected . ' ' . $found;
        self::assertStringContainsString($expectedMessage, $exception->getMessage());
    }
}
