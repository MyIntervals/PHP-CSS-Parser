<?php

namespace Sabberworm\CSS\Tests\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Renderable;

/**
 * @covers \Sabberworm\CSS\CSSList\AtRuleBlockList
 */
class AtRuleBlockListTest extends TestCase
{
    /**
     * @test
     */
    public function implementsAtRule()
    {
        $subject = new AtRuleBlockList('');

        self::assertInstanceOf(AtRuleBlockList::class, $subject);
    }

    /**
     * @test
     */
    public function implementsRenderable()
    {
        $subject = new AtRuleBlockList('');

        self::assertInstanceOf(Renderable::class, $subject);
    }

    /**
     * @test
     */
    public function implementsCommentable()
    {
        $subject = new AtRuleBlockList('');

        self::assertInstanceOf(Commentable::class, $subject);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function mediaRuleDataProvider()
    {
        return [
            'without spaces around arguments' => ['@media(min-width: 768px){.class{color:red}}'],
            'with spaces around arguments' => ['@media (min-width: 768px) {.class{color:red}}'],
        ];
    }

    /**
     * @test
     *
     * @param string $css
     *
     * @dataProvider mediaRuleDataProvider
     */
    public function parsesRuleNameOfMediaQueries($css)
    {
        $contents = (new Parser($css))->parse()->getContents();
        $atRuleBlockList = $contents[0];

        self::assertSame('media', $atRuleBlockList->atRuleName());
    }

    /**
     * @test
     *
     * @param string $css
     *
     * @dataProvider mediaRuleDataProvider
     */
    public function parsesArgumentsOfMediaQueries($css)
    {
        $contents = (new Parser($css))->parse()->getContents();
        $atRuleBlockList = $contents[0];

        self::assertSame('(min-width: 768px)', $atRuleBlockList->atRuleArgs());
    }
}
