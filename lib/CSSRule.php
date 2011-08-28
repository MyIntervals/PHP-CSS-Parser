<?php

/**
* CSSRuleSets contains CSSRule objects which always have a key and a value.
* In CSS, CSSRules are expressed as follows: “key: value[0][0] value[0][1], value[1][0] value[1][1];”
*/
class CSSRule {
	private $sRule;
	private $aValues;
	private $bIsImportant;
	
	public function __construct($sRule) {
		$this->sRule = $sRule;
		$this->bIsImportant = false;
	}
	
	public function setRule($sRule) {
			$this->sRule = $sRule;
	}

	public function getRule() {
			return $this->sRule;
	}
	
	public function addValue($mValue) {
		$this->aValues[] = $mValue;
	}
	
	public function setValues($aValues) {
			$this->aValues = $aValues;
	}

	public function getValues() {
			return $this->aValues;
	}
	
	public function setIsImportant($bIsImportant) {
			$this->bIsImportant = $bIsImportant;
	}

	public function getIsImportant() {
			return $this->bIsImportant;
	}
	public function __toString() {
		$sResult = "{$this->sRule}: ";
		foreach($this->aValues as $aValues) {
			$sResult .= implode(', ', $aValues).' ';
		}
		if($this->bIsImportant) {
			$sResult .= '!important';
		} else {
			$sResult = substr($sResult, 0, -1);
		}
		$sResult .= ';';
		return $sResult;
	}
}
