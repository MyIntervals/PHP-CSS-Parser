<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\Position\Positionable;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\RuleSet\RuleSet;

/**
 * @covers \Sabberworm\CSS\RuleSet\DeclarationBlock
 */
final class DeclarationBlockTest extends TestCase
{
    use RuleContainerTest;

    /**
     * @var DeclarationBlock
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new DeclarationBlock();
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
    public function implementsPositionable(): void
    {
        self::assertInstanceOf(Positionable::class, $this->subject);
    }

    /**
     * @test
     */
    public function getRuleSetOnVirginReturnsARuleSet(): void
    {
        $result = $this->subject->getRuleSet();

        self::assertInstanceOf(RuleSet::class, $result);
    }

    /**
     * @test
     */
    public function getRuleSetAfterRulesSetReturnsARuleSet(): void
    {
        $this->subject->setRules([new Rule('color')]);

        $result = $this->subject->getRuleSet();

        self::assertInstanceOf(RuleSet::class, $result);
    }

    /**
     * @test
     */
    public function getRuleSetOnVirginReturnsObjectWithoutRules(): void
    {
        $result = $this->subject->getRuleSet();

        self::assertSame([], $result->getRules());
    }

    /**
     * @test
     *
     * @param list<string> $propertyNamesToSet
     *
     * @dataProvider providePropertyNames
     */
    public function getRuleSetReturnsObjectWithRulesSet(array $propertyNamesToSet): void
    {
        $rules = self::createRulesFromPropertyNames($propertyNamesToSet);
        $this->subject->setRules($rules);

        $result = $this->subject->getRuleSet();

        self::assertSame($rules, $result->getRules());
    }
}
