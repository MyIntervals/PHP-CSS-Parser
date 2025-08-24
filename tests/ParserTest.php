<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\CSSList\CSSList;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\CSSList\KeyFrame;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Parsing\OutputException;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Position\Positionable;
use Sabberworm\CSS\Property\AtRule;
use Sabberworm\CSS\Property\Charset;
use Sabberworm\CSS\Property\CSSNamespace;
use Sabberworm\CSS\Property\Import;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\RuleSet\AtRuleSet;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\RuleSet\RuleSet;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\CalcFunction;
use Sabberworm\CSS\Value\Color;
use Sabberworm\CSS\Value\Size;
use Sabberworm\CSS\Value\URL;
use Sabberworm\CSS\Value\ValueList;

/**
 * @covers \Sabberworm\CSS\Parser
 */
final class ParserTest extends TestCase
{
    /**
     * @test
     */
    public function parseForOneDeclarationBlockReturnsDocumentWithOneDeclarationBlock(): void
    {
        $css = '.thing { left: 10px; }';
        $parser = new Parser($css);

        $document = $parser->parse();

        self::assertInstanceOf(Document::class, $document);

        $cssList = $document->getContents();
        self::assertCount(1, $cssList);
        self::assertInstanceOf(DeclarationBlock::class, $cssList[0]);
    }

    /**
     * @test
     */
    public function files(): void
    {
        $directory = __DIR__ . '/fixtures';
        if ($directoryHandle = \opendir($directory)) {
            /* This is the correct way to loop over the directory. */
            while (false !== ($filename = \readdir($directoryHandle))) {
                if (\strpos($filename, '.') === 0) {
                    continue;
                }
                if (\strrpos($filename, '.css') !== \strlen($filename) - \strlen('.css')) {
                    continue;
                }
                if (\strpos($filename, '-') === 0) {
                    // Either a file which SHOULD fail (at least in strict mode)
                    // or a future test of an as-of-now missing feature
                    continue;
                }
                $parser = new Parser(\file_get_contents($directory . '/' . $filename));
                self::assertNotSame('', $parser->parse()->render());
            }
            \closedir($directoryHandle);
        }
    }

    /**
     * @depends files
     *
     * @test
     */
    public function colorParsing(): void
    {
        $document = self::parsedStructureForFile('colortest');
        foreach ($document->getAllDeclarationBlocks() as $declarationBlock) {
            $selectors = $declarationBlock->getSelectors();
            $selector = $selectors[0]->getSelector();
            if ($selector === '#mine') {
                $colorRules = $declarationBlock->getRules('color');
                $colorRuleValue = $colorRules[0]->getValue();
                self::assertSame('red', $colorRuleValue);
                $colorRules = $declarationBlock->getRules('background-');
                $colorRuleValue = $colorRules[0]->getValue();
                self::assertInstanceOf(Color::class, $colorRuleValue);
                self::assertEquals([
                    'r' => new Size(35.0, null, true, $colorRuleValue->getLineNumber()),
                    'g' => new Size(35.0, null, true, $colorRuleValue->getLineNumber()),
                    'b' => new Size(35.0, null, true, $colorRuleValue->getLineNumber()),
                ], $colorRuleValue->getColor());
                $colorRules = $declarationBlock->getRules('border-color');
                $colorRuleValue = $colorRules[0]->getValue();
                self::assertInstanceOf(Color::class, $colorRuleValue);
                self::assertEquals([
                    'r' => new Size(10.0, null, true, $colorRuleValue->getLineNumber()),
                    'g' => new Size(100.0, null, true, $colorRuleValue->getLineNumber()),
                    'b' => new Size(230.0, null, true, $colorRuleValue->getLineNumber()),
                ], $colorRuleValue->getColor());
                $colorRuleValue = $colorRules[1]->getValue();
                self::assertInstanceOf(Color::class, $colorRuleValue);
                self::assertEquals([
                    'r' => new Size(10.0, null, true, $colorRuleValue->getLineNumber()),
                    'g' => new Size(100.0, null, true, $colorRuleValue->getLineNumber()),
                    'b' => new Size(231.0, null, true, $colorRuleValue->getLineNumber()),
                    'a' => new Size('0000.3', null, true, $colorRuleValue->getLineNumber()),
                ], $colorRuleValue->getColor());
                $colorRules = $declarationBlock->getRules('outline-color');
                $colorRuleValue = $colorRules[0]->getValue();
                self::assertInstanceOf(Color::class, $colorRuleValue);
                self::assertEquals([
                    'r' => new Size(34.0, null, true, $colorRuleValue->getLineNumber()),
                    'g' => new Size(34.0, null, true, $colorRuleValue->getLineNumber()),
                    'b' => new Size(34.0, null, true, $colorRuleValue->getLineNumber()),
                ], $colorRuleValue->getColor());
            } elseif ($selector === '#yours') {
                $colorRules = $declarationBlock->getRules('background-color');
                $colorRuleValue = $colorRules[0]->getValue();
                self::assertInstanceOf(Color::class, $colorRuleValue);
                self::assertEquals([
                    'h' => new Size(220.0, null, true, $colorRuleValue->getLineNumber()),
                    's' => new Size(10.0, '%', true, $colorRuleValue->getLineNumber()),
                    'l' => new Size(220.0, '%', true, $colorRuleValue->getLineNumber()),
                ], $colorRuleValue->getColor());
                $colorRuleValue = $colorRules[1]->getValue();
                self::assertInstanceOf(Color::class, $colorRuleValue);
                self::assertEquals([
                    'h' => new Size(220.0, null, true, $colorRuleValue->getLineNumber()),
                    's' => new Size(10.0, '%', true, $colorRuleValue->getLineNumber()),
                    'l' => new Size(220.0, '%', true, $colorRuleValue->getLineNumber()),
                    'a' => new Size(0000.3, null, true, $colorRuleValue->getLineNumber()),
                ], $colorRuleValue->getColor());
                $colorRules = $declarationBlock->getRules('outline-color');
                self::assertEmpty($colorRules);
            }
        }
        foreach ($document->getAllValues(null, 'color') as $colorValue) {
            self::assertSame('red', $colorValue);
        }
        self::assertSame(
            '#mine {color: red;border-color: #0a64e6;border-color: rgba(10,100,231,.3);outline-color: #222;'
            . 'background-color: #232323;}'
            . "\n"
            . '#yours {background-color: hsl(220,10%,220%);background-color: hsla(220,10%,220%,.3);}'
            . "\n"
            . '#variables {background-color: rgb(var(--some-rgb));background-color: rgb(var(--r),var(--g),var(--b));'
            . 'background-color: rgb(255,var(--g),var(--b));background-color: rgb(255,255,var(--b));'
            . 'background-color: rgb(255,var(--rg));background-color: hsl(var(--some-hsl));}'
            . "\n"
            . '#variables-alpha {background-color: rgba(var(--some-rgb),.1);'
            . 'background-color: rgba(var(--some-rg),255,.1);background-color: hsla(var(--some-hsl),.1);}',
            $document->render()
        );
    }

    /**
     * @test
     */
    public function unicodeParsing(): void
    {
        $document = self::parsedStructureForFile('unicode');
        foreach ($document->getAllDeclarationBlocks() as $declarationBlock) {
            $selectors = $declarationBlock->getSelectors();
            $selector = $selectors[0]->getSelector();
            if (\substr($selector, 0, \strlen('.test-')) !== '.test-') {
                continue;
            }
            $contentRules = $declarationBlock->getRules('content');
            $firstContentRuleAsString = $contentRules[0]->getValue()->render(OutputFormat::create());
            if ($selector === '.test-1') {
                self::assertSame('" "', $firstContentRuleAsString);
            }
            if ($selector === '.test-2') {
                self::assertSame('"é"', $firstContentRuleAsString);
            }
            if ($selector === '.test-3') {
                self::assertSame('" "', $firstContentRuleAsString);
            }
            if ($selector === '.test-4') {
                self::assertSame('"𝄞"', $firstContentRuleAsString);
            }
            if ($selector === '.test-5') {
                self::assertSame('"水"', $firstContentRuleAsString);
            }
            if ($selector === '.test-6') {
                self::assertSame('"¥"', $firstContentRuleAsString);
            }
            if ($selector === '.test-7') {
                self::assertSame('"\\A"', $firstContentRuleAsString);
            }
            if ($selector === '.test-8') {
                self::assertSame('"\\"\\""', $firstContentRuleAsString);
            }
            if ($selector === '.test-9') {
                self::assertSame('"\\"\\\'"', $firstContentRuleAsString);
            }
            if ($selector === '.test-10') {
                self::assertSame('"\\\'\\\\"', $firstContentRuleAsString);
            }
            if ($selector === '.test-11') {
                self::assertSame('"test"', $firstContentRuleAsString);
            }
        }
    }

    /**
     * @test
     */
    public function unicodeRangeParsing(): void
    {
        $document = self::parsedStructureForFile('unicode-range');
        $expected = '@font-face {unicode-range: U+0100-024F,U+0259,U+1E??-2EFF,U+202F;}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function specificity(): void
    {
        $document = self::parsedStructureForFile('specificity');
        self::assertEquals([new Selector('#test .help')], $document->getSelectorsBySpecificity('> 100'));
        self::assertEquals(
            [new Selector('#test .help'), new Selector('#file')],
            $document->getSelectorsBySpecificity('>= 100')
        );
        self::assertEquals([new Selector('#file')], $document->getSelectorsBySpecificity('=== 100'));
        self::assertEquals([new Selector('#file')], $document->getSelectorsBySpecificity('== 100'));
        self::assertEquals([
            new Selector('#file'),
            new Selector('.help:hover'),
            new Selector('li.green'),
            new Selector('ol li::before'),
        ], $document->getSelectorsBySpecificity('<= 100'));
        self::assertEquals([
            new Selector('.help:hover'),
            new Selector('li.green'),
            new Selector('ol li::before'),
        ], $document->getSelectorsBySpecificity('< 100'));
        self::assertEquals([new Selector('li.green')], $document->getSelectorsBySpecificity('11'));
        self::assertEquals([new Selector('ol li::before')], $document->getSelectorsBySpecificity('3'));
    }

    /**
     * @test
     */
    public function manipulation(): void
    {
        $document = self::parsedStructureForFile('atrules');
        self::assertSame(
            '@charset "utf-8";'
            . "\n"
            . '@font-face {font-family: "CrassRoots";src: url("../media/cr.ttf");}'
            . "\n"
            . 'html, body {font-size: -.6em;}'
            . "\n"
            . '@keyframes mymove {from {top: 0px;}'
            . "\n\t"
            . 'to {top: 200px;}}'
            . "\n"
            . '@-moz-keyframes some-move {from {top: 0px;}'
            . "\n\t"
            . 'to {top: 200px;}}'
            . "\n"
            . '@supports ( (perspective: 10px) or (-moz-perspective: 10px) or (-webkit-perspective: 10px) or '
            . '(-ms-perspective: 10px) or (-o-perspective: 10px) ) {body {font-family: "Helvetica";}}'
            . "\n"
            . '@page :pseudo-class {margin: 2in;}'
            . "\n"
            . '@-moz-document url(https://www.w3.org/),'
            . "\n"
            . '               url-prefix(https://www.w3.org/Style/),'
            . "\n"
            . '               domain(mozilla.org),'
            . "\n"
            . '               regexp("https:.*") {body {color: purple;background: yellow;}}'
            . "\n"
            . '@media screen and (orientation: landscape) {@-ms-viewport {width: 1024px;height: 768px;}}'
            . "\n"
            . '@region-style #intro {p {color: blue;}}',
            $document->render()
        );
        foreach ($document->getAllDeclarationBlocks() as $declarationBlock) {
            foreach ($declarationBlock->getSelectors() as $selector) {
                //Loop over all selector parts (the comma-separated strings in a selector) and prepend the id
                $selector->setSelector('#my_id ' . $selector->getSelector());
            }
        }
        self::assertSame(
            '@charset "utf-8";'
            . "\n"
            . '@font-face {font-family: "CrassRoots";src: url("../media/cr.ttf");}'
            . "\n"
            . '#my_id html, #my_id body {font-size: -.6em;}'
            . "\n"
            . '@keyframes mymove {from {top: 0px;}'
            . "\n\t"
            . 'to {top: 200px;}}'
            . "\n"
            . '@-moz-keyframes some-move {from {top: 0px;}'
            . "\n\t"
            . 'to {top: 200px;}}'
            . "\n"
            . '@supports ( (perspective: 10px) or (-moz-perspective: 10px) or (-webkit-perspective: 10px) '
            . 'or (-ms-perspective: 10px) or (-o-perspective: 10px) ) {#my_id body {font-family: "Helvetica";}}'
            . "\n"
            . '@page :pseudo-class {margin: 2in;}'
            . "\n"
            . '@-moz-document url(https://www.w3.org/),'
            . "\n"
            . '               url-prefix(https://www.w3.org/Style/),'
            . "\n"
            . '               domain(mozilla.org),'
            . "\n"
            . '               regexp("https:.*") {#my_id body {color: purple;background: yellow;}}'
            . "\n"
            . '@media screen and (orientation: landscape) {@-ms-viewport {width: 1024px;height: 768px;}}'
            . "\n"
            . '@region-style #intro {#my_id p {color: blue;}}',
            $document->render(OutputFormat::create()->setRenderComments(false))
        );

        $document = self::parsedStructureForFile('values');
        self::assertSame(
            '#header {margin: 10px 2em 1cm 2%;font-family: Verdana,Helvetica,"Gill Sans",sans-serif;'
            . 'font-size: 10px;color: red !important;background-color: green;'
            . 'background-color: rgba(0,128,0,.7);frequency: 30Hz;transform: rotate(1turn);}
body {color: green;font: 75% "Lucida Grande","Trebuchet MS",Verdana,sans-serif;}',
            $document->render()
        );
        foreach ($document->getAllRuleSets() as $ruleSet) {
            $ruleSet->removeMatchingRules('font-');
        }
        self::assertSame(
            '#header {margin: 10px 2em 1cm 2%;color: red !important;background-color: green;'
            . 'background-color: rgba(0,128,0,.7);frequency: 30Hz;transform: rotate(1turn);}
body {color: green;}',
            $document->render()
        );
        foreach ($document->getAllRuleSets() as $ruleSet) {
            $ruleSet->removeMatchingRules('background-');
        }
        self::assertSame(
            '#header {margin: 10px 2em 1cm 2%;color: red !important;frequency: 30Hz;transform: rotate(1turn);}
body {color: green;}',
            $document->render()
        );
    }

    /**
     * @test
     */
    public function ruleGetters(): void
    {
        $document = self::parsedStructureForFile('values');
        $declarationBlocks = $document->getAllDeclarationBlocks();
        $headerBlock = $declarationBlocks[0];
        $bodyBlock = $declarationBlocks[1];
        $backgroundHeaderRules = $headerBlock->getRules('background-');
        self::assertCount(2, $backgroundHeaderRules);
        self::assertSame('background-color', $backgroundHeaderRules[0]->getRule());
        self::assertSame('background-color', $backgroundHeaderRules[1]->getRule());
        $backgroundHeaderRules = $headerBlock->getRulesAssoc('background-');
        self::assertCount(1, $backgroundHeaderRules);
        self::assertInstanceOf(Color::class, $backgroundHeaderRules['background-color']->getValue());
        self::assertSame('rgba', $backgroundHeaderRules['background-color']->getValue()->getColorDescription());
        $headerBlock->removeRule($backgroundHeaderRules['background-color']);
        $backgroundHeaderRules = $headerBlock->getRules('background-');
        self::assertCount(1, $backgroundHeaderRules);
        self::assertSame('green', $backgroundHeaderRules[0]->getValue());
    }

    /**
     * @test
     */
    public function slashedValues(): void
    {
        $document = self::parsedStructureForFile('slashed');
        self::assertSame(
            '.test {font: 12px/1.5 Verdana,Arial,sans-serif;border-radius: 5px 10px 5px 10px/10px 5px 10px 5px;}',
            $document->render()
        );
        foreach ($document->getAllValues(null) as $value) {
            if ($value instanceof Size && $value->isSize() && !$value->isRelative()) {
                $value->setSize($value->getSize() * 3);
            }
        }
        foreach ($document->getAllDeclarationBlocks() as $declarationBlock) {
            $fontRules = $declarationBlock->getRules('font');
            $fontRule = $fontRules[0];
            $fontRuleValue = $fontRule->getValue();
            self::assertSame(' ', $fontRuleValue->getListSeparator());
            $fontRuleValueComponents = $fontRuleValue->getListComponents();
            $commaList = $fontRuleValueComponents[1];
            self::assertInstanceOf(ValueList::class, $commaList);
            $slashList = $fontRuleValueComponents[0];
            self::assertInstanceOf(ValueList::class, $slashList);
            self::assertSame(',', $commaList->getListSeparator());
            self::assertSame('/', $slashList->getListSeparator());
            $borderRadiusRules = $declarationBlock->getRules('border-radius');
            $borderRadiusRule = $borderRadiusRules[0];
            $slashList = $borderRadiusRule->getValue();
            self::assertSame('/', $slashList->getListSeparator());
            $slashListComponents = $slashList->getListComponents();
            $secondSlashListComponent = $slashListComponents[1];
            self::assertInstanceOf(ValueList::class, $secondSlashListComponent);
            $firstSlashListComponent = $slashListComponents[0];
            self::assertInstanceOf(ValueList::class, $firstSlashListComponent);
            self::assertSame(' ', $firstSlashListComponent->getListSeparator());
            self::assertSame(' ', $secondSlashListComponent->getListSeparator());
        }
        self::assertSame(
            '.test {font: 36px/1.5 Verdana,Arial,sans-serif;border-radius: 15px 30px 15px 30px/30px 15px 30px 15px;}',
            $document->render()
        );
    }

    /**
     * @test
     */
    public function functionSyntax(): void
    {
        $document = self::parsedStructureForFile('functions');
        $expected = 'div.main {background-image: linear-gradient(#000,#fff);}'
            . "\n"
            . '.collapser::before, .collapser::-moz-before, .collapser::-webkit-before {content: "»";font-size: 1.2em;'
            . 'margin-right: .2em;-moz-transition-property: -moz-transform;-moz-transition-duration: .2s;'
            . '-moz-transform-origin: center 60%;}'
            . "\n"
            . '.collapser.expanded::before, .collapser.expanded::-moz-before,'
            . ' .collapser.expanded::-webkit-before {-moz-transform: rotate(90deg);}'
            . "\n"
            . '.collapser + * {height: 0;overflow: hidden;-moz-transition-property: height;'
            . '-moz-transition-duration: .3s;}'
            . "\n"
            . '.collapser.expanded + * {height: auto;}';
        self::assertSame($expected, $document->render());

        foreach ($document->getAllValues(null, null, true) as $value) {
            if ($value instanceof Size && $value->isSize()) {
                $value->setSize($value->getSize() * 3);
            }
        }
        $expected = \str_replace(['1.2em', '.2em', '60%'], ['3.6em', '.6em', '180%'], $expected);
        self::assertSame($expected, $document->render());

        foreach ($document->getAllValues(null, null, true) as $value) {
            if ($value instanceof Size && !$value->isRelative() && !$value->isColorComponent()) {
                $value->setSize($value->getSize() * 2);
            }
        }
        $expected = \str_replace(['.2s', '.3s', '90deg'], ['.4s', '.6s', '180deg'], $expected);
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function namespaces(): void
    {
        $document = self::parsedStructureForFile('namespaces');
        $expected = '@namespace toto "http://toto.example.org";
@namespace "http://example.com/foo";
@namespace foo url("http://www.example.com/");
@namespace foo url("http://www.example.com/");
foo|test {gaga: 1;}
|test {gaga: 2;}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function innerColors(): void
    {
        $document = self::parsedStructureForFile('inner-color');
        $expected = 'test {background: -webkit-gradient(linear,0 0,0 bottom,from(#006cad),to(hsl(202,100%,49%)));}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function prefixedGradient(): void
    {
        $document = self::parsedStructureForFile('webkit');
        $expected = '.test {background: -webkit-linear-gradient(top right,white,black);}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function listValueRemoval(): void
    {
        $document = self::parsedStructureForFile('atrules');
        foreach ($document->getContents() as $contentItem) {
            if ($contentItem instanceof AtRule) {
                $document->remove($contentItem);
                continue;
            }
        }
        self::assertSame('html, body {font-size: -.6em;}', $document->render());

        $document = self::parsedStructureForFile('nested');
        foreach ($document->getAllDeclarationBlocks() as $declarationBlock) {
            $document->removeDeclarationBlockBySelector($declarationBlock, false);
            break;
        }
        self::assertSame(
            'html {some-other: -test(val1);}
@media screen {html {some: -test(val2);}}
#unrelated {other: yes;}',
            $document->render()
        );

        $document = self::parsedStructureForFile('nested');
        foreach ($document->getAllDeclarationBlocks() as $declarationBlock) {
            $document->removeDeclarationBlockBySelector($declarationBlock, true);
            break;
        }
        self::assertSame(
            '@media screen {html {some: -test(val2);}}
#unrelated {other: yes;}',
            $document->render()
        );
    }

    /**
     * @test
     */
    public function selectorRemoval(): void
    {
        $this->expectException(OutputException::class);

        $document = self::parsedStructureForFile('1readme');
        $declarationsBlocks = $document->getAllDeclarationBlocks();
        $declarationBlock = $declarationsBlocks[0];
        self::assertTrue($declarationBlock->removeSelector('html'));
        $expected = '@charset "utf-8";
@font-face {font-family: "CrassRoots";src: url("../media/cr.ttf");}
body {font-size: 1.6em;}';
        self::assertSame($expected, $document->render());
        self::assertFalse($declarationBlock->removeSelector('html'));
        self::assertTrue($declarationBlock->removeSelector('body'));
        // This tries to output a declaration block without a selector and throws.
        $document->render();
    }

    /**
     * @test
     */
    public function comments(): void
    {
        $document = self::parsedStructureForFile('comments');
        $expected = <<<EXPECTED
@import url("some/url.css") screen;
.foo, #bar {background-color: #000;}
@media screen {#foo.bar {position: absolute;}}
EXPECTED;
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function urlInFile(): void
    {
        $document = self::parsedStructureForFile('url', Settings::create()->withMultibyteSupport(true));
        $expected = 'body {background: #fff url("https://somesite.com/images/someimage.gif") repeat top center;}
body {background-url: url("https://somesite.com/images/someimage.gif");}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function hexAlphaInFile(): void
    {
        $document = self::parsedStructureForFile('hex-alpha', Settings::create()->withMultibyteSupport(true));
        $expected = 'div {background: rgba(17,34,51,.27);}
div {background: rgba(17,34,51,.27);}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function calcInFile(): void
    {
        $document = self::parsedStructureForFile('calc', Settings::create()->withMultibyteSupport(true));
        $expected = 'div {width: calc(100% / 4);}
div {margin-top: calc(-120% - 4px);}
div {height: calc(9 / 16 * 100%) !important;width: calc(( 50px - 50% ) * 2);}
div {width: calc(50% - ( ( 4% ) * .5 ));}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function calcNestedInFile(): void
    {
        $document = self::parsedStructureForFile('calc-nested', Settings::create()->withMultibyteSupport(true));
        $expected = '.test {font-size: calc(( 3 * 4px ) + -2px);top: calc(200px - calc(20 * 3px));}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function invalidCalcInFile(): void
    {
        $document = self::parsedStructureForFile('calc-invalid', Settings::create()->withMultibyteSupport(true));
        $expected = 'div {}
div {}
div {}
div {height: -moz-calc;}
div {height: calc;}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function invalidCalc(): void
    {
        $parser = new Parser('div { height: calc(100px');
        $document = $parser->parse();
        self::assertSame('div {height: calc(100px);}', $document->render());

        $parser = new Parser('div { height: calc(100px)');
        $document = $parser->parse();
        self::assertSame('div {height: calc(100px);}', $document->render());

        $parser = new Parser('div { height: calc(100px);');
        $document = $parser->parse();
        self::assertSame('div {height: calc(100px);}', $document->render());

        $parser = new Parser('div { height: calc(100px}');
        $document = $parser->parse();
        self::assertSame('div {}', $document->render());

        $parser = new Parser('div { height: calc(100px;');
        $document = $parser->parse();
        self::assertSame('div {}', $document->render());

        $parser = new Parser('div { height: calc(100px;}');
        $document = $parser->parse();
        self::assertSame('div {}', $document->render());
    }

    /**
     * @test
     */
    public function gridLineNameInFile(): void
    {
        $document = self::parsedStructureForFile('grid-linename', Settings::create()->withMultibyteSupport(true));
        $expected = "div {grid-template-columns: [linename] 100px;}\n"
            . 'span {grid-template-columns: [linename1 linename2] 100px;}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function emptyGridLineNameLenientInFile(): void
    {
        $document = self::parsedStructureForFile('empty-grid-linename');
        $expected = '.test {grid-template-columns: [] 100px;}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function invalidGridLineNameInFile(): void
    {
        $document = self::parsedStructureForFile(
            'invalid-grid-linename',
            Settings::create()->withMultibyteSupport(true)
        );
        $expected = 'div {}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function unmatchedBracesInFile(): void
    {
        $document = self::parsedStructureForFile('unmatched_braces', Settings::create()->withMultibyteSupport(true));
        $expected = 'button, input, checkbox, textarea {outline: 0;margin: 0;}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function invalidSelectorsInFile(): void
    {
        $document = self::parsedStructureForFile('invalid-selectors', Settings::create()->withMultibyteSupport(true));
        $expected = '@keyframes mymove {from {top: 0px;}}
#test {color: white;background: green;}
#test {display: block;background: white;color: black;}';
        self::assertSame($expected, $document->render());

        $document = self::parsedStructureForFile('invalid-selectors-2', Settings::create()->withMultibyteSupport(true));
        $expected = '@media only screen and (max-width: 1215px) {.breadcrumb {padding-left: 10px;}
	.super-menu > li:first-of-type {border-left-width: 0;}
	.super-menu > li:last-of-type {border-right-width: 0;}
	html[dir="rtl"] .super-menu > li:first-of-type {border-left-width: 1px;border-right-width: 0;}
	html[dir="rtl"] .super-menu > li:last-of-type {border-left-width: 0;}}
body {background-color: red;}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function selectorEscapesInFile(): void
    {
        $document = self::parsedStructureForFile('selector-escapes', Settings::create()->withMultibyteSupport(true));
        $expected = '#\\# {color: red;}
.col-sm-1\\/5 {width: 20%;}';
        self::assertSame($expected, $document->render());

        $document = self::parsedStructureForFile('invalid-selectors-2', Settings::create()->withMultibyteSupport(true));
        $expected = '@media only screen and (max-width: 1215px) {.breadcrumb {padding-left: 10px;}
	.super-menu > li:first-of-type {border-left-width: 0;}
	.super-menu > li:last-of-type {border-right-width: 0;}
	html[dir="rtl"] .super-menu > li:first-of-type {border-left-width: 1px;border-right-width: 0;}
	html[dir="rtl"] .super-menu > li:last-of-type {border-left-width: 0;}}
body {background-color: red;}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function identifierEscapesInFile(): void
    {
        $document = self::parsedStructureForFile('identifier-escapes', Settings::create()->withMultibyteSupport(true));
        $expected = 'div {font: 14px Font Awesome\\ 5 Pro;font: 14px Font Awesome\\} 5 Pro;'
            . 'font: 14px Font Awesome\\; 5 Pro;f\\;ont: 14px Font Awesome\\; 5 Pro;}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function selectorIgnoresInFile(): void
    {
        $document = self::parsedStructureForFile('selector-ignores', Settings::create()->withMultibyteSupport(true));
        $expected = '.some[selectors-may=\'contain-a-{\'] {}'
            . "\n"
            . '.this-selector  .valid {width: 100px;}'
            . "\n"
            . '@media only screen and (min-width: 200px) {.test {prop: val;}}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function keyframeSelectors(): void
    {
        $document = self::parsedStructureForFile(
            'keyframe-selector-validation',
            Settings::create()->withMultibyteSupport(true)
        );
        $expected = '@-webkit-keyframes zoom {0% {-webkit-transform: scale(1,1);}'
            . "\n\t"
            . '50% {-webkit-transform: scale(1.2,1.2);}'
            . "\n\t"
            . '100% {-webkit-transform: scale(1,1);}}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function lineNameFailure(): void
    {
        $this->expectException(UnexpectedTokenException::class);

        self::parsedStructureForFile('-empty-grid-linename', Settings::create()->withLenientParsing(false));
    }

    /**
     * @test
     */
    public function calcFailure(): void
    {
        $this->expectException(UnexpectedTokenException::class);

        self::parsedStructureForFile('-calc-no-space-around-minus', Settings::create()->withLenientParsing(false));
    }

    /**
     * @test
     */
    public function urlInFileMbOff(): void
    {
        $document = self::parsedStructureForFile('url', Settings::create()->withMultibyteSupport(false));
        $expected = 'body {background: #fff url("https://somesite.com/images/someimage.gif") repeat top center;}'
            . "\n"
            . 'body {background-url: url("https://somesite.com/images/someimage.gif");}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function emptyFile(): void
    {
        $document = self::parsedStructureForFile('-empty', Settings::create()->withMultibyteSupport(true));
        $expected = '';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function emptyFileMbOff(): void
    {
        $document = self::parsedStructureForFile('-empty', Settings::create()->withMultibyteSupport(false));
        $expected = '';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function charsetLenient1(): void
    {
        $document = self::parsedStructureForFile('-charset-after-rule', Settings::create()->withLenientParsing(true));
        $expected = '#id {prop: var(--val);}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function charsetLenient2(): void
    {
        $document = self::parsedStructureForFile('-charset-in-block', Settings::create()->withLenientParsing(true));
        $expected = '@media print {}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function trailingWhitespace(): void
    {
        $document = self::parsedStructureForFile('trailing-whitespace', Settings::create()->withLenientParsing(false));
        $expected = 'div {width: 200px;}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function charsetFailure1(): void
    {
        $this->expectException(UnexpectedTokenException::class);

        self::parsedStructureForFile('-charset-after-rule', Settings::create()->withLenientParsing(false));
    }

    /**
     * @test
     */
    public function charsetFailure2(): void
    {
        $this->expectException(UnexpectedTokenException::class);

        self::parsedStructureForFile('-charset-in-block', Settings::create()->withLenientParsing(false));
    }

    /**
     * @test
     */
    public function unopenedClosingBracketFailure(): void
    {
        $this->expectException(SourceException::class);

        self::parsedStructureForFile('-unopened-close-brackets', Settings::create()->withLenientParsing(false));
    }

    /**
     * Ensure that a missing property value raises an exception.
     *
     * @test
     */
    public function missingPropertyValueStrict(): void
    {
        $this->expectException(UnexpectedTokenException::class);

        self::parsedStructureForFile('missing-property-value', Settings::create()->withLenientParsing(false));
    }

    /**
     * Ensure that a missing property value is ignored when in lenient parsing mode.
     *
     * @test
     */
    public function missingPropertyValueLenient(): void
    {
        $parsed = self::parsedStructureForFile('missing-property-value', Settings::create()->withLenientParsing(true));
        $declarationBlocks = $parsed->getAllDeclarationBlocks();
        self::assertCount(1, $declarationBlocks);
        $block = $declarationBlocks[0];
        self::assertInstanceOf(DeclarationBlock::class, $block);
        self::assertEquals([new Selector('div')], $block->getSelectors());
        $rules = $block->getRules();
        self::assertCount(1, $rules);
        $rule = $rules[0];
        self::assertSame('display', $rule->getRule());
        self::assertSame('inline-block', $rule->getValue());
    }

    /**
     * Parses structure for file.
     *
     * @param string $filename
     * @param Settings|null $settings
     */
    public static function parsedStructureForFile($filename, $settings = null): Document
    {
        $filename = __DIR__ . "/fixtures/$filename.css";
        $parser = new Parser(\file_get_contents($filename), $settings);
        return $parser->parse();
    }

    /**
     * @depends files
     *
     * @test
     */
    public function lineNumbersParsing(): void
    {
        $document = self::parsedStructureForFile('line-numbers');
        // array key is the expected line number
        $expected = [
            1 => [Charset::class],
            3 => [CSSNamespace::class],
            5 => [AtRuleSet::class],
            11 => [DeclarationBlock::class],
            // Line Numbers of the inner declaration blocks
            17 => [KeyFrame::class, 18, 20],
            23 => [Import::class],
            25 => [DeclarationBlock::class],
        ];

        $actual = [];
        foreach ($document->getContents() as $contentItem) {
            self::assertInstanceOf(Positionable::class, $contentItem);
            $actual[$contentItem->getLineNumber()] = [\get_class($contentItem)];
            if ($contentItem instanceof KeyFrame) {
                foreach ($contentItem->getContents() as $block) {
                    self::assertInstanceOf(Positionable::class, $block);
                    $actual[$contentItem->getLineNumber()][] = $block->getLineNumber();
                }
            }
        }

        $expectedLineNumbers = [7, 26];
        $actualLineNumbers = [];
        foreach ($document->getAllValues() as $value) {
            if ($value instanceof URL) {
                $actualLineNumbers[] = $value->getLineNumber();
            }
        }

        // Checking for the multiline color rule lines 27-31
        $expectedColorLineNumbers = [28, 29, 30];
        $declarationBlocks = $document->getAllDeclarationBlocks();
        // Choose the 2nd one
        $secondDeclarationBlock = $declarationBlocks[1];
        $rules = $secondDeclarationBlock->getRules();
        // Choose the 2nd one
        $valueOfSecondRule = $rules[1]->getValue();
        self::assertInstanceOf(Color::class, $valueOfSecondRule);
        self::assertSame(27, $rules[1]->getLineNumber());

        $actualColorLineNumbers = [];
        foreach ($valueOfSecondRule->getColor() as $size) {
            $actualColorLineNumbers[] = $size->getLineNumber();
        }

        self::assertSame($expectedColorLineNumbers, $actualColorLineNumbers);
        self::assertSame($expectedLineNumbers, $actualLineNumbers);
        self::assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function unexpectedTokenExceptionLineNo(): void
    {
        $this->expectException(UnexpectedTokenException::class);

        $parser = new Parser("\ntest: 1;", Settings::create()->beStrict());
        try {
            $parser->parse();
        } catch (UnexpectedTokenException $e) {
            self::assertSame(2, $e->getLineNumber());
            throw $e;
        }
    }

    /**
     * @depends files
     *
     * @test
     */
    public function commentExtracting(): void
    {
        $document = self::parsedStructureForFile('comments');
        $nodes = $document->getContents();

        // Import property.
        self::assertInstanceOf(Commentable::class, $nodes[0]);
        $importComments = $nodes[0]->getComments();
        self::assertCount(2, $importComments);
        self::assertSame("*\n * Comments\n ", $importComments[0]->getComment());
        self::assertSame(' Hell ', $importComments[1]->getComment());

        // Declaration block.
        $fooBarBlock = $nodes[1];
        self::assertInstanceOf(Commentable::class, $fooBarBlock);
        $fooBarBlockComments = $fooBarBlock->getComments();
        // TODO Support comments in selectors.
        // $this->assertCount(2, $fooBarBlockComments);
        // $this->assertSame("* Number 4 *", $fooBarBlockComments[0]->getComment());
        // $this->assertSame("* Number 5 *", $fooBarBlockComments[1]->getComment());

        // Declaration rules.
        self::assertInstanceOf(DeclarationBlock::class, $fooBarBlock);
        $fooBarRules = $fooBarBlock->getRules();
        $fooBarRule = $fooBarRules[0];
        $fooBarRuleComments = $fooBarRule->getComments();
        self::assertCount(1, $fooBarRuleComments);
        self::assertSame(' Number 6 ', $fooBarRuleComments[0]->getComment());

        // Media property.
        self::assertInstanceOf(Commentable::class, $nodes[2]);
        $mediaComments = $nodes[2]->getComments();
        self::assertCount(0, $mediaComments);

        // Media children.
        self::assertInstanceOf(CSSList::class, $nodes[2]);
        $mediaRules = $nodes[2]->getContents();
        self::assertInstanceOf(Commentable::class, $mediaRules[0]);
        $fooBarComments = $mediaRules[0]->getComments();
        self::assertCount(1, $fooBarComments);
        self::assertSame('* Number 10 *', $fooBarComments[0]->getComment());

        // Media -> declaration -> rule.
        self::assertInstanceOf(DeclarationBlock::class, $mediaRules[0]);
        $fooBarRules = $mediaRules[0]->getRules();
        $fooBarChildComments = $fooBarRules[0]->getComments();
        self::assertCount(1, $fooBarChildComments);
        self::assertSame('* Number 10b *', $fooBarChildComments[0]->getComment());
    }

    /**
     * @test
     */
    public function flatCommentExtractingOneComment(): void
    {
        $parser = new Parser('div {/*Find Me!*/left:10px; text-align:left;}');
        $document = $parser->parse();

        $contents = $document->getContents();
        self::assertInstanceOf(DeclarationBlock::class, $contents[0]);
        $divRules = $contents[0]->getRules();
        $comments = $divRules[0]->getComments();

        self::assertCount(1, $comments);
        self::assertSame('Find Me!', $comments[0]->getComment());
    }

    /**
     * @test
     */
    public function flatCommentExtractingTwoConjoinedCommentsForOneRule(): void
    {
        $parser = new Parser('div {/*Find Me!*//*Find Me Too!*/left:10px; text-align:left;}');
        $document = $parser->parse();

        $contents = $document->getContents();
        self::assertInstanceOf(DeclarationBlock::class, $contents[0]);
        $divRules = $contents[0]->getRules();
        $comments = $divRules[0]->getComments();

        self::assertCount(2, $comments);
        self::assertSame('Find Me!', $comments[0]->getComment());
        self::assertSame('Find Me Too!', $comments[1]->getComment());
    }

    /**
     * @test
     */
    public function flatCommentExtractingTwoSpaceSeparatedCommentsForOneRule(): void
    {
        $parser = new Parser('div { /*Find Me!*/ /*Find Me Too!*/ left:10px; text-align:left;}');
        $document = $parser->parse();

        $contents = $document->getContents();
        self::assertInstanceOf(DeclarationBlock::class, $contents[0]);
        $divRules = $contents[0]->getRules();
        $comments = $divRules[0]->getComments();

        self::assertCount(2, $comments);
        self::assertSame('Find Me!', $comments[0]->getComment());
        self::assertSame('Find Me Too!', $comments[1]->getComment());
    }

    /**
     * @test
     */
    public function flatCommentExtractingCommentsForTwoRules(): void
    {
        $parser = new Parser('div {/*Find Me!*/left:10px; /*Find Me Too!*/text-align:left;}');
        $document = $parser->parse();

        $contents = $document->getContents();
        self::assertInstanceOf(DeclarationBlock::class, $contents[0]);
        $divRules = $contents[0]->getRules();
        $rule1Comments = $divRules[0]->getComments();
        $rule2Comments = $divRules[1]->getComments();

        self::assertCount(1, $rule1Comments);
        self::assertCount(1, $rule2Comments);
        self::assertSame('Find Me!', $rule1Comments[0]->getComment());
        self::assertSame('Find Me Too!', $rule2Comments[0]->getComment());
    }

    /**
     * @test
     */
    public function topLevelCommentExtracting(): void
    {
        $parser = new Parser('/*Find Me!*/div {left:10px; text-align:left;}');
        $document = $parser->parse();
        $contents = $document->getContents();
        self::assertInstanceOf(Commentable::class, $contents[0]);
        $comments = $contents[0]->getComments();
        self::assertCount(1, $comments);
        self::assertSame('Find Me!', $comments[0]->getComment());
    }

    /**
     * @test
     */
    public function microsoftFilterStrictParsing(): void
    {
        $this->expectException(UnexpectedTokenException::class);

        $document = self::parsedStructureForFile('ms-filter', Settings::create()->beStrict());
    }

    /**
     * @test
     */
    public function microsoftFilterParsing(): void
    {
        $document = self::parsedStructureForFile('ms-filter');
        $expected = '.test {filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#80000000",'
            . 'endColorstr="#00000000",GradientType=1);}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function largeSizeValuesInFile(): void
    {
        $document = self::parsedStructureForFile('large-z-index', Settings::create()->withMultibyteSupport(false));
        $expected = '.overlay {z-index: 10000000000000000000000;}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function scientificNotationSizeValuesInFile(): void
    {
        $document = self::parsedStructureForFile(
            'scientific-notation-numbers',
            Settings::create()->withMultibyteSupport(false)
        );
        $expected = ''
            . 'body {background-color: rgba(62,174,151,3041820656523200167936);'
            . 'z-index: .030418206565232;font-size: 1em;top: 192.3478px;}';
        self::assertSame($expected, $document->render());
    }

    /**
     * @test
     */
    public function lonelyImport(): void
    {
        $document = self::parsedStructureForFile('lonely-import');
        $expected = '@import url("example.css") only screen and (max-width: 600px);';
        self::assertSame($expected, $document->render());
    }

    public function escapedSpecialCaseTokens(): void
    {
        $document = self::parsedStructureForFile('escaped-tokens');
        $contents = $document->getContents();
        self::assertInstanceOf(RuleSet::class, $contents[0]);
        $rules = $contents[0]->getRules();
        $urlRule = $rules[0];
        $calcRule = $rules[1];
        self::assertInstanceOf(URL::class, $urlRule->getValue());
        self::assertInstanceOf(CalcFunction::class, $calcRule->getValue());
    }
}
