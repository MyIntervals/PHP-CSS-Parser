<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\UnitDeprecated\Position;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Tests\Unit\Position\Fixtures\ConcretePosition;

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
     */
    public function getLineNoInitiallyReturnsZero()
    {
        $this->doSetUp();

        self::assertSame(0, $this->subject->getLineNo());
    }

    /**
     * @test
     *
     * @paarm int $lineNumber
     *
     * @dataProvider provideLineNumber
     */
    public function getLineNoReturnsLineNumberSet($lineNumber)
    {
        $this->doSetUp();

        $this->subject->setPosition($lineNumber);

        self::assertSame($lineNumber, $this->subject->getLineNo());
    }

    /**
     * @test
     */
    public function getLineNoReturnsZeroAfterLineNumberCleared()
    {
        $this->doSetUp();

        $this->subject->setPosition(99);

        $this->subject->setPosition(null);

        self::assertSame(0, $this->subject->getLineNo());
    }

    /**
     * @test
     */
    public function getColNoInitiallyReturnsZero()
    {
        $this->doSetUp();

        self::assertSame(0, $this->subject->getColNo());
    }

    /**
     * @test
     *
     * @param int $columnNumber
     *
     * @dataProvider provideColumnNumber
     */
    public function getColNoReturnsColumnNumberSet($columnNumber)
    {
        $this->doSetUp();

        $this->subject->setPosition(1, $columnNumber);

        self::assertSame($columnNumber, $this->subject->getColNo());
    }

    /**
     * @test
     */
    public function getColNoReturnsZeroAfterColumnNumberCleared()
    {
        $this->doSetUp();

        $this->subject->setPosition(1, 99);

        $this->subject->setPosition(2);

        self::assertSame(0, $this->subject->getColNo());
    }

    /**
     * @test
     */
    public function setPositionWithZeroClearsLineNumber()
    {
        $this->doSetUp();

        $this->subject->setPosition(99);

        $this->subject->setPosition(0);

        self::assertNull($this->subject->getLineNumber());
    }

    /**
     * @test
     */
    public function getLineNoAfterSetPositionWithZeroReturnsZero()
    {
        $this->doSetUp();

        $this->subject->setPosition(99);

        $this->subject->setPosition(0);

        self::assertSame(0, $this->subject->getLineNo());
    }
}
