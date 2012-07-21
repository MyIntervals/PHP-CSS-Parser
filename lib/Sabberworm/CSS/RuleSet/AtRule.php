<?php

namespace Sabberworm\CSS\RuleSet;

/**
 * A RuleSet constructed by an unknown @-rule. @font-face rules are rendered into AtRule objects.
 */
class AtRule extends RuleSet {

	private $sType;

	public function __construct($sType) {
		parent::__construct();
		$this->sType = $sType;
	}

	public function getType() {
		return $this->sType;
	}

	public function __toString() {
		$sResult = "@{$this->sType} {";
		$sResult .= parent::__toString();
		$sResult .= '}';
		return $sResult;
	}

}