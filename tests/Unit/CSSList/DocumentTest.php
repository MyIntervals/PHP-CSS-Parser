<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\Property\Charset;
use Sabberworm\CSS\Property\Import;
use Sabberworm\CSS\Renderable;
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
    public function getContentsInitiallyReturnsEmptyArray(): void
    {
        $subject = new Document();

        self::assertSame([], $subject->getContents());
    }

    /**
     * @return array<string, array{0: list<DeclarationBlock>}>
     */
    public static function contentsDataProvider(): array
    {
        return [
            'empty array' => [[]],
            '1 item' => [[new DeclarationBlock()]],
            '2 items' => [[new DeclarationBlock(), new DeclarationBlock()]],
        ];
    }

    /**
     * @test
     *
     * @param list<DeclarationBlock> $contents
     *
     * @dataProvider contentsDataProvider
     */
    public function setContentsSetsContents(array $contents): void
    {
        $subject = new Document();

        $subject->setContents($contents);

        self::assertSame($contents, $subject->getContents());
    }

    /**
     * @test
     */
    public function setContentsReplacesContentsSetInPreviousCall(): void
    {
        $subject = new Document();

        $contents2 = [new DeclarationBlock()];

        $subject->setContents([new DeclarationBlock()]);
        $subject->setContents($contents2);

        self::assertSame($contents2, $subject->getContents());
    }

    /**
     * @test
     */
    public function insertContentBeforeInsertsContentBeforeSibling(): void
    {
        $subject = new Document();

        $bogusOne = new DeclarationBlock();
        $bogusOne->setSelectors('.bogus-one');
        $bogusTwo = new DeclarationBlock();
        $bogusTwo->setSelectors('.bogus-two');

        $item = new DeclarationBlock();
        $item->setSelectors('.item');

        $sibling = new DeclarationBlock();
        $sibling->setSelectors('.sibling');

        $subject->setContents([$bogusOne, $sibling, $bogusTwo]);

        self::assertCount(3, $subject->getContents());

        $subject->insertBefore($item, $sibling);

        self::assertCount(4, $subject->getContents());
        self::assertSame([$bogusOne, $item, $sibling, $bogusTwo], $subject->getContents());
    }

    /**
     * @test
     */
    public function insertContentBeforeAppendsIfSiblingNotFound(): void
    {
        $subject = new Document();

        $bogusOne = new DeclarationBlock();
        $bogusOne->setSelectors('.bogus-one');
        $bogusTwo = new DeclarationBlock();
        $bogusTwo->setSelectors('.bogus-two');

        $item = new DeclarationBlock();
        $item->setSelectors('.item');

        $sibling = new DeclarationBlock();
        $sibling->setSelectors('.sibling');

        $orphan = new DeclarationBlock();
        $orphan->setSelectors('.forever-alone');

        $subject->setContents([$bogusOne, $sibling, $bogusTwo]);

        self::assertCount(3, $subject->getContents());

        $subject->insertBefore($item, $orphan);

        self::assertCount(4, $subject->getContents());
        self::assertSame([$bogusOne, $sibling, $bogusTwo, $item], $subject->getContents());
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
}
