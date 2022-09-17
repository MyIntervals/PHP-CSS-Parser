<?php

namespace Sabberworm\CSS\Tests\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Value\CalcRuleValueList;
use Sabberworm\CSS\Value\RuleValueList;

/**
 * @covers \Sabberworm\CSS\Value\CalcRuleValueList
 */
class CalcRuleValueListTest extends TestCase
{
    /**
     * @test
     */
    public function isRuleValueList()
    {
        $subject = new CalcRuleValueList();

        self::assertInstanceOf(RuleValueList::class, $subject);
    }

    /**
     * @test
     */
    public function getLineNumberByDefaultReturnsZero()
    {
        $subject = new CalcRuleValueList();

        self::assertSame(0, $subject->getLineNo());
    }

    /**
     * @test
     */
    public function getLineNoReturnsLineNumberProvidedToConstructor()
    {
        $lineNumber = 42;

        $subject = new CalcRuleValueList($lineNumber);

        self::assertSame($lineNumber, $subject->getLineNo());
    }

    /**
     * @test
     */
    public function separatorAlwaysIsComma()
    {
        $subject = new CalcRuleValueList();

        self::assertSame(',', $subject->getListSeparator());
    }
}
