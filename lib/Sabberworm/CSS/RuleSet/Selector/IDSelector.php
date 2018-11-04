<?php

namespace Sabberworm\CSS\RuleSet\Selector;

use Sabberworm\CSS\Renderable;

abstract class IDSelector extends SelectorPart {
	private $sId;

	public function __construct($sId = false, $iLineNo = 0) {
		parent::__construct($iLineNo);
		$this->sId = $sId;
	}

	public function getId() {
		return $this->sId;
	}

	public function setId($sId) {
		$this->sId = $sId;
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return '#' . $this->sId;
	}
}
