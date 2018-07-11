<?php

namespace Sabberworm\CSS\Value;

class LineName extends PrimitiveValue {
	private $sName;

	public function __construct($sName, $iLineNo = 0) {
		parent::__construct($iLineNo);
		$this->sName = $sName;
	}

	public function setName($sName) {
		$this->sName = $sName;
	}

	public function getName() {
		return $this->sName;
	}

	public function __toString() {
		return $this->render(new \Sabberworm\CSS\OutputFormat());
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return "[{$this->sName}]";
	}

}
