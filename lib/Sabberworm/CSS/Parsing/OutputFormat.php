<?php

namespace Sabberworm\CSS;

class OutputFormat {
	public $sStringQuotingType = '"';
	public $bNewlinesAfterRule = false;
	public $bNewlinesStartingRuleSets = false;
	public $sIndentationLevel = 0;
	public $sIndentation = "\t";
	
	private $oFormatter = null;
	
	public function __construct() {
	}
	
	public function nextLevel() {
		$result = clone $this;
		$result->sIndentationLevel++;
		$result->oFormatter = null;
		return $result;
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
	
	public function newline($bOutput = false) {
		if($bOutput) {
			return $this->indent()."\n";
		}
		return '';
	}
	
	public function indent() {
		return str_repeat($this->sIndentation, $this->sIndentationLevel);
	}
	
	
}