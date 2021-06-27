<?php

namespace Sabberworm\CSS\Tests\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Settings;

/**
 * @covers \Sabberworm\CSS\Parser
 * @covers \Sabberworm\CSS\CSSList\Document::parse
 * @covers \Sabberworm\CSS\Rule\Rule::parse
 * @covers \Sabberworm\CSS\RuleSet\DeclarationBlock::parse
 * @covers \Sabberworm\CSS\Value\CalcFunction::parse
 * @covers \Sabberworm\CSS\Value\Color::parse
 * @covers \Sabberworm\CSS\Value\CSSString::parse
 * @covers \Sabberworm\CSS\Value\LineName::parse
 * @covers \Sabberworm\CSS\Value\Size::parse
 * @covers \Sabberworm\CSS\Value\URL::parse
 */
class LenientParsingTest extends TestCase
{
    /**
     * @expectedException \Sabberworm\CSS\Parsing\UnexpectedTokenException
     */
    public function testFaultToleranceOff()
    {
        $sFile = __DIR__ . '/../fixtures/-fault-tolerance.css';
        $oParser = new Parser(file_get_contents($sFile), Settings::create()->beStrict());
        $oParser->parse();
    }

    public function testFaultToleranceOn()
    {
        $sFile = __DIR__ . '/../fixtures/-fault-tolerance.css';
        $oParser = new Parser(file_get_contents($sFile), Settings::create()->withLenientParsing(true));
        $oResult = $oParser->parse();
        $this->assertSame(
            '.test1 {}' . "\n" . '.test2 {hello: 2.2;hello: 2000000000000.2;}' . "\n" . '#test {}' . "\n"
            . '#test2 {help: none;}',
            $oResult->render()
        );
    }

    /**
     * @expectedException \Sabberworm\CSS\Parsing\UnexpectedTokenException
     */
    public function testEndToken()
    {
        $sFile = __DIR__ . '/../fixtures/-end-token.css';
        $oParser = new Parser(file_get_contents($sFile), Settings::create()->beStrict());
        $oParser->parse();
    }

    /**
     * @expectedException \Sabberworm\CSS\Parsing\UnexpectedTokenException
     */
    public function testEndToken2()
    {
        $sFile = __DIR__ . '/../fixtures/-end-token-2.css';
        $oParser = new Parser(file_get_contents($sFile), Settings::create()->beStrict());
        $oParser->parse();
    }

    public function testEndTokenPositive()
    {
        $sFile = __DIR__ . '/../fixtures/-end-token.css';
        $oParser = new Parser(file_get_contents($sFile), Settings::create()->withLenientParsing(true));
        $oResult = $oParser->parse();
        $this->assertSame("", $oResult->render());
    }

    public function testEndToken2Positive()
    {
        $sFile = __DIR__ . '/../fixtures/-end-token-2.css';
        $oParser = new Parser(file_get_contents($sFile), Settings::create()->withLenientParsing(true));
        $oResult = $oParser->parse();
        $this->assertSame(
            '#home .bg-layout {background-image: url("/bundles/main/img/bg1.png?5");}',
            $oResult->render()
        );
    }

    public function testLocaleTrap()
    {
        setlocale(LC_ALL, "pt_PT", "no");
        $sFile = __DIR__ . '/../fixtures/-fault-tolerance.css';
        $oParser = new Parser(file_get_contents($sFile), Settings::create()->withLenientParsing(true));
        $oResult = $oParser->parse();
        $this->assertSame(
            '.test1 {}' . "\n" . '.test2 {hello: 2.2;hello: 2000000000000.2;}' . "\n" . '#test {}' . "\n"
            . '#test2 {help: none;}',
            $oResult->render()
        );
    }

    public function testCaseInsensitivity()
    {
        $sFile = __DIR__ . '/../fixtures/case-insensitivity.css';
        $oParser = new Parser(file_get_contents($sFile));
        $oResult = $oParser->parse();

        $this->assertSame(
            '@charset "utf-8";' . "\n"
            . '@import url("test.css");'
            . "\n@media screen {}"
            . "\n#myid {case: insensitive !important;frequency: 30Hz;font-size: 1em;color: #ff0;"
            . 'color: hsl(40,40%,30%);font-family: Arial;}',
            $oResult->render()
        );
    }
}
