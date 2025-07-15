<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\Tests\Unit\CSSList\Fixtures\ConcreteCSSList;

/**
 * @covers \Sabberworm\CSS\CSSList\CSSList
 */
final class CSSListTest extends TestCase
{
    /**
     * @test
     */
    public function implementsCSSElement(): void
    {
        $subject = new ConcreteCSSList();

        self::assertInstanceOf(CSSElement::class, $subject);
    }

    /**
     * @test
     */
    public function implementsRenderable(): void
    {
        $subject = new ConcreteCSSList();

        self::assertInstanceOf(Renderable::class, $subject);
    }

    /**
     * @test
     */
    public function implementsCommentable(): void
    {
        $subject = new ConcreteCSSList();

        self::assertInstanceOf(Commentable::class, $subject);
    }

    /**
     * @test
     */
    public function implementsCSSListItem(): void
    {
        $subject = new ConcreteCSSList();

        self::assertInstanceOf(CSSListItem::class, $subject);
    }

    /**
     * @test
     */
    public function getLineNumberByDefaultReturnsNull(): void
    {
        $subject = new ConcreteCSSList();

        self::assertNull($subject->getLineNumber());
    }

    /**
     * @test
     */
    public function getLineNumberReturnsLineNumberProvidedToConstructor(): void
    {
        $lineNumber = 42;
        $subject = new ConcreteCSSList($lineNumber);

        self::assertSame($lineNumber, $subject->getLineNumber());
    }

    /**
     * @test
     */
    public function getContentsInitiallyReturnsEmptyArray(): void
    {
        $subject = new ConcreteCSSList();

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
        $subject = new ConcreteCSSList();

        $subject->setContents($contents);

        self::assertSame($contents, $subject->getContents());
    }

    /**
     * @test
     */
    public function setContentsReplacesContentsSetInPreviousCall(): void
    {
        $subject = new ConcreteCSSList();

        $contents2 = [new DeclarationBlock()];

        $subject->setContents([new DeclarationBlock()]);
        $subject->setContents($contents2);

        self::assertSame($contents2, $subject->getContents());
    }

    /**
     * @test
     */
    public function insertBeforeInsertsContentBeforeSibling(): void
    {
        $subject = new ConcreteCSSList();

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
    public function insertBeforeAppendsIfSiblingNotFound(): void
    {
        $subject = new ConcreteCSSList();

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
    public function removeDeclarationBlockBySelectorRemovesDeclarationBlockProvided(): void
    {
        $subject = new ConcreteCSSList();
        $declarationBlock = new DeclarationBlock();
        $declarationBlock->setSelectors(['html', 'body']);
        $subject->setContents([$declarationBlock]);
        self::assertNotSame([], $subject->getContents()); // make sure contents are set

        $subject->removeDeclarationBlockBySelector($declarationBlock);

        self::assertSame([], $subject->getContents());
    }

    /**
     * @test
     */
    public function removeDeclarationBlockBySelectorRemovesDeclarationBlockWithSelectorsProvidedFromItself(): void
    {
        $subject = new ConcreteCSSList();
        $declarationBlock = new DeclarationBlock();
        $declarationBlock->setSelectors(['html', 'body']);
        $subject->setContents([$declarationBlock]);
        self::assertNotSame([], $subject->getContents()); // make sure contents are set

        $subject->removeDeclarationBlockBySelector($declarationBlock->getSelectors());

        self::assertSame([], $subject->getContents());
    }

    /**
     * @test
     */
    public function removeDeclarationBlockBySelectorRemovesDeclarationBlockWithOutsourcedSelectorsProvided(): void
    {
        $subject = new ConcreteCSSList();
        $declarationBlock = new DeclarationBlock();
        $declarationBlock->setSelectors(['html', 'body']);
        $subject->setContents([$declarationBlock]);
        self::assertNotSame([], $subject->getContents()); // make sure contents are set

        $subject->removeDeclarationBlockBySelector([new Selector('html'), new Selector('body')]);

        self::assertSame([], $subject->getContents());
    }

    /**
     * @test
     */
    public function removeDeclarationBlockBySelectorRemovesDeclarationBlockWithStringSelectorsProvided(): void
    {
        $subject = new ConcreteCSSList();
        $declarationBlock = new DeclarationBlock();
        $declarationBlock->setSelectors(['html', 'body']);
        $subject->setContents([$declarationBlock]);
        self::assertNotSame([], $subject->getContents()); // make sure contents are set

        $subject->removeDeclarationBlockBySelector(['html', 'body']);

        self::assertSame([], $subject->getContents());
    }

    /**
     * @test
     */
    public function removeDeclarationBlockBySelectorRemovesDeclarationBlockProvidedAndAnotherWithSameSelectors(): void
    {
        $subject = new ConcreteCSSList();
        $declarationBlock1 = new DeclarationBlock();
        $declarationBlock1->setSelectors(['html', 'body']);
        $declarationBlock2 = new DeclarationBlock();
        $declarationBlock2->setSelectors(['html', 'body']);
        $subject->setContents([$declarationBlock1, $declarationBlock2]);
        self::assertNotSame([], $subject->getContents()); // make sure contents are set

        $subject->removeDeclarationBlockBySelector($declarationBlock1, true);

        self::assertSame([], $subject->getContents());
    }

    /**
     * @test
     */
    public function removeDeclarationBlockBySelectorRemovesBlockWithSelectorsFromItselfAndAnotherMatching(): void
    {
        $subject = new ConcreteCSSList();
        $declarationBlock1 = new DeclarationBlock();
        $declarationBlock1->setSelectors(['html', 'body']);
        $declarationBlock2 = new DeclarationBlock();
        $declarationBlock2->setSelectors(['html', 'body']);
        $subject->setContents([$declarationBlock1, $declarationBlock2]);
        self::assertNotSame([], $subject->getContents()); // make sure contents are set

        $subject->removeDeclarationBlockBySelector($declarationBlock1->getSelectors(), true);

        self::assertSame([], $subject->getContents());
    }

    /**
     * @test
     */
    public function removeDeclarationBlockBySelectorRemovesMultipleBlocksWithOutsourcedSelectors(): void
    {
        $subject = new ConcreteCSSList();
        $declarationBlock1 = new DeclarationBlock();
        $declarationBlock1->setSelectors(['html', 'body']);
        $declarationBlock2 = new DeclarationBlock();
        $declarationBlock2->setSelectors(['html', 'body']);
        $subject->setContents([$declarationBlock1, $declarationBlock2]);
        self::assertNotSame([], $subject->getContents()); // make sure contents are set

        $subject->removeDeclarationBlockBySelector([new Selector('html'), new Selector('body')], true);

        self::assertSame([], $subject->getContents());
    }

    /**
     * @test
     */
    public function removeDeclarationBlockBySelectorRemovesMultipleBlocksWithStringSelectorsProvided(): void
    {
        $subject = new ConcreteCSSList();
        $declarationBlock1 = new DeclarationBlock();
        $declarationBlock1->setSelectors(['html', 'body']);
        $declarationBlock2 = new DeclarationBlock();
        $declarationBlock2->setSelectors(['html', 'body']);
        $subject->setContents([$declarationBlock1, $declarationBlock2]);
        self::assertNotSame([], $subject->getContents()); // make sure contents are set

        $subject->removeDeclarationBlockBySelector(['html', 'body'], true);

        self::assertSame([], $subject->getContents());
    }
}
