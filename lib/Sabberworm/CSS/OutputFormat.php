<?php

namespace Sabberworm\CSS;

class OutputFormat {
	public $sStringQuotingType = '"';
	public $bNewlinesAfterRule = false;
	public $bNewlinesStartingRuleSets = false;
	public $sIndentationLevel = 0;
	public $sIndentation = "\t";
	public $bIgnoreExceptions = false;
	
	public $bRGBHashNotation = true;
	
	private $oFormatter = null;
	
	public function __construct() {
	}
	
	public function get($sName) {
		$aVarPrefixes = array('a', 's', 'm', 'b', 'f', 'o', 'c', 'i');
		foreach($aVarPrefixes as $sPrefix) {
			$sFieldName = $sPrefix.ucfirst($sName);
			if(isset($this->$sFieldName)) {
				return $this->$sFieldName;
			}
		}
		return null;
	}
	
	public function set($sName, $mValue) {
		$aVarPrefixes = array('a', 's', 'm', 'b', 'f', 'o', 'c', 'i');
		foreach($aVarPrefixes as $sPrefix) {
			$sFieldName = $sPrefix.ucfirst($sName);
			if(isset($this->$sFieldName)) {
				$this->$sFieldName = $mValue;
				return $this;
			}
		}
		return false;
	}
	
	public function nextLevel() {
		$result = clone $this;
		$result->sIndentationLevel++;
		$result->oFormatter = null;
		return $result;
	}
	
	public function beLenient() {
		$this->bIgnoreExceptions = true;
	}
	
	public static function create() {
		return new OutputFormatter();
	}
	
	public function getFormatter() {
		if($this->oFormatter === null) {
			$this->oFormatter = new OutputFormatter($this);
		}
		return $this->oFormatter;
	}
}

class OutputFormatter {
	private $oFormat;
	
	public function __construct(OutputFormat $oFormat) {
		$this->oFormat = $oFormat;
	}
	
	public function newline($bOutput = true) {
		if($bOutput) {
			return $this->indent()."\n";
		}
		return "\n";
	}
	
	public function newlineAfterRule($bIsLast = false) {
		if($this->oFormat->bNewlinesAfterRule) {
			return $this->newline(!$bIsLast);
		}
		return '';
	}
	
	public function newlineAfterStartingRuleSet() {
		if($this->oFormat->$bNewlinesStartingRuleSets) {
			return $this->newline();
		}
		return '';
	}
	
	/**
	* Returns the given code, either swallowing or passing exceptions, depending on the bIgnoreExceptions setting.
	*/
	public function safely($cCode) {
		if($this->oFormat->bIgnoreExceptions) {
			// If output exceptions are ignored, run the code with exception guards
			try {
				return $cCode();
			} catch (OutputException $e) {
				return null;
			} //Do nothing
		} else {
			// Run the code as-is
			return $cCode();
		}
	}
	
	public function indent() {
		return str_repeat($this->oFormat->sIndentation, $this->oFormat->sIndentationLevel);
	}
	
	
}