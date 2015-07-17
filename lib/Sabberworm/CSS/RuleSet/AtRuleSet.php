<?php

namespace Sabberworm\CSS\RuleSet;

use Sabberworm\CSS\Property\AtRule;

/**
 * A RuleSet constructed by an unknown @-rule. @font-face rules are rendered into AtRuleSet objects.
 */
class AtRuleSet extends RuleSet implements AtRule {

	protected $sType;
	protected $sArgs;

	public function __construct($sType, $sArgs = '') {
		parent::__construct();
		$this->sType = $sType;
		$this->sArgs = $sArgs;
	}

	public function atRuleName() {
		return $this->sType;
	}

	public function atRuleArgs() {
		return $this->sArgs;
	}

	public function __toString() {
		return $this->render(new \Sabberworm\CSS\OutputFormat());
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$sArgs = $this->sArgs;
		if($sArgs) {
			$sArgs = ' ' . $sArgs;
		}
		$sResult = "@{$this->sType}$sArgs{$oOutputFormat->spaceBeforeOpeningBrace()}{";
		$sResult .= parent::render($oOutputFormat);
		$sResult .= '}';
		return $sResult;
	}

}