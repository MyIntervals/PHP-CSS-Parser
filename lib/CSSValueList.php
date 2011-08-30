<?php

abstract class CSSValueList extends CSSValue {
	protected $aComponents;
	protected $sSeparator;
	
	public function __construct($aComponents = array(), $sSeparator = ',') {
		if($aComponents instanceof CSSValueList && $aComponents->getListSeparator() === $sSeparator) {
			$aComponents = $aComponents->getListComponents();
		} else if(!is_array($aComponents)) {
			$aComponents = array($aComponents);
		}
		$this->aComponents = $aComponents;
		$this->sSeparator = $sSeparator;
	}

	public function addListComponent($mComponent) {
		$this->aComponents[] = $mComponent;
	}

	public function getListComponents() {
		return $this->aComponents;
	}

	public function setListComponents($aComponents) {
		$this->aComponents = $aComponents;
	}
	
	public function getListSeparator() {
		return $this->sSeparator;
	}

	public function setListSeparator($sSeparator) {
		$this->sSeparator = $sSeparator;
	}

	function __toString() {
		return implode($this->sSeparator, $this->aComponents);
	}
}

class CSSRuleValueList extends CSSValueList {
	public function __construct($sSeparator = ',') {
		parent::__construct(array(), $sSeparator);
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

class CSSColor extends CSSFunction {
	public function __construct($aColor) {
		parent::__construct(implode('', array_keys($aColor)), $aColor);
	}
	
	public function getColor() {
		return $this->aComponents;
	}

	public function setColor($aColor) {
		$this->setName(implode('', array_keys($aColor)));
		$this->aComponents = $aColor;
	}
	
	public function getColorDescription() {
		return $this->getName();
	}
}


