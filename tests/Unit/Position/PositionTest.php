<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Position;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Tests\Unit\Position\Fixtures\ConcretePosition;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @covers \Sabberworm\CSS\Position\Position
 */
final class PositionTest extends TestCase
{
    /**
     * @var ConcretePosition
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ConcretePosition();
    }

    /**
     * @test
     */
    public function getLineNumberInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getLineNumber());
    }

    /**
     * @test
     */
    public function getColumnNumberInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getColumnNumber());
    }

    /**
     * @return array<non-empty-string, array{0: int<1, max>}>
     */
    public function provideLineNumber(): array
    {
        return [
            'line 1' => [1],
            'line 42' => [42],
        ];
    }

    /**
     * @test
     *
     * @param int<1, max> $lineNumber
     *
     * @dataProvider provideLineNumber
     */
    public function setPositionOnVirginSetsLineNumber(int $lineNumber): void
    {
        $this->subject->setPosition($lineNumber);

        self::assertSame($lineNumber, $this->subject->getLineNumber());
    }

    /**
     * @test
     *
     * @param int<1, max> $lineNumber
     *
     * @dataProvider provideLineNumber
     */
    public function setPositionSetsNewLineNumber(int $lineNumber): void
    {
        $this->subject->setPosition(99);

        $this->subject->setPosition($lineNumber);

        self::assertSame($lineNumber, $this->subject->getLineNumber());
    }

    /**
     * @test
     */
    public function setPositionWithNullClearsLineNumber(): void
    {
        $this->subject->setPosition(99);

        $this->subject->setPosition(null);

        self::assertNull($this->subject->getLineNumber());
    }

    /**
     * @return array<non-empty-string, array{0: int<0, max>}>
     */
    public function provideColumnNumber(): array
    {
        return [
            'column 0' => [0],
            'column 14' => [14],
            'column 39' => [39],
        ];
    }

    /**
     * @test
     *
     * @param int<0, max> $columnNumber
     *
     * @dataProvider provideColumnNumber
     */
    public function setPositionOnVirginSetsColumnNumber(int $columnNumber): void
    {
        $this->subject->setPosition(1, $columnNumber);

        self::assertSame($columnNumber, $this->subject->getColumnNumber());
    }

    /**
     * @test
     *
     * @dataProvider provideColumnNumber
     */
    public function setPositionSetsNewColumnNumber(int $columnNumber): void
    {
        $this->subject->setPosition(1, 99);

        $this->subject->setPosition(2, $columnNumber);

        self::assertSame($columnNumber, $this->subject->getColumnNumber());
    }

    /**
     * @test
     */
    public function setPositionWithoutColumnNumberClearsColumnNumber(): void
    {
        $this->subject->setPosition(1, 99);

        $this->subject->setPosition(2);

        self::assertNull($this->subject->getColumnNumber());
    }

    /**
     * @test
     */
    public function setPositionWithNullForColumnNumberClearsColumnNumber(): void
    {
        $this->subject->setPosition(1, 99);

        $this->subject->setPosition(2, null);

        self::assertNull($this->subject->getColumnNumber());
    }

    /**
     * @return DataProvider<non-empty-string, array{0: int<1, max>, 1: int<0, max>}>
     */
    public function provideLineAndColumnNumber(): DataProvider
    {
        return DataProvider::cross($this->provideLineNumber(), $this->provideColumnNumber());
    }

    /**
     * @test
     *
     * @dataProvider provideLineAndColumnNumber
     */
    public function setPositionOnVirginSetsLineAndColumnNumber(int $lineNumber, int $columnNumber): void
    {
        $this->subject->setPosition($lineNumber, $columnNumber);

        self::assertSame($lineNumber, $this->subject->getLineNumber());
        self::assertSame($columnNumber, $this->subject->getColumnNumber());
    }

    /**
     * @test
     *
     * @dataProvider provideLineAndColumnNumber
     */
    public function setPositionSetsNewLineAndColumnNumber(int $lineNumber, int $columnNumber): void
    {
        $this->subject->setPosition(98, 99);

        $this->subject->setPosition($lineNumber, $columnNumber);

        self::assertSame($lineNumber, $this->subject->getLineNumber());
        self::assertSame($columnNumber, $this->subject->getColumnNumber());
    }
}
