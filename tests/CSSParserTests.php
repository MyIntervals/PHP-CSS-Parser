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
}