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
            'starting-style' => [
                '@starting-style { .dialog { opacity: 0; transform: translateY(-10px); } }',
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

    /**
     * @test
     */
    public function parsesLayerWithNamedArgument(): void
    {
        $css = '@layer theme { .button { color: blue; } }';
        $contents = (new Parser($css))->parse()->getContents();
        $atRuleBlockList = $contents[0];

        self::assertInstanceOf(AtRuleBlockList::class, $atRuleBlockList);
        self::assertSame('layer', $atRuleBlockList->atRuleName());
        self::assertSame('theme', $atRuleBlockList->atRuleArgs());

        $nestedContents = $atRuleBlockList->getContents();
        self::assertCount(1, $nestedContents, 'Layer should contain one declaration block');
    }

    /**
     * @test
     */
    public function parsesLayerWithoutArguments(): void
    {
        $css = '@layer { .card { padding: 1rem; } }';
        $contents = (new Parser($css))->parse()->getContents();
        $atRuleBlockList = $contents[0];

        self::assertInstanceOf(AtRuleBlockList::class, $atRuleBlockList);
        self::assertSame('layer', $atRuleBlockList->atRuleName());
        self::assertSame('', $atRuleBlockList->atRuleArgs());

        $nestedContents = $atRuleBlockList->getContents();
        self::assertCount(1, $nestedContents, 'Layer should contain one declaration block');
    }

    /**
     * @test
     */
    public function parsesScopeWithSelector(): void
    {
        $css = '@scope (.card) { .title { font-size: 2rem; } }';
        $contents = (new Parser($css))->parse()->getContents();
        $atRuleBlockList = $contents[0];

        self::assertInstanceOf(AtRuleBlockList::class, $atRuleBlockList);
        self::assertSame('scope', $atRuleBlockList->atRuleName());
        self::assertSame('(.card)', $atRuleBlockList->atRuleArgs());

        $nestedContents = $atRuleBlockList->getContents();
        self::assertCount(1, $nestedContents, 'Scope should contain one declaration block');
    }

    /**
     * @test
     */
    public function parsesScopeWithoutSelector(): void
    {
        $css = '@scope { .content { margin: 0; } }';
        $contents = (new Parser($css))->parse()->getContents();
        $atRuleBlockList = $contents[0];

        self::assertInstanceOf(AtRuleBlockList::class, $atRuleBlockList);
        self::assertSame('scope', $atRuleBlockList->atRuleName());
        self::assertSame('', $atRuleBlockList->atRuleArgs());

        $nestedContents = $atRuleBlockList->getContents();
        self::assertCount(1, $nestedContents, 'Scope should contain one declaration block');
    }

    /**
     * @test
     */
    public function parsesStartingStyle(): void
    {
        $css = '@starting-style { .dialog { opacity: 0; transform: translateY(-10px); } }';
        $contents = (new Parser($css))->parse()->getContents();
        $atRuleBlockList = $contents[0];

        self::assertInstanceOf(AtRuleBlockList::class, $atRuleBlockList);
        self::assertSame('starting-style', $atRuleBlockList->atRuleName());
        self::assertSame('', $atRuleBlockList->atRuleArgs());

        $nestedContents = $atRuleBlockList->getContents();
        self::assertCount(1, $nestedContents, 'Starting-style should contain one declaration block');
    }

    /**
     * @test
     */
    public function rendersLayerCorrectly(): void
    {
        $css = '@layer theme { .button { color: blue; } }';
        $document = (new Parser($css))->parse();
        $rendered = $document->render();

        self::assertStringContainsString('@layer theme', $rendered);
        self::assertStringContainsString('.button', $rendered);
        self::assertStringContainsString('color: blue', $rendered);
    }

    /**
     * @test
     */
    public function rendersScopeCorrectly(): void
    {
        $css = '@scope (.card) { .title { font-size: 2rem; } }';
        $document = (new Parser($css))->parse();
        $rendered = $document->render();

        self::assertStringContainsString('@scope (.card)', $rendered);
        self::assertStringContainsString('.title', $rendered);
        self::assertStringContainsString('font-size: 2rem', $rendered);
    }

    /**
     * @test
     */
    public function rendersStartingStyleCorrectly(): void
    {
        $css = '@starting-style { .dialog { opacity: 0; } }';
        $document = (new Parser($css))->parse();
        $rendered = $document->render();

        self::assertStringContainsString('@starting-style', $rendered);
        self::assertStringContainsString('.dialog', $rendered);
        self::assertStringContainsString('opacity: 0', $rendered);
    }
}
