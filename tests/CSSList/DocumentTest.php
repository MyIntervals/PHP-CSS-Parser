<?php

namespace Sabberworm\CSS\Tests\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\RuleSet\DeclarationBlock;

/**
 * @covers \Sabberworm\CSS\CSSList\Document
 */
class DocumentTest extends TestCase
{
    /**
     * @var Document
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new Document();
    }

    /**
     * @test
     */
    public function getContentsInitiallyReturnsEmptyArray()
    {
        self::assertSame([], $this->subject->getContents());
    }

    /**
     * @return array<string, array<int, array<int, DeclarationBlock>>>
     */
    public function contentsDataProvider()
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
    public function setContentsSetsContents(array $contents)
    {
        $this->subject->setContents($contents);

        self::assertSame($contents, $this->subject->getContents());
    }

    /**
     * @test
     */
    public function setContentsReplacesContentsSetInPreviousCall()
    {
        $contents2 = [new DeclarationBlock()];

        $this->subject->setContents([new DeclarationBlock()]);
        $this->subject->setContents($contents2);

        self::assertSame($contents2, $this->subject->getContents());
    }
}
