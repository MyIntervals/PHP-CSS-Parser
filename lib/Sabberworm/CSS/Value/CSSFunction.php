<?php

namespace Sabberworm\CSS\Value;

class CSSFunction extends ValueList {

	private $sName;

	public function __construct($sName, $aArguments) {
		$this->sName = $sName;
		parent::__construct($aArguments);
	}

	public function getName() {
		return $this->sName;
	}

	public function setName($sName) {
		$this->sName = $sName;
	}

	public function getArguments() {
		return $this->aComponents;
	}

	public function __toString() {
		$aArguments = parent::__toString();
		return "{$this->sName}({$aArguments})";
	}

}