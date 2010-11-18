<?php

require_once('CSSParser.php');

class CSSParserTests extends PHPUnit_Framework_TestCase {
	function testCssFiles() {
		
		$sDirectory = dirname(__FILE__).DIRECTORY_SEPARATOR.'files';
		if($rHandle = opendir($sDirectory)) {
			/* This is the correct way to loop over the directory. */
			while (false !== ($sFileName = readdir($rHandle))) {
				if(strpos($sFileName, '.') === 0) {
					continue;
				}
				if(strrpos($sFileName, '.css') !== strlen($sFileName)-strlen('.css')) {
					continue;
				}
				$oParser = new CSSParser(file_get_contents($sDirectory.DIRECTORY_SEPARATOR.$sFileName));
				try {
					$oParser->parse();
				} catch(Exception $e) {
					$this->fail($e);
				}
			}
			closedir($rHandle);
		}
	}
	
	/**
	* @depends testCssFiles
	*/
	function testColorParsing() {
		$oDoc = $this->parsedStructureForFile('colortest');
		foreach($oDoc->getAllRuleSets() as $oRuleSet) {
			if(!$oRuleSet instanceof CSSSelector) {
				continue;
			}
			$aSelector = $oRuleSet->getSelector();
			if($aSelector[0] === '#mine') {
				$aColorRule = $oRuleSet->getRules('color');
				$aValues = $aColorRule['color']->getValues();
				$this->assertSame('red', $aValues[0][0]);
				$aColorRule = $oRuleSet->getRules('background-');
				$aValues = $aColorRule['background-color']->getValues();
				$this->assertEquals(array('r' => new CSSSize(35.0), 'g' => new CSSSize(35.0), 'b' => new CSSSize(35.0)), $aValues[0][0]->getColor());
				$aColorRule = $oRuleSet->getRules('border-color');
				$aValues = $aColorRule['border-color']->getValues();
				$this->assertEquals(array('r' => new CSSSize(10.0), 'g' => new CSSSize(100.0), 'b' => new CSSSize(230.0), 'a' => new CSSSize(0.3)), $aValues[0][0]->getColor());
				$aColorRule = $oRuleSet->getRules('outline-color');
				$aValues = $aColorRule['outline-color']->getValues();
				$this->assertEquals(array('r' => new CSSSize(34.0), 'g' => new CSSSize(34.0), 'b' => new CSSSize(34.0)), $aValues[0][0]->getColor());
			}
		}
		foreach($oDoc->getAllValues('background-') as $oColor) {
			if($oColor->getColorDescription() === 'hsl') {
				$this->assertEquals(array('h' => new CSSSize(220.0), 's' => new CSSSize(10.0), 'l' => new CSSSize(220.0)), $oColor->getColor());
			}
		}
		foreach($oDoc->getAllValues('color') as $sColor) {
			$this->assertSame('red', $sColor);
		}
	}
	
	function parsedStructureForFile($sFileName) {
		$sFile = dirname(__FILE__).DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR."$sFileName.css";
		$oParser = new CSSParser(file_get_contents($sFile));
		return $oParser->parse();
	}
}