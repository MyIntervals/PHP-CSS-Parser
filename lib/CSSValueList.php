<?php

abstract class CSSValueList extends CSSValue {
	protected $aComponents;
	protected $sSeparator;
	
	public function __construct($aComponents = array(), $sSeparator = ',') {
		$this->aComponents = $aComponents;
		$this->sSeparator = $sSeparator;
	}

	public function getListComponents() {
		return $this->aComponents;
	}
	
	public function getListSeparator() {
		return $this->sSeparator;
	}

	function __toString() {
		return implode($this->sSeparator, $this->aComponents);
	}
}

class CSSSlashedValue extends CSSValueList {
	public function __construct($oValue1, $oValue2) {
		parent::__construct(array($oValue1, $oValue2), '/');
	}

	public function getValue1() {
		return $this->aComponents[0];
	}

	public function getValue2() {
		return $this->aComponents[1];
	}
}

class CSSFunction extends CSSValueList {
	private $sName;
	public function __construct($sName, $aArguments) {
		$this->sName = $sName;
		parent::__construct($aArguments);
	}

	public function getName() {
		return $this->sName;
	}

	public function getArguments() {
		return $this->aComponents;
	}

	public function __toString() {
		$aArguments = parent::__toString();
		return "{$this->sName}({$aArguments})";
	}
}

class CSSColor extends CSSFunction {
	public function __construct($aColor) {
		parent::__construct(implode('', array_keys($aColor)), $aColor);
	}
	
	public function getColor() {
		return $this->aComponents;
	}
	
	public function getColorDescription() {
		return $this->getName();
	}
}


