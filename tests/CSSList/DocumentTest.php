<?php

namespace Sabberworm\CSS\Tests\CSSList;

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
     * @return array<string, array<int, array<int, DeclarationBlock>>>
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
     * @param array<int, DeclarationBlock> $contents
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
     * @return array<string, array<string, DeclarationBlock|array<int, DeclarationBlock>>>
     */
    public static function insertContentBeforeInsertsContentBeforeSibblingOrAppendsIfSibblingNotFoundDataProvider(): array
    {

        $bogusOne = new DeclarationBlock();
        $bogusOne->setSelectors('.bogus-one');
        $bogusTwo = new DeclarationBlock();
        $bogusTwo->setSelectors('.bogus-two');

        $oItem = new DeclarationBlock();
        $oItem->setSelectors('.item');

        $oSibling = new DeclarationBlock();
        $oSibling->setSelectors('.sibling');

        $oOrphan = new DeclarationBlock();
        $oOrphan->setSelectors('.forever-alone');

        return [
            'insert before' => [
                'initialContent' => [$bogusOne, $oSibling, $bogusTwo],
                'oItem' => $oItem,
                'oSibling' => $oSibling,
                'expectedContent' => [$bogusOne, $oItem, $oSibling, $bogusTwo],
            ],
            'append if not found' => [
                'initialContent' => [$bogusOne, $oSibling, $bogusTwo],
                'oItem' => $oItem,
                'oSibling' => $oOrphan,
                'expectedContent' => [$bogusOne, $oSibling, $bogusTwo, $oItem],
            ]
        ];
    }

    /**
     * @test
     *
     * @dataProvider insertBeforeDataProvider
     */
    public function insertContentBeforeInsertsContentBeforeSibblingOrAppendsIfSibblingNotFound(
        array $initialContent,
        DeclarationBlock $oItem,
        DeclarationBlock $oSibling,
        array $expectedContent
    ) {

        $this->subject->setContents($initialContent);

        self::assertCount(3, $this->subject->getContents());

        $this->subject->insertBefore($oItem, $oSibling);

        self::assertCount(4, $this->subject->getContents());
        self::assertSame($expectedContent, $this->subject->getContents());
    }
}
