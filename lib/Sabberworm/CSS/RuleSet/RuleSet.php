<?php

namespace Sabberworm\CSS\RuleSet;

use Sabberworm\CSS\Rule\Rule;

/**
 * RuleSet is a generic superclass denoting rules. The typical example for rule sets are declaration block.
 * However, unknown At-Rules (like @font-face) are also rule sets.
 */
abstract class RuleSet {

	private $aRules;

	public function __construct() {
		$this->aRules = array();
	}

	public function addRule(Rule $oRule) {
		$this->aRules[$oRule->getRule()] = $oRule;
	}

	/**
	 * Returns all rules matching the given pattern
	 * @param (null|string|Rule) $mRule pattern to search for. If null, returns all rules. if the pattern ends with a dash, all rules starting with the pattern are returned as well as one matching the pattern with the dash excluded. passing a Rule behaves like calling getRules($mRule->getRule()).
	 * @example $oRuleSet->getRules('font-') //returns an array of all rules either beginning with font- or matching font.
	 * @example $oRuleSet->getRules('font') //returns array('font' => $oRule) or array().
	 */
	public function getRules($mRule = null) {
		if ($mRule === null) {
			return $this->aRules;
		}
		$aResult = array();
		if ($mRule instanceof Rule) {
			$mRule = $mRule->getRule();
		}
		if (strrpos($mRule, '-') === strlen($mRule) - strlen('-')) {
			$sStart = substr($mRule, 0, -1);
			foreach ($this->aRules as $oRule) {
				if ($oRule->getRule() === $sStart || strpos($oRule->getRule(), $mRule) === 0) {
					$aResult[$oRule->getRule()] = $this->aRules[$oRule->getRule()];
				}
			}
		} else if (isset($this->aRules[$mRule])) {
			$aResult[$mRule] = $this->aRules[$mRule];
		}
		return $aResult;
	}

	public function removeRule($mRule) {
		if ($mRule instanceof Rule) {
			$mRule = $mRule->getRule();
		}
		if (strrpos($mRule, '-') === strlen($mRule) - strlen('-')) {
			$sStart = substr($mRule, 0, -1);
			foreach ($this->aRules as $oRule) {
				if ($oRule->getRule() === $sStart || strpos($oRule->getRule(), $mRule) === 0) {
					unset($this->aRules[$oRule->getRule()]);
				}
			}
		} else if (isset($this->aRules[$mRule])) {
			unset($this->aRules[$mRule]);
		}
	}

	public function __toString() {
		$sResult = '';
		foreach ($this->aRules as $oRule) {
			$sResult .= $oRule->__toString();
		}
		return $sResult;
	}

}
