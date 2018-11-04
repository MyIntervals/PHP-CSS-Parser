<?php

namespace Sabberworm\CSS\RuleSet\Selector;

use Sabberworm\CSS\Renderable;

abstract class TypeSelector extends SelectorPart {
	private $sType;

	public function __construct($sType = false, $iLineNo = 0) {
		parent::__construct($iLineNo);
		$this->sType = $sType;
	}

	public function getType() {
		return $this->sType;
	}

	public function setType($sType) {
		$this->sType = $sType;
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return $this->sType;
	}
}
