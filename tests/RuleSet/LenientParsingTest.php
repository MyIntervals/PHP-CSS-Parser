<?php

namespace Sabberworm\CSS\Tests\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
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
final class LenientParsingTest extends TestCase
{
    /**
     * @test
     */
    public function faultToleranceOff(): void
    {
        $this->expectException(UnexpectedTokenException::class);

        $sFile = __DIR__ . '/../fixtures/-fault-tolerance.css';
        $oParser = new Parser(\file_get_contents($sFile), Settings::create()->beStrict());
        $oParser->parse();
    }

    /**
     * @test
     */
    public function faultToleranceOn(): void
    {
        $sFile = __DIR__ . '/../fixtures/-fault-tolerance.css';
        $oParser = new Parser(\file_get_contents($sFile), Settings::create()->withLenientParsing(true));
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
    public function endToken(): void
    {
        $this->expectException(UnexpectedTokenException::class);

        $sFile = __DIR__ . '/../fixtures/-end-token.css';
        $oParser = new Parser(\file_get_contents($sFile), Settings::create()->beStrict());
        $oParser->parse();
    }

    /**
     * @test
     */
    public function endToken2(): void
    {
        $this->expectException(UnexpectedTokenException::class);

        $sFile = __DIR__ . '/../fixtures/-end-token-2.css';
        $oParser = new Parser(\file_get_contents($sFile), Settings::create()->beStrict());
        $oParser->parse();
    }

    /**
     * @test
     */
    public function endTokenPositive(): void
    {
        $sFile = __DIR__ . '/../fixtures/-end-token.css';
        $oParser = new Parser(\file_get_contents($sFile), Settings::create()->withLenientParsing(true));
        $oResult = $oParser->parse();
        self::assertSame('', $oResult->render());
    }

    /**
     * @test
     */
    public function endToken2Positive(): void
    {
        $sFile = __DIR__ . '/../fixtures/-end-token-2.css';
        $oParser = new Parser(\file_get_contents($sFile), Settings::create()->withLenientParsing(true));
        $oResult = $oParser->parse();
        self::assertSame(
            '#home .bg-layout {background-image: url("/bundles/main/img/bg1.png?5");}',
            $oResult->render()
        );
    }

    /**
     * @test
     */
    public function localeTrap(): void
    {
        \setlocale(LC_ALL, 'pt_PT', 'no');
        $sFile = __DIR__ . '/../fixtures/-fault-tolerance.css';
        $oParser = new Parser(\file_get_contents($sFile), Settings::create()->withLenientParsing(true));
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
    public function caseInsensitivity(): void
    {
        $sFile = __DIR__ . '/../fixtures/case-insensitivity.css';
        $oParser = new Parser(\file_get_contents($sFile));
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

    /**
     * @test
     */
    public function cssWithInvalidColorStillGetsParsedAsDocument(): void
    {
        $sFile = __DIR__ . '/../fixtures/invalid-color.css';
        $oParser = new Parser(\file_get_contents($sFile), Settings::create()->withLenientParsing(true));
        $result = $oParser->parse();

        self::assertInstanceOf(Document::class, $result);
    }

    /**
     * @test
     */
    public function invalidColorStrict(): void
    {
        $this->expectException(UnexpectedTokenException::class);

        $sFile = __DIR__ . '/../fixtures/invalid-color.css';
        $oParser = new Parser(\file_get_contents($sFile), Settings::create()->beStrict());
        $oParser->parse();
    }
}
