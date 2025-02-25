<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\CSSList\CSSBlockList;
use Sabberworm\CSS\CSSList\CSSList;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\Property\Charset;
use Sabberworm\CSS\Property\Import;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\RuleSet\AtRuleSet;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\Value\CSSString;
use Sabberworm\CSS\Value\URL;

/**
 * @covers \Sabberworm\CSS\CSSList\CSSBlockList
 * @covers \Sabberworm\CSS\CSSList\CSSList
 * @covers \Sabberworm\CSS\CSSList\Document
 */
final class DocumentTest extends TestCase
{
    /**
     * @test
     */
    public function implementsRenderable(): void
    {
        self::assertInstanceOf(Renderable::class, new Document());
    }

    /**
     * @test
     */
    public function implementsCommentable(): void
    {
        self::assertInstanceOf(Commentable::class, new Document());
    }

    /**
     * @test
     */
    public function isCSSBlockList(): void
    {
        $subject = new Document();

        self::assertInstanceOf(CSSBlockList::class, $subject);
    }

    /**
     * @test
     */
    public function isCSSList(): void
    {
        $subject = new Document();

        self::assertInstanceOf(CSSList::class, $subject);
    }

    /**
     * @test
     */
    public function getAllDeclarationBlocksForNoContentsReturnsEmptyArray(): void
    {
        $subject = new Document();

        self::assertSame([], $subject->getAllDeclarationBlocks());
    }

    /**
     * @test
     */
    public function getAllDeclarationBlocksCanReturnOneDirectDeclarationBlockContent(): void
    {
        $subject = new Document();

        $declarationBlock = new DeclarationBlock();
        $subject->setContents([$declarationBlock]);

        $result = $subject->getAllDeclarationBlocks();

        self::assertSame([$declarationBlock], $result);
    }

    /**
     * @test
     */
    public function getAllDeclarationBlocksCanReturnMultipleDirectDeclarationBlockContents(): void
    {
        $subject = new Document();

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
        $subject = new Document();

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
        $subject = new Document();

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
        $subject = new Document();

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
        $subject = new Document();

        self::assertSame([], $subject->getAllRuleSets());
    }

    /**
     * @test
     */
    public function getAllRuleSetsReturnsOneDeclarationBlockDirectlySetAsContent(): void
    {
        $subject = new Document();

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
        $subject = new Document();

        $atRuleSet = new AtRuleSet('media');
        $subject->setContents([$atRuleSet]);

        $result = $subject->getAllRuleSets();

        self::assertSame([$atRuleSet], $result);
    }

    /**
     * @test
     */
    public function getAllRuleSetsCanReturnMultipleDirectDeclarationBlockContents(): void
    {
        $subject = new Document();

        $declarationBlock1 = new DeclarationBlock();
        $declarationBlock2 = new DeclarationBlock();
        $subject->setContents([$declarationBlock1, $declarationBlock2]);

        $result = $subject->getAllRuleSets();

        self::assertSame([$declarationBlock1, $declarationBlock2], $result);
    }

    /**
     * @test
     */
    public function getAllRuleSetsCanReturnMultipleDirectAtRuleSetContents(): void
    {
        $subject = new Document();

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
        $subject = new Document();

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
        $subject = new Document();

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
        $subject = new Document();

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
        $subject = new Document();

        $charset = new Charset(new CSSString('UTF-8'));
        $subject->setContents([$charset]);

        $result = $subject->getAllRuleSets();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function isRootListAlwaysReturnsTrue(): void
    {
        $subject = new Document();

        self::assertTrue($subject->isRootList());
    }
}
