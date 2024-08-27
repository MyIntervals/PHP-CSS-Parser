<?php

namespace Sabberworm\CSS\Tests\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\Value\Size;

/**
 * @covers \Sabberworm\CSS\RuleSet\DeclarationBlock
 */
final class DeclarationBlockTest extends TestCase
{
    /**
     * @dataProvider expandBorderShorthandProvider
     *
     * @test
     */
    public function expandBorderShorthand(string $sCss, string $sExpected): void
    {
        $parser = new Parser($sCss);
        $document = $parser->parse();
        foreach ($document->getAllDeclarationBlocks() as $declarationBlock) {
            $declarationBlock->expandBorderShorthand();
        }
        self::assertSame(\trim((string) $document), $sExpected);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function expandBorderShorthandProvider(): array
    {
        return [
            ['body{ border: 2px solid #000 }', 'body {border-width: 2px;border-style: solid;border-color: #000;}'],
            ['body{ border: none }', 'body {border-style: none;}'],
            ['body{ border: 2px }', 'body {border-width: 2px;}'],
            ['body{ border: #f00 }', 'body {border-color: #f00;}'],
            ['body{ border: 1em solid }', 'body {border-width: 1em;border-style: solid;}'],
            ['body{ margin: 1em; }', 'body {margin: 1em;}'],
        ];
    }

    /**
     * @dataProvider expandFontShorthandProvider
     *
     * @test
     */
    public function expandFontShorthand(string $sCss, string $sExpected): void
    {
        $parser = new Parser($sCss);
        $document = $parser->parse();
        foreach ($document->getAllDeclarationBlocks() as $declarationBlock) {
            $declarationBlock->expandFontShorthand();
        }
        self::assertSame(\trim((string) $document), $sExpected);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function expandFontShorthandProvider(): array
    {
        return [
            [
                'body{ margin: 1em; }',
                'body {margin: 1em;}',
            ],
            [
                'body {font: 12px serif;}',
                'body {font-style: normal;font-variant: normal;font-weight: normal;font-size: 12px;'
                . 'line-height: normal;font-family: serif;}',
            ],
            [
                'body {font: italic 12px serif;}',
                'body {font-style: italic;font-variant: normal;font-weight: normal;font-size: 12px;'
                . 'line-height: normal;font-family: serif;}',
            ],
            [
                'body {font: italic bold 12px serif;}',
                'body {font-style: italic;font-variant: normal;font-weight: bold;font-size: 12px;'
                . 'line-height: normal;font-family: serif;}',
            ],
            [
                'body {font: italic bold 12px/1.6 serif;}',
                'body {font-style: italic;font-variant: normal;font-weight: bold;font-size: 12px;'
                . 'line-height: 1.6;font-family: serif;}',
            ],
            [
                'body {font: italic small-caps bold 12px/1.6 serif;}',
                'body {font-style: italic;font-variant: small-caps;font-weight: bold;font-size: 12px;'
                . 'line-height: 1.6;font-family: serif;}',
            ],
        ];
    }

    /**
     * @dataProvider expandBackgroundShorthandProvider
     *
     * @test
     */
    public function expandBackgroundShorthand(string $sCss, string $sExpected): void
    {
        $parser = new Parser($sCss);
        $document = $parser->parse();
        foreach ($document->getAllDeclarationBlocks() as $declarationBlock) {
            $declarationBlock->expandBackgroundShorthand();
        }
        self::assertSame(\trim((string) $document), $sExpected);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function expandBackgroundShorthandProvider(): array
    {
        return [
            ['body {border: 1px;}', 'body {border: 1px;}'],
            [
                'body {background: #f00;}',
                'body {background-color: #f00;background-image: none;background-repeat: repeat;'
                . 'background-attachment: scroll;background-position: 0% 0%;}',
            ],
            [
                'body {background: #f00 url("foobar.png");}',
                'body {background-color: #f00;background-image: url("foobar.png");background-repeat: repeat;'
                . 'background-attachment: scroll;background-position: 0% 0%;}',
            ],
            [
                'body {background: #f00 url("foobar.png") no-repeat;}',
                'body {background-color: #f00;background-image: url("foobar.png");background-repeat: no-repeat;'
                . 'background-attachment: scroll;background-position: 0% 0%;}',
            ],
            [
                'body {background: #f00 url("foobar.png") no-repeat center;}',
                'body {background-color: #f00;background-image: url("foobar.png");background-repeat: no-repeat;'
                . 'background-attachment: scroll;background-position: center center;}',
            ],
            [
                'body {background: #f00 url("foobar.png") no-repeat top left;}',
                'body {background-color: #f00;background-image: url("foobar.png");background-repeat: no-repeat;'
                . 'background-attachment: scroll;background-position: top left;}',
            ],
        ];
    }

    /**
     * @dataProvider expandDimensionsShorthandProvider
     *
     * @test
     */
    public function expandDimensionsShorthand(string $sCss, string $sExpected): void
    {
        $parser = new Parser($sCss);
        $document = $parser->parse();
        foreach ($document->getAllDeclarationBlocks() as $declarationBlock) {
            $declarationBlock->expandDimensionsShorthand();
        }
        self::assertSame(\trim((string) $document), $sExpected);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function expandDimensionsShorthandProvider(): array
    {
        return [
            ['body {border: 1px;}', 'body {border: 1px;}'],
            ['body {margin-top: 1px;}', 'body {margin-top: 1px;}'],
            ['body {margin: 1em;}', 'body {margin-top: 1em;margin-right: 1em;margin-bottom: 1em;margin-left: 1em;}'],
            [
                'body {margin: 1em 2em;}',
                'body {margin-top: 1em;margin-right: 2em;margin-bottom: 1em;margin-left: 2em;}',
            ],
            [
                'body {margin: 1em 2em 3em;}',
                'body {margin-top: 1em;margin-right: 2em;margin-bottom: 3em;margin-left: 2em;}',
            ],
        ];
    }

    /**
     * @dataProvider createBorderShorthandProvider
     *
     * @test
     */
    public function createBorderShorthand(string $sCss, string $sExpected): void
    {
        $parser = new Parser($sCss);
        $document = $parser->parse();
        foreach ($document->getAllDeclarationBlocks() as $declarationBlock) {
            $declarationBlock->createBorderShorthand();
        }
        self::assertSame(\trim((string) $document), $sExpected);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function createBorderShorthandProvider(): array
    {
        return [
            ['body {border-width: 2px;border-style: solid;border-color: #000;}', 'body {border: 2px solid #000;}'],
            ['body {border-style: none;}', 'body {border: none;}'],
            ['body {border-width: 1em;border-style: solid;}', 'body {border: 1em solid;}'],
            ['body {margin: 1em;}', 'body {margin: 1em;}'],
        ];
    }

    /**
     * @dataProvider createFontShorthandProvider
     *
     * @test
     */
    public function createFontShorthand(string $sCss, string $sExpected): void
    {
        $parser = new Parser($sCss);
        $document = $parser->parse();
        foreach ($document->getAllDeclarationBlocks() as $declarationBlock) {
            $declarationBlock->createFontShorthand();
        }
        self::assertSame(\trim((string) $document), $sExpected);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function createFontShorthandProvider(): array
    {
        return [
            ['body {font-size: 12px; font-family: serif}', 'body {font: 12px serif;}'],
            ['body {font-size: 12px; font-family: serif; font-style: italic;}', 'body {font: italic 12px serif;}'],
            [
                'body {font-size: 12px; font-family: serif; font-style: italic; font-weight: bold;}',
                'body {font: italic bold 12px serif;}',
            ],
            [
                'body {font-size: 12px; font-family: serif; font-style: italic; font-weight: bold; line-height: 1.6;}',
                'body {font: italic bold 12px/1.6 serif;}',
            ],
            [
                'body {font-size: 12px; font-family: serif; font-style: italic; font-weight: bold; '
                . 'line-height: 1.6; font-variant: small-caps;}',
                'body {font: italic small-caps bold 12px/1.6 serif;}',
            ],
            ['body {margin: 1em;}', 'body {margin: 1em;}'],
        ];
    }

    /**
     * @dataProvider createDimensionsShorthandProvider
     *
     * @test
     */
    public function createDimensionsShorthand(string $sCss, string $sExpected): void
    {
        $parser = new Parser($sCss);
        $document = $parser->parse();
        foreach ($document->getAllDeclarationBlocks() as $declarationBlock) {
            $declarationBlock->createDimensionsShorthand();
        }
        self::assertSame(\trim((string) $document), $sExpected);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function createDimensionsShorthandProvider(): array
    {
        return [
            ['body {border: 1px;}', 'body {border: 1px;}'],
            ['body {margin-top: 1px;}', 'body {margin-top: 1px;}'],
            ['body {margin-top: 1em; margin-right: 1em; margin-bottom: 1em; margin-left: 1em;}', 'body {margin: 1em;}'],
            [
                'body {margin-top: 1em; margin-right: 2em; margin-bottom: 1em; margin-left: 2em;}',
                'body {margin: 1em 2em;}',
            ],
            [
                'body {margin-top: 1em; margin-right: 2em; margin-bottom: 3em; margin-left: 2em;}',
                'body {margin: 1em 2em 3em;}',
            ],
        ];
    }

    /**
     * @dataProvider createBackgroundShorthandProvider
     *
     * @test
     */
    public function createBackgroundShorthand(string $sCss, string $sExpected): void
    {
        $parser = new Parser($sCss);
        $document = $parser->parse();
        foreach ($document->getAllDeclarationBlocks() as $declarationBlock) {
            $declarationBlock->createBackgroundShorthand();
        }
        self::assertSame(\trim((string) $document), $sExpected);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function createBackgroundShorthandProvider(): array
    {
        return [
            ['body {border: 1px;}', 'body {border: 1px;}'],
            ['body {background-color: #f00;}', 'body {background: #f00;}'],
            [
                'body {background-color: #f00;background-image: url(foobar.png);}',
                'body {background: #f00 url("foobar.png");}',
            ],
            [
                'body {background-color: #f00;background-image: url(foobar.png);background-repeat: no-repeat;}',
                'body {background: #f00 url("foobar.png") no-repeat;}',
            ],
            [
                'body {background-color: #f00;background-image: url(foobar.png);background-repeat: no-repeat;}',
                'body {background: #f00 url("foobar.png") no-repeat;}',
            ],
            [
                'body {background-color: #f00;background-image: url(foobar.png);background-repeat: no-repeat;'
                . 'background-position: center;}',
                'body {background: #f00 url("foobar.png") no-repeat center;}',
            ],
            [
                'body {background-color: #f00;background-image: url(foobar.png);background-repeat: no-repeat;'
                . 'background-position: top left;}',
                'body {background: #f00 url("foobar.png") no-repeat top left;}',
            ],
        ];
    }

    /**
     * @test
     */
    public function overrideRules(): void
    {
        $sCss = '.wrapper { left: 10px; text-align: left; }';
        $parser = new Parser($sCss);
        $document = $parser->parse();
        $rule = new Rule('right');
        $rule->setValue('-10px');
        $contents = $document->getContents();
        $wrapper = $contents[0];

        self::assertCount(2, $wrapper->getRules());
        $contents[0]->setRules([$rule]);

        $rules = $wrapper->getRules();
        self::assertCount(1, $rules);
        self::assertSame('right', $rules[0]->getRule());
        self::assertSame('-10px', $rules[0]->getValue());
    }

    /**
     * @test
     */
    public function ruleInsertion(): void
    {
        $sCss = '.wrapper { left: 10px; text-align: left; }';
        $parser = new Parser($sCss);
        $document = $parser->parse();
        $contents = $document->getContents();
        $wrapper = $contents[0];

        $leftRules = $wrapper->getRules('left');
        self::assertCount(1, $leftRules);
        $firstLeftRule = $leftRules[0];

        $textRules = $wrapper->getRules('text-');
        self::assertCount(1, $textRules);
        $firstTextRule = $textRules[0];

        $leftPrefixRule = new Rule('left');
        $leftPrefixRule->setValue(new Size(16, 'em'));

        $textAlignRule = new Rule('text-align');
        $textAlignRule->setValue(new Size(1));

        $borderBottomRule = new Rule('border-bottom-width');
        $borderBottomRule->setValue(new Size(1, 'px'));

        $wrapper->addRule($borderBottomRule);
        $wrapper->addRule($leftPrefixRule, $firstLeftRule);
        $wrapper->addRule($textAlignRule, $firstTextRule);

        $rules = $wrapper->getRules();

        self::assertSame($leftPrefixRule, $rules[0]);
        self::assertSame($firstLeftRule, $rules[1]);
        self::assertSame($textAlignRule, $rules[2]);
        self::assertSame($firstTextRule, $rules[3]);
        self::assertSame($borderBottomRule, $rules[4]);

        self::assertSame(
            '.wrapper {left: 16em;left: 10px;text-align: 1;text-align: left;border-bottom-width: 1px;}',
            $document->render()
        );
    }

    /**
     * @test
     *
     * TODO: The order is different on PHP 5.6 than on PHP >= 7.0.
     */
    public function orderOfElementsMatchingOriginalOrderAfterExpandingShorthands(): void
    {
        $sCss = '.rule{padding:5px;padding-top: 20px}';
        $parser = new Parser($sCss);
        $document = $parser->parse();
        $declarationBlocks = $document->getAllDeclarationBlocks();

        self::assertCount(1, $declarationBlocks);

        $lastDeclarationBlock = \array_pop($declarationBlocks);
        $lastDeclarationBlock->expandShorthands();

        self::assertEquals(
            [
                'padding-top' => 'padding-top: 20px;',
                'padding-right' => 'padding-right: 5px;',
                'padding-bottom' => 'padding-bottom: 5px;',
                'padding-left' => 'padding-left: 5px;',
            ],
            \array_map('strval', $lastDeclarationBlock->getRulesAssoc())
        );
    }
}
