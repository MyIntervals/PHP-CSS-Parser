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
     *
     * @test
     */
    public function faultToleranceOff()
    {
        $sFile = __DIR__ . '/../fixtures/-fault-tolerance.css';
        $oParser = new Parser(file_get_contents($sFile), Settings::create()->beStrict());
        $oParser->parse();
    }

    /**
     * @test
     */
    public function faultToleranceOn()
    {
        $sFile = __DIR__ . '/../fixtures/-fault-tolerance.css';
        $oParser = new Parser(file_get_contents($sFile), Settings::create()->withLenientParsing(true));
        $oResult = $oParser->parse();
        self::assertSame(
            '.test1 {}' . "\n" . '.test2 {hello: 2.2;hello: 2000000000000.2;}' . "\n" . '#test {}' . "\n"
            . '#test2 {help: none;}',
            $oResult->render()
        );
    }

    /**
     * @expectedException \Sabberworm\CSS\Parsing\UnexpectedTokenException
     *
     * @test
     */
    public function endToken()
    {
        $sFile = __DIR__ . '/../fixtures/-end-token.css';
        $oParser = new Parser(file_get_contents($sFile), Settings::create()->beStrict());
        $oParser->parse();
    }

    /**
     * @expectedException \Sabberworm\CSS\Parsing\UnexpectedTokenException
     *
     * @test
     */
    public function endToken2()
    {
        $sFile = __DIR__ . '/../fixtures/-end-token-2.css';
        $oParser = new Parser(file_get_contents($sFile), Settings::create()->beStrict());
        $oParser->parse();
    }

    /**
     * @test
     */
    public function endTokenPositive()
    {
        $sFile = __DIR__ . '/../fixtures/-end-token.css';
        $oParser = new Parser(file_get_contents($sFile), Settings::create()->withLenientParsing(true));
        $oResult = $oParser->parse();
        self::assertSame("", $oResult->render());
    }

    /**
     * @test
     */
    public function endToken2Positive()
    {
        $sFile = __DIR__ . '/../fixtures/-end-token-2.css';
        $oParser = new Parser(file_get_contents($sFile), Settings::create()->withLenientParsing(true));
        $oResult = $oParser->parse();
        self::assertSame(
            '#home .bg-layout {background-image: url("/bundles/main/img/bg1.png?5");}',
            $oResult->render()
        );
    }

    /**
     * @test
     */
    public function localeTrap()
    {
        setlocale(LC_ALL, "pt_PT", "no");
        $sFile = __DIR__ . '/../fixtures/-fault-tolerance.css';
        $oParser = new Parser(file_get_contents($sFile), Settings::create()->withLenientParsing(true));
        $oResult = $oParser->parse();
        self::assertSame(
            '.test1 {}' . "\n" . '.test2 {hello: 2.2;hello: 2000000000000.2;}' . "\n" . '#test {}' . "\n"
            . '#test2 {help: none;}',
            $oResult->render()
        );
    }

    /**
     * @test
     */
    public function caseInsensitivity()
    {
        $sFile = __DIR__ . '/../fixtures/case-insensitivity.css';
        $oParser = new Parser(file_get_contents($sFile));
        $oResult = $oParser->parse();

        self::assertSame(
            '@charset "utf-8";' . "\n"
            . '@import url("test.css");'
            . "\n@media screen {}"
            . "\n#myid {case: insensitive !important;frequency: 30Hz;font-size: 1em;color: #ff0;"
            . 'color: hsl(40,40%,30%);font-family: Arial;}',
            $oResult->render()
        );
    }
}
