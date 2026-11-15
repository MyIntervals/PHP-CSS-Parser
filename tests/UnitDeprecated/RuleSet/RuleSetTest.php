<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\UnitDeprecated\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Property\Declaration;
use Sabberworm\CSS\RuleSet\RuleSet;

/**
 * @covers \Sabberworm\CSS\RuleSet\LegacyDeclarationListMethods
 * @covers \Sabberworm\CSS\RuleSet\RuleSet
 */
final class RuleSetTest extends TestCase
{
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
    public function addRuleAddsDeclaration(): void
    {
        $declaration = new Declaration('color');

        $this->subject->addRule($declaration);

        self::assertSame([$declaration], $this->subject->getDeclarations());
    }

    /**
     * @test
     */
    public function addRuleInsertsDeclarationBeforeSibling(): void
    {
        $sibling = new Declaration('color');
        $this->subject->addDeclaration($sibling);
        $declaration = new Declaration('margin');

        $this->subject->addRule($declaration, $sibling);

        self::assertSame([$declaration, $sibling], $this->subject->getDeclarations());
    }

    /**
     * @test
     */
    public function removeRuleRemovesDeclaration(): void
    {
        $declaration = new Declaration('color');
        $this->subject->addDeclaration($declaration);

        $this->subject->removeRule($declaration);

        self::assertSame([], $this->subject->getDeclarations());
    }

    /**
     * @test
     */
    public function removeMatchingRulesRemovesMatchingDeclarations(): void
    {
        $keptDeclaration = new Declaration('color');
        $this->subject->setDeclarations([new Declaration('margin-top'), $keptDeclaration]);

        $this->subject->removeMatchingRules('margin-');

        self::assertSame([$keptDeclaration], $this->subject->getDeclarations());
    }

    /**
     * @test
     */
    public function removeAllRulesRemovesAllDeclarations(): void
    {
        $this->subject->setDeclarations([new Declaration('color'), new Declaration('margin')]);

        $this->subject->removeAllRules();

        self::assertSame([], $this->subject->getDeclarations());
    }

    /**
     * @test
     */
    public function setRulesSetsDeclarations(): void
    {
        $declarations = [new Declaration('color'), new Declaration('margin')];

        $this->subject->setRules($declarations);

        self::assertSame($declarations, $this->subject->getDeclarations());
    }

    /**
     * @test
     */
    public function getRulesReturnsDeclarationsMatchingSearchPattern(): void
    {
        $marginDeclaration = new Declaration('margin');
        $this->subject->setDeclarations([new Declaration('color'), $marginDeclaration]);

        self::assertSame([$marginDeclaration], $this->subject->getRules('margin'));
    }

    /**
     * @test
     */
    public function getRulesAssocReturnsDeclarationsKeyedByPropertyName(): void
    {
        $colorDeclaration = new Declaration('color');
        $marginDeclaration = new Declaration('margin');
        $this->subject->setDeclarations([$colorDeclaration, $marginDeclaration]);

        self::assertSame(
            ['color' => $colorDeclaration, 'margin' => $marginDeclaration],
            $this->subject->getRulesAssoc()
        );
    }
}
