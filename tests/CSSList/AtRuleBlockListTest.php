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
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideMinWidthMediaRule(): array
    {
        return [
            'without spaces around arguments' => ['@media(min-width: 768px){.class{color:red}}'],
            'with spaces around arguments' => ['@media (min-width: 768px) {.class{color:red}}'],
        ];
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
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
            'container' => [
                '@container (min-width: 60rem) { .items { background: blue; } }',
            ],
            'layer named' => [
                '@layer theme { .button { color: blue; } }',
            ],
            'layer anonymous' => [
                '@layer { .card { padding: 1rem; } }',
            ],
            'scope with selector' => [
                '@scope (.card) { .title { font-size: 2rem; } }',
            ],
            'scope root only' => [
                '@scope { .content { margin: 0; } }',
            ],
            'scope with limit' => [
                '@scope (.article-body) to (figure) { h2 { color: red; } }',
            ],
            'starting-style' => [
                '@starting-style { .dialog { opacity: 0; transform: translateY(-10px); } }',
            ],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $css
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
     * @param non-empty-string $css
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
     * @param non-empty-string $css
     *
     * @dataProvider provideMinWidthMediaRule
     * @dataProvider provideSyntacticallyCorrectAtRule
     */
    public function parsesSyntacticallyCorrectAtRuleInStrictMode(string $css): void
    {
        $contents = (new Parser($css, Settings::create()->beStrict()))->parse()->getContents();

        self::assertNotEmpty($contents, 'Failing CSS: `' . $css . '`');
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: non-empty-string, 2: string, 3: int<0, max>}>
     */
    public static function provideAtRuleParsingData(): array
    {
        return [
            'layer with named argument' => [
                '@layer theme { .button { color: blue; } }',
                'layer',
                'theme',
                1,
            ],
            'layer without arguments' => [
                '@layer { .card { padding: 1rem; } }',
                'layer',
                '',
                1,
            ],
            'scope with selector' => [
                '@scope (.card) { .title { font-size: 2rem; } }',
                'scope',
                '(.card)',
                1,
            ],
            'scope without selector' => [
                '@scope { .content { margin: 0; } }',
                'scope',
                '',
                1,
            ],
            'scope with limit' => [
                '@scope (.article-body) to (figure) { h2 { color: red; } }',
                'scope',
                '(.article-body) to (figure)',
                1,
            ],
            'starting-style' => [
                '@starting-style { .dialog { opacity: 0; transform: translateY(-10px); } }',
                'starting-style',
                '',
                1,
            ],
        ];
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: non-empty-list<non-empty-string>}>
     */
    public static function provideAtRuleRenderingData(): array
    {
        return [
            'layer with named argument' => [
                '@layer theme { .button { color: blue; } }',
                ['@layer theme', '.button', 'color: blue'],
            ],
            'scope with selector' => [
                '@scope (.card) { .title { font-size: 2rem; } }',
                ['@scope (.card)', '.title', 'font-size: 2rem'],
            ],
            'scope with limit' => [
                '@scope (.article-body) to (figure) { h2 { color: red; } }',
                ['@scope (.article-body) to (figure)', 'h2', 'color: red'],
            ],
            'starting-style' => [
                '@starting-style { .dialog { opacity: 0; } }',
                ['@starting-style', '.dialog', 'opacity: 0'],
            ],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $css
     * @param non-empty-string $expectedName
     * @param int<0, max> $expectedContentCount
     *
     * @dataProvider provideAtRuleParsingData
     */
    public function parsesAtRuleBlockList(
        string $css,
        string $expectedName,
        string $expectedArgs,
        int $expectedContentCount
    ): void {
        $contents = (new Parser($css))->parse()->getContents();
        $atRuleBlockList = $contents[0];

        self::assertInstanceOf(AtRuleBlockList::class, $atRuleBlockList);
        self::assertSame($expectedName, $atRuleBlockList->atRuleName());
        self::assertSame($expectedArgs, $atRuleBlockList->atRuleArgs());
        self::assertCount($expectedContentCount, $atRuleBlockList->getContents());
    }

    /**
     * @test
     *
     * @dataProvider provideAtRuleRenderingData
     *
     * @param non-empty-string $css
     * @param non-empty-list<non-empty-string> $expectedSubstrings
     */
    public function rendersAtRuleBlockListCorrectly(string $css, array $expectedSubstrings): void
    {
        $rendered = (new Parser($css))->parse()->render();

        foreach ($expectedSubstrings as $expected) {
            self::assertStringContainsString($expected, $rendered);
        }
    }
}
