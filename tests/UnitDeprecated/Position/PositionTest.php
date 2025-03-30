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

    protected function setUp(): void
    {
        $this->subject = new ConcretePosition();
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
     */
    public function getLineNoInitiallyReturnsZero(): void
    {
        self::assertSame(0, $this->subject->getLineNo());
    }

    /**
     * @test
     *
     * @dataProvider provideLineNumber
     */
    public function getLineNoReturnsLineNumberSet(int $lineNumber): void
    {
        $this->subject->setPosition($lineNumber);

        self::assertSame($lineNumber, $this->subject->getLineNo());
    }

    /**
     * @test
     */
    public function getLineNoReturnsZeroAfterLineNumberCleared(): void
    {
        $this->subject->setPosition(99);

        $this->subject->setPosition(null);

        self::assertSame(0, $this->subject->getLineNo());
    }

    /**
     * @test
     */
    public function getColNoInitiallyReturnsZero(): void
    {
        self::assertSame(0, $this->subject->getColNo());
    }

    /**
     * @test
     *
     * @dataProvider provideColumnNumber
     */
    public function getColNoReturnsColumnNumberSet(int $columnNumber): void
    {
        $this->subject->setPosition(1, $columnNumber);

        self::assertSame($columnNumber, $this->subject->getColNo());
    }

    /**
     * @test
     */
    public function getColNoReturnsZeroAfterColumnNumberCleared(): void
    {
        $this->subject->setPosition(1, 99);

        $this->subject->setPosition(2);

        self::assertSame(0, $this->subject->getColNo());
    }

    /**
     * @test
     */
    public function setPositionWithZeroClearsLineNumber(): void
    {
        $this->subject->setPosition(99);

        $this->subject->setPosition(0);

        self::assertNull($this->subject->getLineNumber());
    }

    /**
     * @test
     */
    public function getLineNoAfterSetPositionWithZeroReturnsZero(): void
    {
        $this->subject->setPosition(99);

        $this->subject->setPosition(0);

        self::assertSame(0, $this->subject->getLineNo());
    }
}
