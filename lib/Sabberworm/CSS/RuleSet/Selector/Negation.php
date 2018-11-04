<?php

namespace Sabberworm\CSS\RuleSet\Selector;

use Sabberworm\CSS\Property\Selector;

abstract class Negation extends SelectorPart {
	private $oNegated;

	public function __construct(Selector $oNegated, $iLineNo = 0) {
		parent::__construct($iLineNo);
		$this->oNegated = $oNegated;
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return ':not('.$this->oNegated->render($oOutputFormat).')';
	}
}
