<?php

namespace Sabberworm\CSS\Value;

class CalcFunction extends CSSFunction {

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$aArguments = $oOutputFormat->implode($oOutputFormat->spaceBeforeCalcListArgumentSeparator($this->sSeparator) . $this->sSeparator . $oOutputFormat->spaceAfterCalcListArgumentSeparator($this->sSeparator), $this->aComponents);
		return "{$this->sName}({$aArguments})";
	}

}
