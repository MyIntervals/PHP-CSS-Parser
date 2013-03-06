<?php

namespace Sabberworm\CSS\RuleSet;

use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Settings;

class LenientParsingTest extends \PHPUnit_Framework_TestCase {

	/**
	* @expectedException Sabberworm\CSS\Parsing\UnexpectedTokenException
	*/
	public function testFaultToleranceOff() {
		$sFile = dirname(__FILE__) . '/../../../files' . DIRECTORY_SEPARATOR . "fault-tolerance.css";
		$oParser = new Parser(file_get_contents($sFile), Settings::create()->beStrict());
		$oParser->parse();
	}

	public function testFaultToleranceOn() {
		$sFile = dirname(__FILE__) . '/../../../files' . DIRECTORY_SEPARATOR . "fault-tolerance.css";
		$oParser = new Parser(file_get_contents($sFile), Settings::create()->withLenientParsing(true));
		$oResult = $oParser->parse();
		$this->assertSame('.test1 {}'."\n".'.test2 {hello: 2;}'."\n", $oResult->__toString());
	}

}
