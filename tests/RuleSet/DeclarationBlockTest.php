<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\Settings as ParserSettings;
use Sabberworm\CSS\Value\Size;

/**
 * @covers \Sabberworm\CSS\RuleSet\DeclarationBlock
 */
final class DeclarationBlockTest extends TestCase
{
    /**
     * @test
     */
    public function overrideRules(): void
    {
        $css = '.wrapper { left: 10px; text-align: left; }';
        $parser = new Parser($css);
        $document = $parser->parse();
        $rule = new Rule('right');
        $rule->setValue('-10px');
        $contents = $document->getContents();
        $wrapper = $contents[0];

        self::assertInstanceOf(DeclarationBlock::class, $wrapper);
        self::assertCount(2, $wrapper->getRules());
        $wrapper->setRules([$rule]);

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
        $css = '.wrapper { left: 10px; text-align: left; }';
        $parser = new Parser($css);
        $document = $parser->parse();
        $contents = $document->getContents();
        $wrapper = $contents[0];

        self::assertInstanceOf(DeclarationBlock::class, $wrapper);

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
     * @return array<string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function declarationBlocksWithCommentsProvider(): array
    {
        return [
            'CSS comments with one asterisk' => ['p {color: #000;/* black */}', 'p {color: #000;}'],
            'CSS comments with two asterisks' => ['p {color: #000;/** black */}', 'p {color: #000;}'],
        ];
    }

    /**
     * @test
     * @dataProvider declarationBlocksWithCommentsProvider
     */
    public function canRemoveCommentsFromRulesUsingLenientParsing(
        string $cssWithComments,
        string $cssWithoutComments
    ): void {
        $parserSettings = ParserSettings::create()->withLenientParsing(true);
        $document = (new Parser($cssWithComments, $parserSettings))->parse();

        $outputFormat = (new OutputFormat())->setRenderComments(false);
        $renderedDocument = $document->render($outputFormat);

        self::assertSame($cssWithoutComments, $renderedDocument);
    }

    /**
     * @test
     * @dataProvider declarationBlocksWithCommentsProvider
     */
    public function canRemoveCommentsFromRulesUsingStrictParsing(
        string $cssWithComments,
        string $cssWithoutComments
    ): void {
        $parserSettings = ParserSettings::create()->withLenientParsing(false);
        $document = (new Parser($cssWithComments, $parserSettings))->parse();

        $outputFormat = (new OutputFormat())->setRenderComments(false);
        $renderedDocument = $document->render($outputFormat);

        self::assertSame($cssWithoutComments, $renderedDocument);
    }
}
