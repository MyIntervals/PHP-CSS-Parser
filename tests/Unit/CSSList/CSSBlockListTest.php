<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\CSSList\CSSList;
use Sabberworm\CSS\Property\Charset;
use Sabberworm\CSS\Property\Import;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\RuleSet\AtRuleSet;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\Tests\Unit\CSSList\Fixtures\ConcreteCSSBlockList;
use Sabberworm\CSS\Value\CSSString;
use Sabberworm\CSS\Value\URL;

/**
 * @covers \Sabberworm\CSS\CSSList\CSSBlockList
 * @covers \Sabberworm\CSS\CSSList\CSSList
 */
final class CSSBlockListTest extends TestCase
{
    /**
     * @test
     */
    public function implementsRenderable(): void
    {
        $subject = new ConcreteCSSBlockList();

        self::assertInstanceOf(Renderable::class, $subject);
    }

    /**
     * @test
     */
    public function implementsCommentable(): void
    {
        $subject = new ConcreteCSSBlockList();

        self::assertInstanceOf(Commentable::class, $subject);
    }

    /**
     * @test
     */
    public function isCSSList(): void
    {
        $subject = new ConcreteCSSBlockList();

        self::assertInstanceOf(CSSList::class, $subject);
    }

    /**
     * @test
     */
    public function getAllDeclarationBlocksWhenNoContentSetReturnsEmptyArray(): void
    {
        $subject = new ConcreteCSSBlockList();

        self::assertSame([], $subject->getAllDeclarationBlocks());
    }

    /**
     * @test
     */
    public function getAllDeclarationBlocksReturnsOneDeclarationBlockDirectlySetAsContent(): void
    {
        $subject = new ConcreteCSSBlockList();

        $declarationBlock = new DeclarationBlock();
        $subject->setContents([$declarationBlock]);

        $result = $subject->getAllDeclarationBlocks();

        self::assertSame([$declarationBlock], $result);
    }

    /**
     * @test
     */
    public function getAllDeclarationBlocksReturnsMultipleDeclarationBlocksDirectlySetAsContents(): void
    {
        $subject = new ConcreteCSSBlockList();

        $declarationBlock1 = new DeclarationBlock();
        $declarationBlock2 = new DeclarationBlock();
        $subject->setContents([$declarationBlock1, $declarationBlock2]);

        $result = $subject->getAllDeclarationBlocks();

        self::assertSame([$declarationBlock1, $declarationBlock2], $result);
    }

    /**
     * @test
     */
    public function getAllDeclarationBlocksReturnsDeclarationBlocksWithinAtRuleBlockList(): void
    {
        $subject = new ConcreteCSSBlockList();

        $declarationBlock = new DeclarationBlock();
        $atRuleBlockList = new AtRuleBlockList('media');
        $atRuleBlockList->setContents([$declarationBlock]);
        $subject->setContents([$atRuleBlockList]);

        $result = $subject->getAllDeclarationBlocks();

        self::assertSame([$declarationBlock], $result);
    }

    /**
     * @test
     */
    public function getAllDeclarationBlocksIgnoresImport(): void
    {
        $subject = new ConcreteCSSBlockList();

        $import = new Import(new URL(new CSSString('https://www.example.com/')), '');
        $subject->setContents([$import]);

        $result = $subject->getAllDeclarationBlocks();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getAllDeclarationBlocksIgnoresCharset(): void
    {
        $subject = new ConcreteCSSBlockList();

        $charset = new Charset(new CSSString('UTF-8'));
        $subject->setContents([$charset]);

        $result = $subject->getAllDeclarationBlocks();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getAllRuleSetsWhenNoContentSetReturnsEmptyArray(): void
    {
        $subject = new ConcreteCSSBlockList();

        self::assertSame([], $subject->getAllRuleSets());
    }

    /**
     * @test
     */
    public function getAllRuleSetsReturnsOneDeclarationBlockDirectlySetAsContent(): void
    {
        $subject = new ConcreteCSSBlockList();

        $declarationBlock = new DeclarationBlock();
        $subject->setContents([$declarationBlock]);

        $result = $subject->getAllRuleSets();

        self::assertSame([$declarationBlock], $result);
    }

    /**
     * @test
     */
    public function getAllRuleSetsReturnsOneAtRuleSetDirectlySetAsContent(): void
    {
        $subject = new ConcreteCSSBlockList();

        $atRuleSet = new AtRuleSet('media');
        $subject->setContents([$atRuleSet]);

        $result = $subject->getAllRuleSets();

        self::assertSame([$atRuleSet], $result);
    }

    /**
     * @test
     */
    public function getAllRuleSetsReturnsMultipleDeclarationBlocksDirectlySetAsContents(): void
    {
        $subject = new ConcreteCSSBlockList();

        $declarationBlock1 = new DeclarationBlock();
        $declarationBlock2 = new DeclarationBlock();
        $subject->setContents([$declarationBlock1, $declarationBlock2]);

        $result = $subject->getAllRuleSets();

        self::assertSame([$declarationBlock1, $declarationBlock2], $result);
    }

    /**
     * @test
     */
    public function getAllRuleSetsReturnsMultipleAtRuleSetsDirectlySetAsContents(): void
    {
        $subject = new ConcreteCSSBlockList();

        $atRuleSet1 = new AtRuleSet('media');
        $atRuleSet2 = new AtRuleSet('media');
        $subject->setContents([$atRuleSet1, $atRuleSet2]);

        $result = $subject->getAllRuleSets();

        self::assertSame([$atRuleSet1, $atRuleSet2], $result);
    }

    /**
     * @test
     */
    public function getAllRuleSetsReturnsDeclarationBlocksWithinAtRuleBlockList(): void
    {
        $subject = new ConcreteCSSBlockList();

        $declarationBlock = new DeclarationBlock();
        $atRuleBlockList = new AtRuleBlockList('media');
        $atRuleBlockList->setContents([$declarationBlock]);
        $subject->setContents([$atRuleBlockList]);

        $result = $subject->getAllRuleSets();

        self::assertSame([$declarationBlock], $result);
    }

    /**
     * @test
     */
    public function getAllRuleSetsReturnsAtRuleSetsWithinAtRuleBlockList(): void
    {
        $subject = new ConcreteCSSBlockList();

        $atRule = new AtRuleSet('media');
        $atRuleBlockList = new AtRuleBlockList('media');
        $atRuleBlockList->setContents([$atRule]);
        $subject->setContents([$atRuleBlockList]);

        $result = $subject->getAllRuleSets();

        self::assertSame([$atRule], $result);
    }

    /**
     * @test
     */
    public function getAllRuleSetsIgnoresImport(): void
    {
        $subject = new ConcreteCSSBlockList();

        $import = new Import(new URL(new CSSString('https://www.example.com/')), '');
        $subject->setContents([$import]);

        $result = $subject->getAllRuleSets();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getAllRuleSetsIgnoresCharset(): void
    {
        $subject = new ConcreteCSSBlockList();

        $charset = new Charset(new CSSString('UTF-8'));
        $subject->setContents([$charset]);

        $result = $subject->getAllRuleSets();

        self::assertSame([], $result);
    }
}
