<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\RuleSet\DeclarationBlock;

/**
 * @covers \Sabberworm\CSS\CSSList\Document
 */
final class DocumentTest extends TestCase
{
    /**
     * @var Document
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new Document();
    }

    /**
     * @test
     */
    public function implementsRenderable(): void
    {
        self::assertInstanceOf(Renderable::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsCommentable(): void
    {
        self::assertInstanceOf(Commentable::class, $this->subject);
    }

    /**
     * @test
     */
    public function getContentsInitiallyReturnsEmptyArray(): void
    {
        self::assertSame([], $this->subject->getContents());
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
        $this->subject->setContents($contents);

        self::assertSame($contents, $this->subject->getContents());
    }

    /**
     * @test
     */
    public function setContentsReplacesContentsSetInPreviousCall(): void
    {
        $contents2 = [new DeclarationBlock()];

        $this->subject->setContents([new DeclarationBlock()]);
        $this->subject->setContents($contents2);

        self::assertSame($contents2, $this->subject->getContents());
    }

    /**
     * @test
     */
    public function insertContentBeforeInsertsContentBeforeSibling(): void
    {
        $bogusOne = new DeclarationBlock();
        $bogusOne->setSelectors('.bogus-one');
        $bogusTwo = new DeclarationBlock();
        $bogusTwo->setSelectors('.bogus-two');

        $item = new DeclarationBlock();
        $item->setSelectors('.item');

        $sibling = new DeclarationBlock();
        $sibling->setSelectors('.sibling');

        $this->subject->setContents([$bogusOne, $sibling, $bogusTwo]);

        self::assertCount(3, $this->subject->getContents());

        $this->subject->insertBefore($item, $sibling);

        self::assertCount(4, $this->subject->getContents());
        self::assertSame([$bogusOne, $item, $sibling, $bogusTwo], $this->subject->getContents());
    }

    /**
     * @test
     */
    public function insertContentBeforeAppendsIfSiblingNotFound(): void
    {
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

        $this->subject->setContents([$bogusOne, $sibling, $bogusTwo]);

        self::assertCount(3, $this->subject->getContents());

        $this->subject->insertBefore($item, $orphan);

        self::assertCount(4, $this->subject->getContents());
        self::assertSame([$bogusOne, $sibling, $bogusTwo, $item], $this->subject->getContents());
    }
}
