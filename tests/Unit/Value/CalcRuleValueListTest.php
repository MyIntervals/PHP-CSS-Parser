<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Value\CalcRuleValueList;
use Sabberworm\CSS\Value\RuleValueList;

/**
 * @covers \Sabberworm\CSS\Value\CalcRuleValueList
 * @covers \Sabberworm\CSS\Value\RuleValueList
 * @covers \Sabberworm\CSS\Value\Value
 * @covers \Sabberworm\CSS\Value\ValueList
 */
final class CalcRuleValueListTest extends TestCase
{
    /**
     * @test
     */
    public function isRuleValueList(): void
    {
        $subject = new CalcRuleValueList();

        self::assertInstanceOf(RuleValueList::class, $subject);
    }

    /**
     * @test
     */
    public function getLineNumberByDefaultReturnsNull(): void
    {
        $subject = new CalcRuleValueList();

        self::assertNull($subject->getLineNumber());
    }

    /**
     * @test
     */
    public function getLineNumberReturnsLineNumberProvidedToConstructor(): void
    {
        $lineNumber = 42;
        $subject = new CalcRuleValueList($lineNumber);

        self::assertSame($lineNumber, $subject->getLineNumber());
    }

    /**
     * @test
     */
    public function separatorAlwaysIsComma(): void
    {
        $subject = new CalcRuleValueList();

        self::assertSame(',', $subject->getListSeparator());
    }
}
