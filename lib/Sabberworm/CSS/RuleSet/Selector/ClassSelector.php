<?php

namespace Sabberworm\CSS\RuleSet\Selector;

use Sabberworm\CSS\Renderable;

abstract class ClassSelector extends SelectorPart {
	private $sClassName;

	public function __construct($sClassName = false, $iLineNo = 0) {
		parent::__construct($iLineNo);
		$this->sClassName = $sClassName;
	}

	public function getClassName() {
		return $this->sClassName;
	}

	public function setClassName($sClassName) {
		$this->sClassName = $sClassName;
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return '.' . $this->sClassName;
	}
}
