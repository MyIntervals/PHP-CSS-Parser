<?php

/**
* CSSRuleSet is a generic superclass denoting rules. The typical example for rule sets are declaration block.
* However, unknown At-Rules (like @font-face) are also rule sets.
*/
abstract class CSSRuleSet {
	private $aRules;
	
	public function __construct() {
		$this->aRules = array();
	}
	
	public function addRule(CSSRule $oRule) {
		$this->aRules[$oRule->getRule()] = $oRule;
	}
	
	/**
	* Returns all rules matching the given pattern
	* @param (null|string|CSSRule) $mRule pattern to search for. If null, returns all rules. if the pattern ends with a dash, all rules starting with the pattern are returned as well as one matching the pattern with the dash excluded. passing a CSSRule behaves like calling getRules($mRule->getRule()).
	* @example $oRuleSet->getRules('font-') //returns an array of all rules either beginning with font- or matching font.
	* @example $oRuleSet->getRules('font') //returns array('font' => $oRule) or array().
	*/
	public function getRules($mRule = null) {
		if($mRule === null) {
			return $this->aRules;
		}
		$aResult = array();
		if($mRule instanceof CSSRule) {
			$mRule = $mRule->getRule();
		}
		if(strrpos($mRule, '-')===strlen($mRule)-strlen('-')) {
			$sStart = substr($mRule, 0, -1);
			foreach($this->aRules as $oRule) {
				if($oRule->getRule() === $sStart || strpos($oRule->getRule(), $mRule) === 0) {
					$aResult[$oRule->getRule()] = $this->aRules[$oRule->getRule()];
				}
			}
		} else if(isset($this->aRules[$mRule])) {
			$aResult[$mRule] = $this->aRules[$mRule];
		}
		return $aResult;
	}
	
	public function removeRule($mRule) {
		if($mRule instanceof CSSRule) {
			$mRule = $mRule->getRule();
		}
		if(strrpos($mRule, '-')===strlen($mRule)-strlen('-')) {
			$sStart = substr($mRule, 0, -1);
			foreach($this->aRules as $oRule) {
				if($oRule->getRule() === $sStart || strpos($oRule->getRule(), $mRule) === 0) {
					unset($this->aRules[$oRule->getRule()]);
				}
			}
		} else if(isset($this->aRules[$mRule])) {
			unset($this->aRules[$mRule]);
		}
	}
	
	public function __toString() {
		$sResult = '';
		foreach($this->aRules as $oRule) {
			$sResult .= $oRule->__toString();
		}
		return $sResult;
	}
}

/**
* A CSSRuleSet constructed by an unknown @-rule. @font-face rules are rendered into CSSAtRule objects.
*/
class CSSAtRule extends CSSRuleSet {
	private $sType;
	
	public function __construct($sType) {
		parent::__construct();
		$this->sType = $sType;
	}
	
	public function __toString() {
		$sResult = "@{$this->sType} {";
		$sResult .= parent::__toString();
		$sResult .= '}';
		return $sResult;
	}
}

/**
* Declaration blocks are the parts of a css file which denote the rules belonging to a selector.
* Declaration blocks usually appear directly inside a CSSDocument or another CSSList (mostly a CSSMediaQuery).
*/
class CSSDeclarationBlock extends CSSRuleSet {
	private $aSelectors;

	public function __construct() {
		parent::__construct();
		$this->aSelectors = array();
	}

	public function setSelectors($mSelector) {
		if(is_array($mSelector)) {
			$this->aSelectors = $mSelector;
		} else {
			$this->aSelectors = explode(',', $mSelector);
		}
		foreach($this->aSelectors as $iKey => $mSelector) {
			if(!($mSelector instanceof CSSSelector)) {
				$this->aSelectors[$iKey] = new CSSSelector($mSelector);
			}
		}
	}
	
	/**
	* @deprecated use getSelectors()
	*/
	public function getSelector() {
		return $this->getSelectors();
	}
	
	/**
	* @deprecated use setSelectors()
	*/
	public function setSelector($mSelector) {
		$this->setSelectors($mSelector);
	}
	
	public function getSelectors() {
		return $this->aSelectors;
	}

  /*
   * Split shorthand declarations (e.g. +margin+ or +font+) into their constituent parts.
   **/
  public function expandShorthands()
  {
    // border must be expanded before dimensions
    $this->expandBorderShorthand();
    $this->expandDimensionsShorthand();
    $this->expandFontShorthand();
    $this->expandBackgroundShorthand();
  }

  /*
   * Create shorthand declarations (e.g. +margin+ or +font+) whenever possible.
   **/
  public function createShorthands()
  {
    $this->createBackgroundShorthand();
    $this->createDimensionsShorthand();
    // border must be shortened after dimensions 
    $this->createBorderShorthand();
    $this->createFontShorthand();
  }

  /**
   * Split shorthand border declarations (e.g. <tt>border: 1px red;</tt>)
   * Additional splitting happens in expandDimensionsShorthand
   **/
  public function expandBorderShorthand()
  {
    $aBorderRules = array(
      'border', 'border-left', 'border-right', 'border-top', 'border-bottom' 
    );
    $aBorderSizes = array(
      'thin', 'medium', 'thick'
    );
    $aRules = $this->getRules();
    foreach ($aBorderRules as $sBorderRule)
    {
      if(!isset($aRules[$sBorderRule])) continue;
      $oRule = $aRules[$sBorderRule];
      foreach ($oRule->getValues() as $aValues)
      {
        // multiple borders are not yet supported as of CSS3
        $mValue = $aValues[0];
        if($mValue instanceof CSSValue)
        {
          $mNewValue = clone $mValue;
        }
        else
        {
          $mNewValue = $mValue;
        }
        if($mValue instanceof CSSSize)
        {
          $sNewRuleName = $sBorderRule."-width";
        }
        else if($mValue instanceof CSSColor)
        {
          $sNewRuleName = $sBorderRule."-color";
        }
        else
        {
          if(in_array($mValue, $aBorderSizes))
          {
            $sNewRuleName = $sBorderRule."-width";
          }
          else //if(in_array($mValue, $aBorderStyles))
          {
            $sNewRuleName = $sBorderRule."-style";
          }
        }
        $oNewRule = new CSSRule($sNewRuleName);
        $oNewRule->setIsImportant($oRule->getIsImportant());
        $oNewRule->addValue(array($mNewValue));
        $this->addRule($oNewRule);
      }
      $this->removeRule($sBorderRule);
    }
  }

  /**
   * Split shorthand dimensional declarations (e.g. <tt>margin: 0px auto;</tt>)
   * into their constituent parts.
   * Handles margin, padding, border-color, border-style and border-width.
   **/
  public function expandDimensionsShorthand()
  {
    $aExpansions = array(
      'margin'       => 'margin-%s',
      'padding'      => 'padding-%s',
      'border-color' => 'border-%s-color', 
      'border-style' => 'border-%s-style', 
      'border-width' => 'border-%s-width'
    );
    $aRules = $this->getRules();
    foreach ($aExpansions as $sProperty => $sExpanded)
    {
      if(!isset($aRules[$sProperty])) continue;
      $oRule = $aRules[$sProperty];
      $aValues = $oRule->getValues();
      $top = $right = $bottom = $left = null;
      switch(count($aValues))
      {
        case 1:
          $top = $right = $bottom = $left = $aValues[0];
          break;
        case 2:
          $top = $bottom = $aValues[0];
          $left = $right = $aValues[1];
          break;
        case 3:
          $top = $aValues[0];
          $left = $right = $aValues[1];
          $bottom = $aValues[2];
          break;
        case 4:
          $top = $aValues[0];
          $right = $aValues[1];
          $bottom = $aValues[2];
          $left = $aValues[3];
          break;
      }
      foreach(array('top', 'right', 'bottom', 'left') as $sPosition)
      {
        $oNewRule = new CSSRule(sprintf($sExpanded, $sPosition));
        $oNewRule->setIsImportant($oRule->getIsImportant());
        $oNewRule->addValue(${$sPosition});
        $this->addRule($oNewRule);
      }
      $this->removeRule($sProperty);
    }
  }

  /**
   * Convert shorthand font declarations
   * (e.g. <tt>font: 300 italic 11px/14px verdana, helvetica, sans-serif;</tt>)
   * into their constituent parts.
   **/
  public function expandFontShorthand()
  {
    $aRules = $this->getRules();
    if(!isset($aRules['font'])) return;
    $oRule = $aRules['font'];
    // reset properties to 'normal' per http://www.w3.org/TR/CSS21/fonts.html#font-shorthand
    $aFontProperties = array(
      'font-style' => 'normal', 'font-variant' => 'normal', 'font-weight' => 'normal',
      'font-size' => 'normal', 'line-height' => 'normal'
    );    
    foreach($oRule->getValues() as $aValues)
    { 
      $mValue = $aValues[0];
      if(!$mValue instanceof CSSValue)
      {
        $mValue = strtolower($mValue);
      }
      if(in_array($mValue, array('normal', 'inherit')))
      {
        foreach (array('font-style', 'font-weight', 'font-variant') as $sProperty)
        {
          if(!isset($aFontProperties[$sProperty]))
          {
            $aFontProperties[$sProperty] = $aValues;
          }
        }
      }
      else if(in_array($mValue, array('italic', 'oblique')))
      {
        $aFontProperties['font-style'] = $aValues;
      }
      else if($mValue == 'small-caps')
      {
        $aFontProperties['font-variant'] = $aValues;
      }
      else if(in_array($mValue, array('bold', 'bolder', 'lighter'))
        || ($mValue instanceof CSSSize
              && in_array($mValue->getSize(), range(100, 900, 100))
            )
      ){
        $aFontProperties['font-weight'] = $aValues;
      }
      else if($mValue instanceof CSSRuleValueList && $mValue->getListSeparator() === '/')
      {
				list($oSize, $oHeight) = $mValue->getListComponents();
				$aFontProperties['font-size'] = $oSize;
				$aFontProperties['line-height'] = $oHeight;
      }
      else if($mValue instanceof CSSSize && $mValue->getUnit() !== null)
      {
        $aFontProperties['font-size'] = $aValues;
      }
      else
      {
        $aFontProperties['font-family'] = $aValues;
      }
    }
    foreach ($aFontProperties as $sProperty => $aValues)
    {
      if(!is_array($aValues)) $aValues = array($aValues);
      $oNewRule = new CSSRule($sProperty);
      $oNewRule->setValues(array($aValues));
      $oNewRule->setIsImportant($oRule->getIsImportant());
      $this->addRule($oNewRule); 
    }
    $this->removeRule('font');
  }

  /*
   * Convert shorthand background declarations
   * (e.g. <tt>background: url("chess.png") gray 50% repeat fixed;</tt>)
   * into their constituent parts.
   * @see http://www.w3.org/TR/CSS21/colors.html#propdef-background
   **/
  public function expandBackgroundShorthand()
  {
    $aRules = $this->getRules();
    if(!isset($aRules['background'])) return;
    $oRule = $aRules['background'];
    $aBgProperties = array(
      'background-color' => array('transparent'), 'background-image' => array('none'),
      'background-repeat' => array('repeat'), 'background-attachment' => array('scroll'),
      'background-position' => array(new CSSSize(0, '%'), new CSSSize(0, '%'))
    );
    $aValuesList = $oRule->getValues();
    if(count($aValuesList) == 1 && $aValuesList[0][0] == 'inherit')
    {
      foreach ($aBgProperties as $sProperty => $aValues) {
        $oNewRule = new CSSRule($sProperty);
        $oNewRule->addValue(array('inherit'));
        $oNewRule->setIsImportant($oRule->getIsImportant());
        $this->addRule($oNewRule);
      }
      $this->removeRule('background');
      return;
    }
    $iNumBgPos = 0;
    foreach($aValuesList as $aValues)
    {
      $mValue = $aValues[0];
      if(!$mValue instanceof CSSValue)
      {
        $mValue = strtolower($mValue);
      }
      if ($mValue instanceof CSSURL)
      {
        $aBgProperties['background-image'] = $aValues;
      }
      else if($mValue instanceof CSSColor)
      {
        $aBgProperties['background-color'] = $aValues;
      }
      else if(in_array($mValue, array('scroll', 'fixed')))
      {
        $aBgProperties['background-attachment'] = $aValues;
      }
      else if(in_array($mValue, array('repeat','no-repeat', 'repeat-x', 'repeat-y')))
      {
        $aBgProperties['background-repeat'] = $aValues;
      }
      else if(in_array($mValue, array('left','center','right','top','bottom'))
        || $mValue instanceof CSSSize
      ){
        if($iNumBgPos == 0)
        {
          $aBgProperties['background-position'][0] = $mValue;
          $aBgProperties['background-position'][1] = 'center';
        }
        else
        {
          $aBgProperties['background-position'][$iNumBgPos] = $mValue;
        }
        $iNumBgPos++;
      }
    }
    foreach ($aBgProperties as $sProperty => $aValues) {
      $oNewRule = new CSSRule($sProperty);
      $oNewRule->setIsImportant($oRule->getIsImportant());
      $oNewRule->addValue($aValues);
      $this->addRule($oNewRule);
    }
    $this->removeRule('background');
  }

  public function createBackgroundShorthand()
  {
    $aProperties = array(
      'background-color', 'background-image', 'background-repeat', 
      'background-position', 'background-attachment'
    );
    $aRules = $this->getRules();
    $aNewValues = array();
    foreach($aProperties as $sProperty) {
      if(!isset($aRules[$sProperty])) continue;
      $oRule = $aRules[$sProperty];
      if(!$oRule->getIsImportant()) {
        foreach($aRules[$sProperty]->getValues() as $aValues) {
          $aNewValues[] = $aValues;
        }
        $this->removeRule($sProperty);
      }
    }
    if(count($aNewValues)) {
      $oNewRule = new CSSRule('background');
      foreach ($aNewValues as $mValue) {
        $oNewRule->addValue($mValue);  
      }
      $this->addRule($oNewRule);
    }
  }

  /**
   * Combine border-color, border-style and border-width into border
   * Should be run after create_dimensions_shorthand!
   *
   * TODO: this is extremely similar to createBackgroundShorthand and should be combined
   **/
  public function createBorderShorthand() {
    $aBorderRules = array(
      'border-width', 'border-style', 'border-color' 
    );
    $aRules = $this->getRules();
    $aNewValues = array();
    foreach ($aBorderRules as $sBorderRule) {
      if(!isset($aRules[$sBorderRule])) continue;
      $oRule = $aRules[$sBorderRule];
      if(!$oRule->getIsImportant()) {
        // Can't merge if multiple values !
        if(count($oRule->getValues()) > 1) continue;
        foreach($oRule->getValues() as $aValues) {
          $mValue = $aValues[0];
          if($mValue instanceof CSSValue) {
            $mNewValue = clone $mValue;
            $aNewValues[] = $mNewValue;
          }
          else {
            $aNewValues[] = $mValue;
          }
        }
      }
    }
    if(count($aNewValues)) {
      $oNewRule = new CSSRule('border');
      foreach($aNewValues as $mNewValue) {
        $oNewRule->addValue(array($mNewValue));
      }
      $this->addRule($oNewRule);
      foreach($aBorderRules as $sRuleName) {
        $this->removeRule($sRuleName);
      }
    }
  }

  /*
   * Looks for long format CSS dimensional properties
   * (margin, padding, border-color, border-style and border-width) 
   * and converts them into shorthand CSS properties.
   **/
  public function createDimensionsShorthand()
  {
    $aPositions = array('top', 'right', 'bottom', 'left');
    $aExpansions = array(
      'margin'       => 'margin-%s',
      'padding'      => 'padding-%s',
      'border-color' => 'border-%s-color', 
      'border-style' => 'border-%s-style', 
      'border-width' => 'border-%s-width'
    );
    $aRules = $this->getRules();
    foreach ($aExpansions as $sProperty => $sExpanded)
    {
      $aFoldable = array();
      foreach($aRules as $sRuleName => $oRule)
      {
        foreach ($aPositions as $sPosition)
        {
          if($sRuleName == sprintf($sExpanded, $sPosition))
          {
            $aFoldable[$sRuleName] = $oRule; 
          }
        }
      }
      // All four dimensions must be present
      if(count($aFoldable) == 4)
      {
        $aValues = array();
        foreach ($aPositions as $sPosition)
        {
          $aValuesList = $aRules[sprintf($sExpanded, $sPosition)]->getValues();
          $aValues[$sPosition] = $aValuesList[0];
        }
        $oNewRule = new CSSRule($sProperty);
        if((string)$aValues['left'][0] == (string)$aValues['right'][0])
        {
          if((string)$aValues['top'][0] == (string)$aValues['bottom'][0])
          {
            if((string)$aValues['top'][0] == (string)$aValues['left'][0])
            {
              // All 4 sides are equal
              $oNewRule->addValue($aValues['top']);
            }
            else
            {
              // Top and bottom are equal, left and right are equal
              $oNewRule->addValue($aValues['top']);
              $oNewRule->addValue($aValues['left']);
            }
          }
          else
          {
            // Only left and right are equal
            $oNewRule->addValue($aValues['top']);
            $oNewRule->addValue($aValues['left']);
            $oNewRule->addValue($aValues['bottom']);
          }
        }
        else
        {
          // No sides are equal 
          $oNewRule->addValue($aValues['top']);
          $oNewRule->addValue($aValues['left']);
          $oNewRule->addValue($aValues['bottom']);
          $oNewRule->addValue($aValues['right']);
        }
        $this->addRule($oNewRule);
        foreach ($aPositions as $sPosition)
        {
          $this->removeRule(sprintf($sExpanded, $sPosition));
        }
      }
    }
  }

  /**
   * Looks for long format CSS font properties (e.g. <tt>font-weight</tt>) and 
   * tries to convert them into a shorthand CSS <tt>font</tt> property. 
   * At least font-size AND font-family must be present in order to create a shorthand declaration.
   **/
  public function createFontShorthand()
  {
    $aFontProperties = array(
      'font-style', 'font-variant', 'font-weight', 'font-size', 'line-height', 'font-family'
    );
    $aRules = $this->getRules();
    if(!isset($aRules['font-size']) || !isset($aRules['font-family']))
    {
      return;
    }
    $oNewRule = new CSSRule('font');
    foreach(array('font-style', 'font-variant', 'font-weight') as $sProperty)
    {
      if(isset($aRules[$sProperty]))
      {
        $oRule = $aRules[$sProperty];
        $aValuesList = $oRule->getValues();
        if($aValuesList[0][0] !== 'normal')
        {
          $oNewRule->addValue($aValuesList[0]);
        }
      }
    }
    // Get the font-size value
    $aFSValues = $aRules['font-size']->getValues();
    // But wait to know if we have line-height to add it
    if(isset($aRules['line-height']))
    {
      $aLHValues = $aRules['line-height']->getValues();
      if($aLHValues[0][0] !== 'normal')
      {
				$val = new CSSRuleValueList('/');
				$val->addListComponent($aFSValues[0][0]);
				$val->addListComponent($aLHValues[0][0]);
        $oNewRule->addValue(array($val));
      }
    }
    else
    {
      $oNewRule->addValue($aFSValues[0]);
    }

    $aFFValues = $aRules['font-family']->getValues();
		$oFFValue = new CSSRuleValueList(',');
		$oFFValue->setListComponents($aFFValues[0]);
    $oNewRule->addValue($oFFValue);

    $this->addRule($oNewRule);
    foreach ($aFontProperties as $sProperty)
    {
      $this->removeRule($sProperty);
    }
  }
	
	public function __toString() {
		$sResult = implode(', ', $this->aSelectors).' {';
		$sResult .= parent::__toString();
		$sResult .= '}';
		return $sResult;
	}
}
