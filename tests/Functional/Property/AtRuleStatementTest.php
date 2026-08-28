<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\Property;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Property\AtRuleStatement;
use Sabberworm\CSS\RuleSet\AtRuleSet;
use Sabberworm\CSS\Settings;

/**
 * @covers \Sabberworm\CSS\CSSList\CSSList
 * @covers \Sabberworm\CSS\Property\AtRuleStatement
 */
final class AtRuleStatementTest extends TestCase
{
    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: non-empty-string, 2: string}>
     */
    public static function provideAtRuleStatementParsingData(): array
    {
        return [
            'layer with single name' => [
                '@layer reset;',
                'layer',
                'reset',
            ],
            'layer with multiple names' => [
                '@layer base, components;',
                'layer',
                'base, components',
            ],
            'custom statement at-rule' => [
                '@custom foo;',
                'custom',
                'foo',
            ],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $css
     * @param non-empty-string $expectedName
     *
     * @dataProvider provideAtRuleStatementParsingData
     */
    public function parsesAtRuleStatement(string $css, string $expectedName, string $expectedArgs): void
    {
        $contents = (new Parser($css))->parse()->getContents();
        $atRuleStatement = $contents[0];

        self::assertInstanceOf(AtRuleStatement::class, $atRuleStatement);
        self::assertSame($expectedName, $atRuleStatement->atRuleName());
        self::assertSame($expectedArgs, $atRuleStatement->atRuleArgs());
    }

    /**
     * @test
     *
     * @param non-empty-string $css
     *
     * @dataProvider provideAtRuleStatementParsingData
     */
    public function parsesAtRuleStatementInStrictMode(string $css): void
    {
        $contents = (new Parser($css, Settings::create()->beStrict()))->parse()->getContents();

        self::assertNotEmpty($contents, 'Failing CSS: `' . $css . '`');
        self::assertInstanceOf(AtRuleStatement::class, $contents[0]);
    }

    /**
     * @test
     */
    public function doesNotConsumeFollowingRulesWhenParsingLayerStatements(): void
    {
        $css = "@layer reset;\n"
            . "@layer base, components;\n"
            . "@property --tw-scale-x {\n"
            . "  syntax: \"*\";\n"
            . "  inherits: false;\n"
            . "  initial-value: 1;\n"
            . "}\n"
            . "@property --tw-scale-y {\n"
            . "  syntax: \"*\";\n"
            . "  inherits: false;\n"
            . "  initial-value: 1;\n"
            . "}\n";

        $contents = (new Parser($css))->parse()->getContents();

        self::assertCount(4, $contents);
        self::assertInstanceOf(AtRuleStatement::class, $contents[0]);
        self::assertSame('reset', $contents[0]->atRuleArgs());
        self::assertInstanceOf(AtRuleStatement::class, $contents[1]);
        self::assertSame('base, components', $contents[1]->atRuleArgs());
        self::assertInstanceOf(AtRuleSet::class, $contents[2]);
        self::assertSame('property', $contents[2]->atRuleName());
        self::assertInstanceOf(AtRuleSet::class, $contents[3]);
        self::assertSame('property', $contents[3]->atRuleName());
    }

    /**
     * @test
     */
    public function parsesLayerStatementThenLayerBlockInTheSameDocument(): void
    {
        $css = '@layer reset; @layer theme { .button { color: blue; } }';

        $contents = (new Parser($css))->parse()->getContents();

        self::assertCount(2, $contents);
        self::assertInstanceOf(AtRuleStatement::class, $contents[0]);
        self::assertSame('reset', $contents[0]->atRuleArgs());
        self::assertInstanceOf(AtRuleBlockList::class, $contents[1]);
        self::assertSame('theme', $contents[1]->atRuleArgs());
    }

    /**
     * @test
     */
    public function rendersParsedLayerStatementWithTrailingSemicolon(): void
    {
        $rendered = (new Parser('@layer reset;'))->parse()->render();

        self::assertStringContainsString('@layer reset;', $rendered);
    }
}
