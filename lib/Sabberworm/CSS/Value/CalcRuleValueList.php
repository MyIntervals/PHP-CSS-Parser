<?php

namespace Sabberworm\CSS\Value;

class CalcRuleValueList extends RuleValueList {

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return $oOutputFormat->implode($oOutputFormat->spaceBeforeCalcListArgumentSeparator($this->sSeparator) . $this->sSeparator . $oOutputFormat->spaceAfterCalcListArgumentSeparator($this->sSeparator), $this->aComponents);
	}

}
