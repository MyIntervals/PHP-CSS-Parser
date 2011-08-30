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
	protected $sName;
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
    if(!is_array($aColor))
    {
      $aRGB = ColorUtils::hex2rgb($aColor);
      $aColor = array(
        'r' => new CSSSize($aRGB['r'], null, true),
        'g' => new CSSSize($aRGB['g'], null, true),
        'b' => new CSSSize($aRGB['b'], null, true)
      );
    }
		parent::__construct(implode('', array_keys($aColor)), $aColor);
	}
	
	public function getColor() {
		return $this->aComponents;
	}
	
	public function getColorDescription() {
		return $this->getName();
	}

  public function toRGB()
  {
    $name = $this->getName();
    $aComponents = $this->aComponents;
    
    if($name == 'rgb') return;
    if($name == 'rgba')
    {
      // If we don't need alpha channel, drop it
      if($aComponents['a']->getSize() >= 1)
      {
        unset($this->aComponents['a']);
        $this->sName = 'rgb';
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
    foreach($aRGB as $key => $val)
    {
      $this->aComponents[$key] = new CSSSize($val, null, true);
    }
    $this->sName = 'rgb';
    // If we don't need alpha channel, drop it
    if($aRGB['a'] < 1)
    {
      $this->sName .= 'a';
    }
    else
    {
      unset($this->aComponents['a']);
    }
  }

  public function toHSL()
  {
    $name = $this->getName();
    $aComponents = $this->aComponents;
    if($name == 'hsl') return;
    if($name == 'hsla')
    {
      // If we don't need alpha channel, drop it
      if($aComponents['a']->getSize() >= 1)
      {
        unset($this->aComponents['a']);
        $this->sName = 'hsl';
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
    $this->sName = 'hsl';
    // If we don't need alpha channel, drop it
    if($aHSL['a'] < 1)
    {
      $this->aComponents['a'] = new CSSSize($aHSL['a'], null, true);
      $this->sName .= 'a';
    }
  }

  public function getNamedColor()
  {
    $this->toRGB();
    $aComponents = $this->aComponents;
    if(isset($aComponents['a']))
    {
      if($aComponents['a'] !== 1)
      {
        return null;
      }
      else if($aComponents['r']->getSize() == 0
              && $aComponents['g']->getSize() == 0
              && $aComponents['b']->getSize() == 0
      ){
        return 'transparent';
      }
    }
    return ColorUtils::rgb2NamedColor(
      $aComponents['r']->getSize(),
      $aComponents['g']->getSize(),
      $aComponents['b']->getSize()
    );
  }

  public function getHexValue()
  {
    $aComponents = $this->aComponents;
    if(isset($aComponents['a']) && $aComponents['a']->getSize() !== 1) return null;
    $name = $this->getName();
    if($name == 'rgb')
    {
      return ColorUtils::rgb2hex(
        $aComponents['r']->getSize(),
        $aComponents['g']->getSize(),
        $aComponents['b']->getSize()
      );
    }
    else if($name == 'hsl')
    {
      return ColorUtils::hsl2hex(
        $aComponents['h']->getSize(),
        $aComponents['s']->getSize(),
        $aComponents['l']->getSize()
      );
    }
  }
}


