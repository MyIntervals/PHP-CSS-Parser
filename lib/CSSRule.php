<?php

/**
* CSSRuleSets contains CSSRule objects which always have a key and a value.
* In CSS, CSSRules are expressed as follows: “key: value[0][0] value[0][1], value[1][0] value[1][1];”
*/
class CSSRule {
	private $sRule;
	private $mValue;
	private $bIsImportant;
	
	public function __construct($sRule) {
		$this->sRule = $sRule;
		$this->mValue = null;
		$this->bIsImportant = false;
	}
	
	public function setRule($sRule) {
		$this->sRule = $sRule;
	}

	public function getRule() {
		return $this->sRule;
	}

	public function getValue() {
		return $this->mValue;
	}

	public function setValue($mValue) {
		$this->mValue = $mValue;
	}
	
	/**
	*	@deprecated Old-Style 2-dimensional array given. Retained for (some) backwards-compatibility. Use setValue() instead and wrapp the value inside a CSSRuleValueList if necessary.
	*/
	public function setValues($aSpaceSeparatedValues) {
		$oSpaceSeparatedList = null;
		if(count($aSpaceSeparatedValues) > 1) {
			$oSpaceSeparatedList = new CSSRuleValueList(' ');
		}
		foreach($aSpaceSeparatedValues as $aCommaSeparatedValues) {
			$oCommaSeparatedList = null;
			if(count($aCommaSeparatedValues) > 1) {
				$oCommaSeparatedList = new CSSRuleValueList(',');
			}
			foreach($aCommaSeparatedValues as $mValue) {
				if(!$oSpaceSeparatedList && !$oCommaSeparatedList) {
					$this->mValue = $mValue;
					return $mValue;
				}
				if($oCommaSeparatedList) {
					$oCommaSeparatedList->addListComponent($mValue);
				} else {
					$oSpaceSeparatedList->addListComponent($mValue);
				}
			}
			if(!$oSpaceSeparatedList) {
				$this->mValue = $oCommaSeparatedList;
				return $oCommaSeparatedList;
			} else {
				$oSpaceSeparatedList->addListComponent($oCommaSeparatedList);
			}
		}
		$this->mValue = $oSpaceSeparatedList;
		return $oSpaceSeparatedList;
	}

	/**
	*	@deprecated Old-Style 2-dimensional array returned. Retained for (some) backwards-compatibility. Use getValue() instead and check for the existance of a (nested set of) CSSValueList object(s).
	*/
	public function getValues() {
		if(!$this->mValue instanceof CSSRuleValueList) {
			return array(array($this->mValue));
		}
		if($this->mValue->getListSeparator() === ',') {
			return array($this->mValue->getListComponents());
		}
		$aResult = array();
		foreach($this->mValue->getListComponents() as $mValue) {
			if(!$mValue instanceof CSSRuleValueList || $mValue->getListSeparator() !== ',') {
				$aResult[] = array($mValue);
				continue;
			}
			if($this->mValue->getListSeparator() === ' ' || count($aResult) === 0) {
				$aResult[] = array();
			}
			foreach($mValue->getListComponents() as $mValue) {
				$aResult[count($aResult)-1][] = $mValue;
			}
		}
		return $aResult;
	}

	/**
	* Adds a value to the existing value. Value will be appended if a CSSRuleValueList exists of the given type. Otherwise, the existing value will be wrapped by one.
	*/
	public function addValue($mValue, $sType = ' ') {
		if(!is_array($mValue)) {
			$mValue = array($mValue);
		}
		if(!$this->mValue instanceof CSSRuleValueList || $this->mValue->getListSeparator() !== $sType) {
			$mCurrentValue = $this->mValue;
			$this->mValue = new CSSRuleValueList($sType);
			if($mCurrentValue) {
				$this->mValue->addListComponent($mCurrentValue);
			}
		}
		foreach($mValue as $mValueItem) {
			$this->mValue->addListComponent($mValueItem);
		}
	}
	
	public function setIsImportant($bIsImportant) {
		$this->bIsImportant = $bIsImportant;
	}

	public function getIsImportant() {
		return $this->bIsImportant;
	}
	
	public function __toString() {
		$sResult = "{$this->sRule}: ";
		if($this->mValue instanceof CSSValue) { //Can also be a CSSValueList
			$sResult .= $this->mValue->__toString();
		} else {
			$sResult .= $this->mValue;
		}
		if($this->bIsImportant) {
			$sResult .= ' !important';
		}
		$sResult .= ';';
		return $sResult;
	}
}
