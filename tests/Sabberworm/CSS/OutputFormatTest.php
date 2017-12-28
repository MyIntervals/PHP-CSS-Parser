<?php

namespace Sabberworm\CSS;

use Sabberworm\CSS\CSSList\Document;

class OutputFormatTest extends \PHPUnit_Framework_TestCase
{
	private $oParser;

	/**
	 * @var Document
	 */
	private $oDocument;

	private static $testCSS = <<<EOT

/**
 * LICENSE comment
 */
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

	protected function setUp()
	{
		$this->oParser = new Parser(self::$testCSS);
		$this->oDocument = $this->oParser->parse();
	}

	public function testPlain()
	{
		$this->assertSame(
			'.main, .test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
			$this->oDocument->render()
		);
	}

	public function testCompact()
	{
		$this->assertSame(
			'.main,.test{font:italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background:white;}@media screen{.main{background-size:100% 100%;font-size:1.3em;background-color:#fff;}}',
			$this->oDocument->render(OutputFormat::createCompact())
		);
	}

	public function testCompactWithComments()
	{
		$expected = <<<EOT
/**
 * LICENSE comment
 */
.main,.test{font:italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background:white;}@media screen{.main{background-size:100% 100%;font-size:1.3em;background-color:#fff;}}
EOT;
		$this->assertSame(
			$expected,
			$this->oDocument->render(
				OutputFormat::createCompact()
					->setKeepComments(true)
			)
		);
	}

	public function testPretty()
	{
		$this->assertSame(
			self::$testCSS,
			$this->oDocument->render(OutputFormat::createPretty())
		);
	}

	public function testSpaceAfterListArgumentSeparator()
	{
		$this->assertSame(
			'.main, .test {font: italic   normal   bold   16px/  1.2   "Helvetica",  Verdana,  sans-serif;background: white;}
@media screen {.main {background-size: 100%   100%;font-size: 1.3em;background-color: #fff;}}',
			$this->oDocument->render(OutputFormat::create()->setSpaceAfterListArgumentSeparator("  "))
		);
	}

	public function testSpaceAfterListArgumentSeparatorComplex()
	{
		$this->assertSame(
			'.main, .test {font: italic normal bold 16px/1.2 "Helvetica",	Verdana,	sans-serif;background: white;}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
			$this->oDocument->render(
				OutputFormat::create()->setSpaceAfterListArgumentSeparator(
					array('default' => ' ', ',' => "\t", '/' => '', ' ' => '')
				)
			)
		);
	}

	public function testSpaceAfterSelectorSeparator()
	{
		$this->assertSame(
			'.main,
.test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
			$this->oDocument->render(OutputFormat::create()->setSpaceAfterSelectorSeparator("\n"))
		);
	}

	public function testStringQuotingType()
	{
		$this->assertSame(
			'.main, .test {font: italic normal bold 16px/1.2 \'Helvetica\',Verdana,sans-serif;background: white;}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
			$this->oDocument->render(OutputFormat::create()->setStringQuotingType("'"))
		);
	}

	public function testRGBHashNotation()
	{
		$this->assertSame(
			'.main, .test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: rgb(255,255,255);}}',
			$this->oDocument->render(OutputFormat::create()->setRGBHashNotation(false))
		);
	}

	public function testSemicolonAfterLastRule()
	{
		$this->assertSame(
			'.main, .test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff}}',
			$this->oDocument->render(OutputFormat::create()->setSemicolonAfterLastRule(false))
		);
	}

	public function testSpaceAfterRuleName()
	{
		$this->assertSame(
			'.main, .test {font:	italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background:	white;}
@media screen {.main {background-size:	100% 100%;font-size:	1.3em;background-color:	#fff;}}',
			$this->oDocument->render(OutputFormat::create()->setSpaceAfterRuleName("\t"))
		);
	}

	public function testSpaceRules()
	{
		$this->assertSame(
			'.main, .test {
	font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;
	background: white;
}
@media screen {.main {
		background-size: 100% 100%;
		font-size: 1.3em;
		background-color: #fff;
	}}',
			$this->oDocument->render(OutputFormat::create()->set('Space*Rules', "\n"))
		);
	}

	public function testSpaceBlocks()
	{
		$this->assertSame(
			'
.main, .test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen {
	.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}
}
',
			$this->oDocument->render(OutputFormat::create()->set('Space*Blocks', "\n"))
		);
	}

	public function testSpaceBoth()
	{
		$this->assertSame(
			'
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
',
			$this->oDocument->render(OutputFormat::create()->set('Space*Rules', "\n")->set('Space*Blocks', "\n"))
		);
	}

	public function testSpaceBetweenBlocks()
	{
		$this->assertSame(
			'.main, .test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
			$this->oDocument->render(OutputFormat::create()->setSpaceBetweenBlocks(''))
		);
	}

	public function testIndentation()
	{
		$this->assertSame(
			'
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
',
			$this->oDocument->render(
				OutputFormat::create()->set('Space*Rules', "\n")->set('Space*Blocks', "\n")->setIndentation('')
			)
		);
	}

	public function testSpaceBeforeBraces()
	{
		$this->assertSame(
			'.main, .test{font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen{.main{background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
			$this->oDocument->render(OutputFormat::create()->setSpaceBeforeOpeningBrace(''))
		);
	}

	/**
	 * @expectedException Sabberworm\CSS\Parsing\OutputException
	 */
	public function testIgnoreExceptionsOff()
	{
		$aBlocks = $this->oDocument->getAllDeclarationBlocks();
		$oFirstBlock = $aBlocks[0];
		$oFirstBlock->removeSelector('.main');
		$this->assertSame(
			'.test {font: italic normal bold 16px/1.2 "Helvetica",Verdana,sans-serif;background: white;}
@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
			$this->oDocument->render(OutputFormat::create()->setIgnoreExceptions(false))
		);
		$oFirstBlock->removeSelector('.test');
		$this->oDocument->render(OutputFormat::create()->setIgnoreExceptions(false));
	}

	public function testIgnoreExceptionsOn()
	{
		$aBlocks = $this->oDocument->getAllDeclarationBlocks();
		$oFirstBlock = $aBlocks[0];
		$oFirstBlock->removeSelector('.main');
		$oFirstBlock->removeSelector('.test');
		$this->assertSame(
			'@media screen {.main {background-size: 100% 100%;font-size: 1.3em;background-color: #fff;}}',
			$this->oDocument->render(OutputFormat::create()->setIgnoreExceptions(true))
		);
	}

}
