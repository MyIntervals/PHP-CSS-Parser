<?php

namespace Sabberworm\CSS\Tests;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Parsing\OutputException;

/**
 * @covers \Sabberworm\CSS\OutputFormat
 */
final class OutputFormatTest extends TestCase
{
    /**
     * @var string
     */
    private const TEST_CSS = <<<EOT

.main, .test {
	font: italic normal bold 16px/1.2 "Helvetica", Verdana, sans-serif;
	background: white;
}

@media screen {
	.main {
		background-size: 100% 100%;
		font-size: 1.3em;
		background-color: #fff;
	}
}

EOT;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Document
     */
    private $document;

    protected function setUp(): void
    {
        $this->parser = new Parser(self::TEST_CSS);
        $this->document = $this->parser->parse();
    }

    /**
     * @test
     */
    public function plain(): void
    {
        self::assertSame(
            '.main, .test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
            $this->document->render()
        );
    }

    /**
     * @test
     */
    public function compact(): void
    {
        self::assertSame(
            '.main,.test{font:italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background:white;}'
            . '@media screen{.main{background-size:100% 100%;font-size:1.3em;background-color:#fff;}}',
            $this->document->render(OutputFormat::createCompact())
        );
    }

    /**
     * @test
     */
    public function pretty(): void
    {
        self::assertSame(self::TEST_CSS, $this->document->render(OutputFormat::createPretty()));
    }

    /**
     * @test
     */
    public function spaceAfterListArgumentSeparator(): void
    {
        self::assertSame(
            '.main, .test {font: italic   normal   bold   16px/  1.2   '
            . '"Helvetica",  Verdana,  sans-serif;background: white;}'
            . "\n@media screen {.main {background-size: 100%   100%;font-size: 1.3em;background-color: #fff;}}",
            $this->document->render(OutputFormat::create()->setSpaceAfterListArgumentSeparator('  '))
        );
    }

    /**
     * @test
     */
    public function spaceAfterListArgumentSeparatorComplex(): void
    {
        self::assertSame(
            '.main, .test {font: italic normal bold 16px/1.2 "Helvetica",	Verdana,	sans-serif;background: white;}'
            . "\n@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}",
            $this->document->render(OutputFormat::create()->setSpaceAfterListArgumentSeparator([
                'default' => ' ',
                ',' => "\t",
                '/' => '',
                ' ' => '',
            ]))
        );
    }

    /**
     * @test
     */
    public function spaceAfterSelectorSeparator(): void
    {
        self::assertSame(
            '.main,
.test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
            $this->document->render(OutputFormat::create()->setSpaceAfterSelectorSeparator("\n"))
        );
    }

    /**
     * @test
     */
    public function stringQuotingType(): void
    {
        self::assertSame(
            '.main, .test {font: italic normal bold 16px/1.2 \'Helvetica\',Verdana,sans-serif;background: white;}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
            $this->document->render(OutputFormat::create()->setStringQuotingType("'"))
        );
    }

    /**
     * @test
     */
    public function rGBHashNotation(): void
    {
        self::assertSame(
            '.main, .test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: rgb(255,255,255);}}',
            $this->document->render(OutputFormat::create()->setRGBHashNotation(false))
        );
    }

    /**
     * @test
     */
    public function semicolonAfterLastRule(): void
    {
        self::assertSame(
            '.main, .test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff}}',
            $this->document->render(OutputFormat::create()->setSemicolonAfterLastRule(false))
        );
    }

    /**
     * @test
     */
    public function spaceAfterRuleName(): void
    {
        self::assertSame(
            '.main, .test {font:	italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background:	white;}
@media screen {.main {background-size:	100% 100%;font-size:	1.3em;background-color:	#fff;}}',
            $this->document->render(OutputFormat::create()->setSpaceAfterRuleName("\t"))
        );
    }

    /**
     * @test
     */
    public function spaceRules(): void
    {
        self::assertSame('.main, .test {
	font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;
	background: white;
}
@media screen {.main {
		background-size: 100% 100%;
		font-size: 1.3em;
		background-color: #fff;
	}}', $this->document->render(OutputFormat::create()->set('Space*Rules', "\n")));
    }

    /**
     * @test
     */
    public function spaceBlocks(): void
    {
        self::assertSame('
.main, .test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen {
	.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}
}
', $this->document->render(OutputFormat::create()->set('Space*Blocks', "\n")));
    }

    /**
     * @test
     */
    public function spaceBoth(): void
    {
        self::assertSame('
.main, .test {
	font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;
	background: white;
}
@media screen {
	.main {
		background-size: 100% 100%;
		font-size: 1.3em;
		background-color: #fff;
	}
}
', $this->document->render(OutputFormat::create()->set('Space*Rules', "\n")->set('Space*Blocks', "\n")));
    }

    /**
     * @test
     */
    public function spaceBetweenBlocks(): void
    {
        self::assertSame(
            '.main, .test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}'
            . '@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
            $this->document->render(OutputFormat::create()->setSpaceBetweenBlocks(''))
        );
    }

    /**
     * @test
     */
    public function indentation(): void
    {
        self::assertSame('
.main, .test {
font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;
background: white;
}
@media screen {
.main {
background-size: 100% 100%;
font-size: 1.3em;
background-color: #fff;
}
}
', $this->document->render(OutputFormat::create()
            ->set('Space*Rules', "\n")
            ->set('Space*Blocks', "\n")
            ->setIndentation('')));
    }

    /**
     * @test
     */
    public function spaceBeforeBraces(): void
    {
        self::assertSame(
            '.main, .test{font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen{.main{background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
            $this->document->render(OutputFormat::create()->setSpaceBeforeOpeningBrace(''))
        );
    }

    /**
     * @test
     */
    public function ignoreExceptionsOff(): void
    {
        $this->expectException(OutputException::class);

        $declarationBlocks = $this->document->getAllDeclarationBlocks();
        $firstDeclarationBlock = $declarationBlocks[0];
        $firstDeclarationBlock->removeSelector('.main');
        self::assertSame(
            '.test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
            $this->document->render(OutputFormat::create()->setIgnoreExceptions(false))
        );
        $firstDeclarationBlock->removeSelector('.test');
        $this->document->render(OutputFormat::create()->setIgnoreExceptions(false));
    }

    /**
     * @test
     */
    public function ignoreExceptionsOn(): void
    {
        $declarationBlocks = $this->document->getAllDeclarationBlocks();
        $firstDeclarationBlock = $declarationBlocks[0];
        $firstDeclarationBlock->removeSelector('.main');
        $firstDeclarationBlock->removeSelector('.test');
        self::assertSame(
            '@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
            $this->document->render(OutputFormat::create()->setIgnoreExceptions(true))
        );
    }
}
