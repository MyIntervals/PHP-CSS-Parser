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

    private function setUpTestcase()
    {
        $this->oParser = new Parser(self::TEST_CSS);
        $this->oDocument = $this->oParser->parse();
    }

    /**
     * @test
     */
    public function plain()
    {
        $this->setUpTestcase();

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
        $this->setUpTestcase();

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
        $this->setUpTestcase();

        self::assertSame(self::TEST_CSS, $this->oDocument->render(OutputFormat::createPretty()));
    }

    /**
     * @test
     */
    public function spaceAfterListArgumentSeparator()
    {
        $this->setUpTestcase();

        self::assertSame(
            '.main, .test {font: italic   normal   bold   16px/  1.2   '
            . '"Helvetica",  Verdana,  sans-serif;background: white;}'
            . "\n@media screen {.main {background-size: 100%   100%;font-size: 1.3em;background-color: #fff;}}",
            $this->oDocument->render(OutputFormat::create()->setSpaceAfterListArgumentSeparator("  "))
        );
    }

    /**
     * @test
     *
     * @deprecated since version 8.8.0; will be removed in version 9.0.
     * Use `setSpaceAfterListArgumentSeparators()` to set different spacing per separator.
     */
    public function spaceAfterListArgumentSeparatorComplexDeprecated()
    {
        $this->setUpTestcase();

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
    public function spaceAfterListArgumentSeparatorComplex()
    {
        $this->setUpTestcase();

        self::assertSame(
            '.main, .test {font: italic normal bold 16px/1.2 "Helvetica",	Verdana,	sans-serif;background: white;}'
            . "\n@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}",
            $this->oDocument->render(
                OutputFormat::create()
                    ->setSpaceAfterListArgumentSeparator(' ')
                    ->setSpaceAfterListArgumentSeparators([
                        ',' => "\t",
                        '/' => '',
                        ' ' => '',
                    ])
            )
        );
    }

    /**
     * @test
     */
    public function spaceAfterSelectorSeparator()
    {
        $this->setUpTestcase();

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
        $this->setUpTestcase();

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
        $this->setUpTestcase();

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
        $this->setUpTestcase();

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
        $this->setUpTestcase();

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
        $this->setUpTestcase();

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
        $this->setUpTestcase();

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
        $this->setUpTestcase();

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
        $this->setUpTestcase();

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
        $this->setUpTestcase();

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
        $this->setUpTestcase();

        self::assertSame(
            '.main, .test{font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen{.main{background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
            $this->oDocument->render(OutputFormat::create()->setSpaceBeforeOpeningBrace(''))
        );
    }

    /**
     * @test
     */
    public function ignoreExceptionsOff()
    {
        $this->setUpTestcase();

        $this->expectException(OutputException::class);

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
        $this->setUpTestcase();

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
