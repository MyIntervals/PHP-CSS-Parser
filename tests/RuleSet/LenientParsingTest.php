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

        $pathToFile = __DIR__ . '/../fixtures/-fault-tolerance.css';
        $parser = new Parser(\file_get_contents($pathToFile), Settings::create()->beStrict());
        $parser->parse();
    }

    /**
     * @test
     */
    public function faultToleranceOn(): void
    {
        $pathToFile = __DIR__ . '/../fixtures/-fault-tolerance.css';
        $parser = new Parser(\file_get_contents($pathToFile), Settings::create()->withLenientParsing(true));
        $result = $parser->parse();
        self::assertSame(
            '.test1 {}' . "\n" . '.test2 {hello: 2.2;hello: 2000000000000.2;}' . "\n" . '#test {}' . "\n"
            . '#test2 {help: none;}',
            $result->render()
        );
    }

    /**
     * @test
     */
    public function endToken(): void
    {
        $this->expectException(UnexpectedTokenException::class);

        $pathToFile = __DIR__ . '/../fixtures/-end-token.css';
        $parser = new Parser(\file_get_contents($pathToFile), Settings::create()->beStrict());
        $parser->parse();
    }

    /**
     * @test
     */
    public function endToken2(): void
    {
        $this->expectException(UnexpectedTokenException::class);

        $pathToFile = __DIR__ . '/../fixtures/-end-token-2.css';
        $parser = new Parser(\file_get_contents($pathToFile), Settings::create()->beStrict());
        $parser->parse();
    }

    /**
     * @test
     */
    public function endTokenPositive(): void
    {
        $pathToFile = __DIR__ . '/../fixtures/-end-token.css';
        $parser = new Parser(\file_get_contents($pathToFile), Settings::create()->withLenientParsing(true));
        $result = $parser->parse();
        self::assertSame('', $result->render());
    }

    /**
     * @test
     */
    public function endToken2Positive(): void
    {
        $pathToFile = __DIR__ . '/../fixtures/-end-token-2.css';
        $parser = new Parser(\file_get_contents($pathToFile), Settings::create()->withLenientParsing(true));
        $result = $parser->parse();
        self::assertSame(
            '#home .bg-layout {background-image: url("/bundles/main/img/bg1.png?5");}',
            $result->render()
        );
    }

    /**
     * @test
     */
    public function localeTrap(): void
    {
        \setlocale(LC_ALL, 'pt_PT', 'no');
        $pathToFile = __DIR__ . '/../fixtures/-fault-tolerance.css';
        $parser = new Parser(\file_get_contents($pathToFile), Settings::create()->withLenientParsing(true));
        $result = $parser->parse();
        self::assertSame(
            '.test1 {}' . "\n" . '.test2 {hello: 2.2;hello: 2000000000000.2;}' . "\n" . '#test {}' . "\n"
            . '#test2 {help: none;}',
            $result->render()
        );
    }

    /**
     * @test
     */
    public function caseInsensitivity(): void
    {
        $pathToFile = __DIR__ . '/../fixtures/case-insensitivity.css';
        $parser = new Parser(\file_get_contents($pathToFile));
        $result = $parser->parse();

        self::assertSame(
            '@charset "utf-8";' . "\n"
            . '@import url("test.css");'
            . "\n@media screen {}"
            . "\n#myid {case: insensitive !important;frequency: 30Hz;font-size: 1em;color: #ff0;"
            . 'color: hsl(40,40%,30%);font-family: Arial;}',
            $result->render()
        );
    }

    /**
     * @test
     */
    public function cssWithInvalidColorStillGetsParsedAsDocument(): void
    {
        $pathToFile = __DIR__ . '/../fixtures/invalid-color.css';
        $parser = new Parser(\file_get_contents($pathToFile), Settings::create()->withLenientParsing(true));
        $result = $parser->parse();

        self::assertInstanceOf(Document::class, $result);
    }

    /**
     * @test
     */
    public function invalidColorStrict(): void
    {
        $this->expectException(UnexpectedTokenException::class);

        $pathToFile = __DIR__ . '/../fixtures/invalid-color.css';
        $parser = new Parser(\file_get_contents($pathToFile), Settings::create()->beStrict());
        $parser->parse();
    }
}
