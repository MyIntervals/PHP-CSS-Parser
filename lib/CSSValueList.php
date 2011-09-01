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

  public function __construct($mColor=null) {
		parent::__construct('rgb', null);
    if(is_array($mColor)) {
      if(isset($mColor['r'], $mColor['g'], $mColor['b'])) {
        $this->fromRGB($mColor);
      }
      else if(isset($mColor['h'], $mColor['s'], $mColor['l'])) {
        $this->fromHSL($mColor);
      }
    }
    else if(is_string($mColor)) {
      if($aRGB = ColorUtils::namedColor2rgb($mColor)) {
        $this->fromRGB($aRGB);
      }
      else if($aRGB = ColorUtils::hex2rgb($mColor)) {
        $this->fromRGB($aRGB);
      }
    }
	}

  public function fromRGB(Array $aRGB) {
    $this->aComponents = array();
    $sName = 'rgb';
    foreach(array('r', 'g', 'b', 'a') as $sChannel) {
			if($sChannel == 'a') {
				if(!isset($aRGB['a'])) continue;
				$sValue = ColorUtils::constrainValue((string)$aRGB['a'], 0, 1);
				if($sValue == 1) continue;
				$sName .= 'a';
			}
			else {
				$sValue = ColorUtils::normalizeRGBValue((string)$aRGB[$sChannel]);
			}
      $this->aComponents[$sChannel] = new CSSSize($sValue, null, true);
    }
		$this->setName($sName);
    return $this;
  }

  public function fromHSL(Array $aHSL) {
    $aRGB = ColorUtils::hsl2rgb(
			(string)$aHSL['h'],
			(string)$aHSL['s'],
			(string)$aHSL['l'],
      isset($aHSL['a']) ? (string)$aHSL['a'] : 1
    );
    return $this->fromRGB($aRGB);
  }

  public function fromHex($sValue) {
    $aRGB = ColorUtils::hex2rgb($sValue);
    return $this->fromRGB($aRGB);
  }

  public function fromNamedColor($sValue) {
    $aRGB = ColorUtils::namedColor2rgb($sValue);
    return $this->fromRGB($aRGB);
  }

	public function getColor() {
		return $this->aComponents;
	}
	
	public function getColorDescription() {
		return $this->getName();
	}

  public function toRGB() {
    $sName = $this->getName();
    $aComponents = $this->aComponents;
    
    if(!$sName || $sName == 'rgb') return;
    if($sName == 'rgba') {
      // If we don't need alpha channel, drop it
      if($aComponents['a']->getSize() >= 1) {
        unset($this->aComponents['a']);
        $this->setName('rgb');
      }
      return;
    }
    $aRGB = ColorUtils::hsl2rgb(
      $aComponents['h']->getSize(),
      $aComponents['s']->getSize(),
      $aComponents['l']->getSize(),
      isset($aComponents['a']) ? $aComponents['a']->getSize() : 1
    );
		
    $this->aComponents = array();
    foreach($aRGB as $key => $val) {
      $this->aComponents[$key] = new CSSSize($val, null, true);
    }
    $sName = isset($aRGB['a']) ? 'rgba' : 'rgb';
		$this->setName($sName);
    return $this;
  }

  public function toHSL() {
    $sName = $this->getName();
    $aComponents = $this->aComponents;
    if(!$sName || $sName == 'hsl') return;
    if($sName == 'hsla') {
      // If we don't need alpha channel, drop it
      if($aComponents['a']->getSize() >= 1) {
        unset($this->aComponents['a']);
        $this->setName('hsl');
      }
      return;
    }
    $aHSL = ColorUtils::rgb2hsl(
      $aComponents['r']->getSize(),
      $aComponents['g']->getSize(),
      $aComponents['b']->getSize(),
      isset($aComponents['a']) ? $aComponents['a']->getSize() : 1
    );
    $this->aComponents = array();
    $this->aComponents['h'] = new CSSSize($aHSL['h'], null, true);
    $this->aComponents['s'] = new CSSSize($aHSL['s'], '%', true);
    $this->aComponents['l'] = new CSSSize($aHSL['l'], '%', true);
		$sName = 'hsl';
    if(isset($aHSL['a'])) {
      $this->aComponents['a'] = new CSSSize($aHSL['a'], null, true);
      $sName = 'hsla';
		}
		$this->setName($sName);
    return $this;
  }

  public function getNamedColor() {
    $this->toRGB();
    $aComponents = $this->aComponents;
    return ColorUtils::rgb2NamedColor(
      $aComponents['r']->getSize(),
      $aComponents['g']->getSize(),
      $aComponents['b']->getSize()
    );
  }

  public function getHexValue() {
    $aComponents = $this->aComponents;
    if(isset($aComponents['a']) && $aComponents['a']->getSize() !== 1) return null;
    $sName = $this->getName();
    if($sName == 'rgb') {
      return ColorUtils::rgb2hex(
        $aComponents['r']->getSize(),
        $aComponents['g']->getSize(),
        $aComponents['b']->getSize()
      );
    }
    else if($sName == 'hsl') {
      return ColorUtils::hsl2hex(
        $aComponents['h']->getSize(),
        $aComponents['s']->getSize(),
        $aComponents['l']->getSize()
      );
    }
  }
}
