<?php

namespace Sabberworm\CSS\Tests;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parser;

/**
 * @covers \Sabberworm\CSS\OutputFormat
 */
class OutputFormatTest extends TestCase
{
    /**
     * @var string
     */
    const TEST_CSS = <<<EOT

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
    private $oParser;

    /**
     * @var Document
     */
    private $oDocument;

    protected function setUp()
    {
        $this->oParser = new Parser(self::TEST_CSS);
        $this->oDocument = $this->oParser->parse();
    }

    /**
     * @test
     */
    public function plain()
    {
        self::assertSame(
            '.main, .test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
            $this->oDocument->render()
        );
    }

    /**
     * @test
     */
    public function compact()
    {
        self::assertSame(
            '.main,.test{font:italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background:white;}'
            . '@media screen{.main{background-size:100% 100%;font-size:1.3em;background-color:#fff;}}',
            $this->oDocument->render(OutputFormat::createCompact())
        );
    }

    /**
     * @test
     */
    public function pretty()
    {
        self::assertSame(self::TEST_CSS, $this->oDocument->render(OutputFormat::createPretty()));
    }

    /**
     * @test
     */
    public function spaceAfterListArgumentSeparator()
    {
        self::assertSame(
            '.main, .test {font: italic   normal   bold   16px/  1.2   '
            . '"Helvetica",  Verdana,  sans-serif;background: white;}'
            . "\n@media screen {.main {background-size: 100%   100%;font-size: 1.3em;background-color: #fff;}}",
            $this->oDocument->render(OutputFormat::create()->setSpaceAfterListArgumentSeparator("  "))
        );
    }

    /**
     * @test
     */
    public function spaceAfterListArgumentSeparatorComplex()
    {
        self::assertSame(
            '.main, .test {font: italic normal bold 16px/1.2 "Helvetica",	Verdana,	sans-serif;background: white;}'
            . "\n@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}",
            $this->oDocument->render(OutputFormat::create()->setSpaceAfterListArgumentSeparator([
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
    public function spaceAfterSelectorSeparator()
    {
        self::assertSame(
            '.main,
.test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
            $this->oDocument->render(OutputFormat::create()->setSpaceAfterSelectorSeparator("\n"))
        );
    }

    /**
     * @test
     */
    public function stringQuotingType()
    {
        self::assertSame(
            '.main, .test {font: italic normal bold 16px/1.2 \'Helvetica\',Verdana,sans-serif;background: white;}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
            $this->oDocument->render(OutputFormat::create()->setStringQuotingType("'"))
        );
    }

    /**
     * @test
     */
    public function rGBHashNotation()
    {
        self::assertSame(
            '.main, .test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: rgb(255,255,255);}}',
            $this->oDocument->render(OutputFormat::create()->setRGBHashNotation(false))
        );
    }

    /**
     * @test
     */
    public function semicolonAfterLastRule()
    {
        self::assertSame(
            '.main, .test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff}}',
            $this->oDocument->render(OutputFormat::create()->setSemicolonAfterLastRule(false))
        );
    }

    /**
     * @test
     */
    public function spaceAfterRuleName()
    {
        self::assertSame(
            '.main, .test {font:	italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background:	white;}
@media screen {.main {background-size:	100% 100%;font-size:	1.3em;background-color:	#fff;}}',
            $this->oDocument->render(OutputFormat::create()->setSpaceAfterRuleName("\t"))
        );
    }

    /**
     * @test
     */
    public function spaceRules()
    {
        self::assertSame('.main, .test {
	font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;
	background: white;
}
@media screen {.main {
		background-size: 100% 100%;
		font-size: 1.3em;
		background-color: #fff;
	}}', $this->oDocument->render(OutputFormat::create()->set('Space*Rules', "\n")));
    }

    /**
     * @test
     */
    public function spaceBlocks()
    {
        self::assertSame('
.main, .test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen {
	.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}
}
', $this->oDocument->render(OutputFormat::create()->set('Space*Blocks', "\n")));
    }

    /**
     * @test
     */
    public function spaceBoth()
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
', $this->oDocument->render(OutputFormat::create()->set('Space*Rules', "\n")->set('Space*Blocks', "\n")));
    }

    /**
     * @test
     */
    public function spaceBetweenBlocks()
    {
        self::assertSame(
            '.main, .test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}'
            . '@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
            $this->oDocument->render(OutputFormat::create()->setSpaceBetweenBlocks(''))
        );
    }

    /**
     * @test
     */
    public function indentation()
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
', $this->oDocument->render(OutputFormat::create()
            ->set('Space*Rules', "\n")
            ->set('Space*Blocks', "\n")
            ->setIndentation('')));
    }

    /**
     * @test
     */
    public function spaceBeforeBraces()
    {
        self::assertSame(
            '.main, .test{font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen{.main{background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
            $this->oDocument->render(OutputFormat::create()->setSpaceBeforeOpeningBrace(''))
        );
    }

    /**
     * @expectedException \Sabberworm\CSS\Parsing\OutputException
     *
     * @test
     */
    public function ignoreExceptionsOff()
    {
        $aBlocks = $this->oDocument->getAllDeclarationBlocks();
        $oFirstBlock = $aBlocks[0];
        $oFirstBlock->removeSelector('.main');
        self::assertSame(
            '.test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
            $this->oDocument->render(OutputFormat::create()->setIgnoreExceptions(false))
        );
        $oFirstBlock->removeSelector('.test');
        $this->oDocument->render(OutputFormat::create()->setIgnoreExceptions(false));
    }

    /**
     * @test
     */
    public function ignoreExceptionsOn()
    {
        $aBlocks = $this->oDocument->getAllDeclarationBlocks();
        $oFirstBlock = $aBlocks[0];
        $oFirstBlock->removeSelector('.main');
        $oFirstBlock->removeSelector('.test');
        self::assertSame(
            '@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
            $this->oDocument->render(OutputFormat::create()->setIgnoreExceptions(true))
        );
    }
}
