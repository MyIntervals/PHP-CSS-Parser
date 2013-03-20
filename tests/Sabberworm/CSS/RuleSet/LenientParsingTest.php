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

	public function testCaseInsensitivity() {
		$sFile = dirname(__FILE__) . '/../../../files' . DIRECTORY_SEPARATOR . "case-insensitivity.css";
		$oParser = new Parser(file_get_contents($sFile));
		$oResult = $oParser->parse();
		$this->assertSame('@charset "utf-8";@import url("test.css");@media screen {}#myid {case: insensitive !important;frequency: 30Hz;color: #ff0;color: hsl(40,40%,30%);font-family: Arial;}'."\n", $oResult->__toString());
	}

}
