<?php

namespace Sabberworm\CSS\Parsing;

/**
* Thrown if the CSS parsers encounters a token it did not expect
*/
class UnexpectedTokenException extends \Exception {
	protected $sExpected;
	protected $sFound;
	// Possible values: literal, identifier, count, expression, search
	protected $sMatchType;

	public function __construct($sExpected, $sFound, $sMatchType = 'literal') {
		$this->sExpected = $sExpected;
		$this->sFound = $sFound;
		$this->sMatchType = $sMatchType;
		$sMessage = "Token “{$sExpected}” ({$sMatchType}) not found. Got “{$sFound}”.";
		if($this->sMatchType === 'search') {
			$sMessage = "Search for “{$sExpected}” returned no results. Context: “{$sFound}”.";
		} else if($this->sMatchType === 'count') {
			$sMessage = "Next token was expected to have {$sExpected} chars. Context: “{$sFound}”.";
		} else if($this->sMatchType === 'identifier') {
			$sMessage = "Identifier expected. Got “{$sFound}”";
		}
		parent::__construct($sMessage);
	}
}