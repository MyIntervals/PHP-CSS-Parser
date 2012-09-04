<?php

namespace Sabberworm\CSS\Property;

/**
* Class representing an @namespace rule.
*/
class CssNamespace {
	private $sValue;
	private $sPrefix;
	
	public function __construct($sValue, $sPrefix = null) {
		$this->sValue = $sValue;
		$this->sPrefix = $sPrefix;		
	}
	
	public function __toString() {
		return '@namespace '.$this->sPrefix.' "'.$this->sValue.'";';
	}
	
	public function getValue() {
		return $this->sValue;
	}

	public function getPrefix() {
		return $this->sPrefix;
	}

	public function setValue($sValue) {
		$this->sValue = $sValue;
	}

	public function setPrefix($sPrefix) {
		$this->sPrefix = $sPrefix;
	}

}