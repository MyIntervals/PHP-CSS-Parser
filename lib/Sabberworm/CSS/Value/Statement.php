<?php

namespace Sabberworm\CSS\Value;


class Statement extends PrimitiveValue {

	private $sStatement;

	public function __construct($sStatement = '', $iLineNo = 0) {
		parent::__construct($iLineNo);
		$this->sStatement = $sStatement;
	}

	public function setStatement($sStatement) {
		$this->sStatement = $sStatement;
	}

	public function getStatement() {
		return $this->sStatement;
	}

	public function __toString() {
		return $this->render(new \Sabberworm\CSS\OutputFormat());
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return $this->sStatement;
	}

}