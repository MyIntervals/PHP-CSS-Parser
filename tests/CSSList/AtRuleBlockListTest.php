<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Settings;

/**
 * @covers \Sabberworm\CSS\CSSList\AtRuleBlockList
 * @covers \Sabberworm\CSS\CSSList\CSSBlockList
 * @covers \Sabberworm\CSS\CSSList\CSSList
 */
final class AtRuleBlockListTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function provideMinWidthMediaRule(): array
    {
        return [
            'without spaces around arguments' => ['@media(min-width: 768px){.class{color:red}}'],
            'with spaces around arguments' => ['@media (min-width: 768px) {.class{color:red}}'],
        ];
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function provideSyntacticallyCorrectAtRule(): array
    {
        return [
            'media print' => ['@media print { html { background: white; color: black; } }'],
            'keyframes' => ['@keyframes mymove { from { top: 0px; } }'],
            'supports' => [
                '
                    @supports (display: flex) {
                        .flex-container > * {
                            text-shadow: 0 0 2px blue;
                            float: none;
                        }
                        .flex-container {
                            display: flex;
                        }
                    }
                ',
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideMinWidthMediaRule
     */
    public function parsesRuleNameOfMediaQueries(string $css): void
    {
        $contents = (new Parser($css))->parse()->getContents();
        $atRuleBlockList = $contents[0];

        self::assertInstanceOf(AtRuleBlockList::class, $atRuleBlockList);
        self::assertSame('media', $atRuleBlockList->atRuleName());
    }

    /**
     * @test
     *
     * @dataProvider provideMinWidthMediaRule
     */
    public function parsesArgumentsOfMediaQueries(string $css): void
    {
        $contents = (new Parser($css))->parse()->getContents();
        $atRuleBlockList = $contents[0];

        self::assertInstanceOf(AtRuleBlockList::class, $atRuleBlockList);
        self::assertSame('(min-width: 768px)', $atRuleBlockList->atRuleArgs());
    }

    /**
     * @test
     *
     * @dataProvider provideMinWidthMediaRule
     * @dataProvider provideSyntacticallyCorrectAtRule
     */
    public function parsesSyntacticallyCorrectAtRuleInStrictMode(string $css): void
    {
        $contents = (new Parser($css, Settings::create()->beStrict()))->parse()->getContents();

        self::assertNotEmpty($contents, 'Failing CSS: `' . $css . '`');
    }
}
