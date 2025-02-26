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
    public function getAllDeclarationBlocksForNoContentsReturnsEmptyArray(): void
    {
        $subject = new ConcreteCSSBlockList();

        self::assertSame([], $subject->getAllDeclarationBlocks());
    }

    /**
     * @test
     */
    public function getAllDeclarationBlocksCanReturnOneDirectDeclarationBlockContent(): void
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
    public function getAllDeclarationBlocksCanReturnMultipleDirectDeclarationBlockContents(): void
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
}
