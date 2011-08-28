<?php

abstract class CSSValue {
	public abstract function __toString();
}

class CSSSize extends CSSValue {
	private $fSize;
	private $sUnit;
	
	public function __construct($fSize, $sUnit = null) {
		$this->fSize = floatval($fSize);
		$this->sUnit = $sUnit;
	}
	
	public function setUnit($sUnit) {
			$this->sUnit = $sUnit;
	}

	public function getUnit() {
			return $this->sUnit;
	}
	
	public function setSize($fSize) {
			$this->fSize = floatval($fSize);
	}

	public function getSize() {
			return $this->fSize;
	}
	
	public function isRelative() {
		if($this->sUnit === '%' || $this->sUnit === 'em' || $this->sUnit === 'ex') {
			return true;
		}
		if($this->sUnit === null && $this->fSize != 0) {
			return true;
		}
		return false;
	}
	
	public function __toString() {
		return $this->fSize.($this->sUnit === null ? '' : $this->sUnit);
	}
}

class CSSColor extends CSSValue {
	private $aColor;
	
	public function __construct($aColor) {
		$this->aColor = $aColor;
	}
	
	public function setColor($aColor) {
			$this->aColor = $aColor;
	}

	public function getColor() {
			return $this->aColor;
	}
	
	public function getColorDescription() {
		return implode('', array_keys($this->aColor));
	}
	
	public function __toString() {
		return $this->getColorDescription().'('.implode(', ', $this->aColor).')';
	}
}

class CSSString extends CSSValue {
	private $sString;
	
	public function __construct($sString) {
		$this->sString = $sString;
	}
	
	public function setString($sString) {
			$this->sString = $sString;
	}

	public function getString() {
			return $this->sString;
	}
	
	public function __toString() {
		$sString = addslashes($this->sString);
		$sString = str_replace("\n", '\A', $sString);
		return '"'.$sString.'"';
	}
}

class CSSURL extends CSSValue {
	private $oURL;
	
	public function __construct(CSSString $oURL) {
		$this->oURL = $oURL;
	}
	
	public function setURL(CSSString $oURL) {
			$this->oURL = $oURL;
	}

	public function getURL() {
			return $this->oURL;
	}
	
	public function __toString() {
		return "url({$this->oURL->__toString()})";
	}
}

class CSSSlashedValue extends CSSValue {
	private $oValue1;
	private $oValue2;

	public function __construct($oValue1, $oValue2) {
		$this->oValue1 = $oValue1;
		$this->oValue2 = $oValue2;
	}

	public function getValue1() {
		return $this->oValue1;
	}

	public function getValue2() {
		return $this->oValue2;
	}

	public function __toString() {
		$oValue1 = $this->oValue1;
		$oValue2 = $this->oValue2;
		if($oValue1 instanceof CSSValue) {
			$oValue1 = $oValue1->__toString();
		}
		if($oValue2 instanceof CSSValue) {
			$oValue2 = $oValue2->__toString();
		}
		return "$oValue1/$oValue2";
	}
}

class CSSFunction extends CSSValue {
	private $sName;
	private $aContents;

	public function __construct($sName, $aContents) {
		$this->sName = $sName;
		$this->aContents = $aContents;
	}

	public function getName() {
		return $this->sName;
	}

	public function getContents() {
		return $this->aContents;
	}

	public function __toString() {
		$sContents = implode(',', $this->aContents);
		return "{$this->sName}({$sContents})";
	}
}
