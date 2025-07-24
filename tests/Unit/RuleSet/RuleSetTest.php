<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\RuleSet\RuleSet;

/**
 * @covers \Sabberworm\CSS\RuleSet\RuleSet
 */
final class RuleSetTest extends TestCase
{
    use RuleContainerTest;

    /**
     * @var RuleSet
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new RuleSet();
    }

    /**
     * @test
     */
    public function implementsCSSElement(): void
    {
        self::assertInstanceOf(CSSElement::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsCSSListItem(): void
    {
        self::assertInstanceOf(CSSListItem::class, $this->subject);
    }

    /**
     * @test
     */
    public function getLineNumberByDefaultReturnsNull(): void
    {
        $result = $this->subject->getLineNumber();

        self::assertNull($result);
    }

    /**
     * @return array<non-empty-string, array{0: int<1, max>|null}>
     */
    public function provideLineNumber(): array
    {
        return [
            'null' => [null],
            'line 1' => [1],
            'line 42' => [42],
        ];
    }

    /**
     * @test
     *
     * @param int<1, max>|null $lineNumber
     *
     * @dataProvider provideLineNumber
     */
    public function getLineNumberReturnsLineNumberPassedToConstructor(?int $lineNumber): void
    {
        $subject = new RuleSet($lineNumber);

        $result = $subject->getLineNumber();

        self::assertSame($lineNumber, $result);
    }
}
