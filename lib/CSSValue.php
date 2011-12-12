<?php

abstract class CSSValue {
	public abstract function __toString();
}

abstract class CSSPrimitiveValue extends CSSValue {
	
}

class CSSIgnoredValue extends CSSValue {
  private $mValue;
  public function __construct($mValue) {
    $this->mValue = $mValue;
  }
  public function getValue() {
    return $this->mValue;
  }
  public function __toString() {
    return $this->mValue->__toString();
  }
}

class CSSSize extends CSSPrimitiveValue {
	private $fSize;
	private $sUnit;
	private $bIsColorComponent;
	
	public function __construct($fSize, $sUnit = null, $bIsColorComponent = false) {
		$this->fSize = floatval($fSize);
		$this->sUnit = $sUnit;
		$this->bIsColorComponent = $bIsColorComponent;
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

	public function isColorComponent() {
		return $this->bIsColorComponent;
	}

	/**
	* Returns whether the number stored in this CSSSize really represents a size (as in a length of something on screen).
	* @return false if the unit an angle, a duration, a frequency or the number is a component in a CSSColor object.
	*/
	public function isSize() {
		$aNonSizeUnits = array('deg', 'grad', 'rad', 'turns', 's', 'ms', 'Hz', 'kHz');
		if(in_array($this->sUnit, $aNonSizeUnits)) {
			return false;
		}
		return !$this->isColorComponent();
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

class CSSString extends CSSPrimitiveValue {
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

class CSSURL extends CSSPrimitiveValue {
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

