<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Position;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Tests\Unit\Position\Fixtures\ConcretePosition;
use TRegx\DataProvider\DataProviders;

/**
 * @covers \Sabberworm\CSS\Position\Position
 */
final class PositionTest extends TestCase
{
    /**
     * @var ConcretePosition
     */
    private $subject;

    /**
     * The method signature of `setUp()` is not compatible with all PHP and PHPUnit versions supported.
     */
    protected function doSetUp()
    {
        $this->subject = new ConcretePosition();
    }

    /**
     * @test
     */
    public function getLineNumberInitiallyReturnsNull()
    {
        $this->doSetUp();

        self::assertNull($this->subject->getLineNumber());
    }

    /**
     * @test
     */
    public function getColumnNumberInitiallyReturnsNull()
    {
        $this->doSetUp();

        self::assertNull($this->subject->getColumnNumber());
    }

    /**
     * @return array<non-empty-string, array{0: int<1, max>}>
     */
    public function provideLineNumber()
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
    public function setPositionOnVirginSetsLineNumber($lineNumber)
    {
        $this->doSetUp();

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
    public function setPositionSetsNewLineNumber($lineNumber)
    {
        $this->doSetUp();

        $this->subject->setPosition(99);

        $this->subject->setPosition($lineNumber);

        self::assertSame($lineNumber, $this->subject->getLineNumber());
    }

    /**
     * @test
     */
    public function setPositionWithNullClearsLineNumber()
    {
        $this->doSetUp();

        $this->subject->setPosition(99);

        $this->subject->setPosition(null);

        self::assertNull($this->subject->getLineNumber());
    }

    /**
     * @return array<non-empty-string, array{0: int<0, max>}>
     */
    public function provideColumnNumber()
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
    public function setPositionOnVirginSetsColumnNumber($columnNumber)
    {
        $this->doSetUp();

        $this->subject->setPosition(1, $columnNumber);

        self::assertSame($columnNumber, $this->subject->getColumnNumber());
    }

    /**
     * @test
     *
     * @param int $columnNumber
     *
     * @dataProvider provideColumnNumber
     */
    public function setPositionSetsNewColumnNumber($columnNumber)
    {
        $this->doSetUp();

        $this->subject->setPosition(1, 99);

        $this->subject->setPosition(2, $columnNumber);

        self::assertSame($columnNumber, $this->subject->getColumnNumber());
    }

    /**
     * @test
     */
    public function setPositionWithoutColumnNumberClearsColumnNumber()
    {
        $this->doSetUp();

        $this->subject->setPosition(1, 99);

        $this->subject->setPosition(2);

        self::assertNull($this->subject->getColumnNumber());
    }

    /**
     * @test
     */
    public function setPositionWithNullForColumnNumberClearsColumnNumber()
    {
        $this->doSetUp();

        $this->subject->setPosition(1, 99);

        $this->subject->setPosition(2, null);

        self::assertNull($this->subject->getColumnNumber());
    }

    /**
     * @return array<non-empty-string, array{0: int<1, max>, 1: int<0, max>}>
     */
    public function provideLineAndColumnNumber()
    {
        if (!\class_exists(DataProviders::class)) {
            self::markTestSkipped('`DataProviders` class is not available');
            return [];
        }

        return DataProviders::cross($this->provideLineNumber(), $this->provideColumnNumber());
    }

    /**
     * @test
     *
     * @param int $lineNumber
     * @param int $columnNumber
     *
     * @dataProvider provideLineAndColumnNumber
     */
    public function setPositionOnVirginSetsLineAndColumnNumber($lineNumber, $columnNumber)
    {
        $this->doSetUp();

        $this->subject->setPosition($lineNumber, $columnNumber);

        self::assertSame($lineNumber, $this->subject->getLineNumber());
        self::assertSame($columnNumber, $this->subject->getColumnNumber());
    }

    /**
     * @test
     *
     * @param int $lineNumber
     * @param int $columnNumber
     *
     * @dataProvider provideLineAndColumnNumber
     */
    public function setPositionSetsNewLineAndColumnNumber($lineNumber, $columnNumber)
    {
        $this->doSetUp();

        $this->subject->setPosition(98, 99);

        $this->subject->setPosition($lineNumber, $columnNumber);

        self::assertSame($lineNumber, $this->subject->getLineNumber());
        self::assertSame($columnNumber, $this->subject->getColumnNumber());
    }
}
