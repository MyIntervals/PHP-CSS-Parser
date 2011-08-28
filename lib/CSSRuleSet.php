<?php

/**
* CSSRuleSet is a generic superclass denoting rules. The typical example for rule sets are declaration block.
* However, unknown At-Rules (like @font-face) are also rule sets.
*/
abstract class CSSRuleSet {
	private $aRules;
	
	public function __construct() {
		$this->aRules = array();
	}
	
	public function addRule(CSSRule $oRule) {
		$this->aRules[$oRule->getRule()] = $oRule;
	}
	
	public function getRules($mRule = null) {
		if($mRule === null) {
			return $this->aRules;
		}
		$aResult = array();
		if($mRule instanceof CSSRule) {
			$mRule = $mRule->getRule();
		}
		if(strrpos($mRule, '-')===strlen($mRule)-strlen('-')) {
			$sStart = substr($mRule, 0, -1);
			foreach($this->aRules as $oRule) {
				if($oRule->getRule() === $sStart || strpos($oRule->getRule(), $mRule) === 0) {
					$aResult[$oRule->getRule()] = $this->aRules[$oRule->getRule()];
				}
			}
		} else if(isset($this->aRules[$mRule])) {
			$aResult[$mRule] = $this->aRules[$mRule];
		}
		return $aResult;
	}
	
	public function removeRule($mRule) {
		if($mRule instanceof CSSRule) {
			$mRule = $mRule->getRule();
		}
		if(strrpos($mRule, '-')===strlen($mRule)-strlen('-')) {
			$sStart = substr($mRule, 0, -1);
			foreach($this->aRules as $oRule) {
				if($oRule->getRule() === $sStart || strpos($oRule->getRule(), $mRule) === 0) {
					unset($this->aRules[$oRule->getRule()]);
				}
			}
		} else if(isset($this->aRules[$mRule])) {
			unset($this->aRules[$mRule]);
		}
	}
	
	public function __toString() {
		$sResult = '';
		foreach($this->aRules as $oRule) {
			$sResult .= $oRule->__toString();
		}
		return $sResult;
	}
}

/**
* A CSSRuleSet constructed by an unknown @-rule. @font-face rules are rendered into CSSAtRule objects.
*/
class CSSAtRule extends CSSRuleSet {
	private $sType;
	
	public function __construct($sType) {
		parent::__construct();
		$this->sType = $sType;
	}
	
	public function __toString() {
		$sResult = "@{$this->sType} {";
		$sResult .= parent::__toString();
		$sResult .= '}';
		return $sResult;
	}
}

/**
* Declaration blocks are the parts of a css file which denote the rules belonging to a selector.
* Declaration blocks usually appear directly inside a CSSDocument or another CSSList (mostly a CSSMediaQuery).
*/
class CSSDeclarationBlock extends CSSRuleSet {
	private $aSelectors;

	public function __construct() {
		parent::__construct();
		$this->aSelectors = array();
	}

	public function setSelectors($mSelector) {
		if(is_array($mSelector)) {
			$this->aSelectors = $mSelector;
		} else {
			$this->aSelectors = explode(',', $mSelector);
		}
		foreach($this->aSelectors as $iKey => $mSelector) {
			if(!($mSelector instanceof CSSSelector)) {
				$this->aSelectors[$iKey] = new CSSSelector($mSelector);
			}
		}
	}
	
	/**
	* @deprecated use getSelectors()
	*/
	public function getSelector() {
		return $this->getSelectors();
	}
	
	/**
	* @deprecated use setSelectors()
	*/
	public function setSelector($mSelector) {
		$this->setSelectors($mSelector);
	}
	
	public function getSelectors() {
		return $this->aSelectors;
	}
	
	public function __toString() {
		$sResult = implode(', ', $this->aSelectors).' {';
		$sResult .= parent::__toString();
		$sResult .= '}';
		return $sResult;
	}
}
