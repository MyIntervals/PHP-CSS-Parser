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

	public function hasRule($sRuleName) {
		foreach($this->aRules as $oRule) {
			if($sRuleName == $oRule->getRules()) return true;
		}
		return false;
	}
	
	public function addRule(CSSRule $oRule) {
		$this->aRules[] = $oRule;
	}

	/**
	 * Adds each rule of given array after the specified rule.
	 *
	 * @param array   $aRules The rules to add
	 * @param CSSRule $oRule  The CSSRule after which rules will be added.
	 **/
	public function addRulesAfter(array $aRules, CSSRule $oRule) {
		$index = array_search($oRule, $this->aRules);
		array_splice($this->aRules, $index, 0, $aRules);
	}

	/**
	 * Adds each rule of given array before the specified rule.
	 *
	 * @param array   $aRules The rules to add
	 * @param CSSRule $oRule  The CSSRule before which rules will be added.
	 **/
	public function addRulesBefore(array $aRules, CSSRule $oRule) {
		$index = array_search($oRule, $this->aRules);
		array_splice($this->aRules, $index-1, 0, $aRules);
	}

	/**
	 * Replaces a rule with all rules in an array.
	 *
	 * @param array   $aRules The rules to add
	 * @param CSSRule $oRule  The CSSRule to replace
	 **/
	public function replaceRule(array $aRules, CSSRule $oRule) {
		$index = array_search($oRules, $this->aRules);
		array_splice($this->aRules, $index, 1, $aRules);
	}

	/**
	 * Get the position of a rule in the rule set.
	 * @param string $sRuleName The name of a rule
	 *
	 * @return integer The position of the rule or false if it is not found; 
	 **/
	public function getRulePosition($sRuleName) {
		return array_search($sRuleName, array_keys($this->aRules));
	}
	
	/**
	* Returns all rules matching the given pattern
	* @param (null|string|CSSRule) $mRule     Pattern to search for. If null, returns all rules.
	* @param boolean               $bWildcard If true, all rules starting with the pattern are returned. If false only rules wich strictly match the pattern.
	*
	* @example $oRuleSet->getRules('font', true) //returns an array of all rules beginning with font.
	* @example $oRuleSet->getRules('font') //returns array([index] => $oRule) or empty array().
	*/
	public function getRules($mRule = null, $bWildcard=false) {
		if(!$mRule) return $this->aRules;
		if($mRule instanceof CSSRule) $mRule = $mRule->getRule();
		$aResult = array();
		foreach($this->aRules as $iPos => $oRule) {
			if($bWildcard) {
				if(strpos($oRule->getRule(), $mRule) === 0) $aResult[$iPos] = $oRule;
			} else {
				if($oRule->getRule() == $mRule) $aResult[$iPos] = $oRule;
			}
		}
		return $aResult;
	}
	
	public function removeRule($mRule, $bWildcard=false) {
		if($mRule instanceof CSSRule) {
			$index = array_search($mRule, $this->aRules);
			unset($this->aRules[$index]);
		} else {
			foreach($this->aRules as $iPos => $oRule) {
				if($bWildcard) {
					if(strpos($oRule->getRule(), $mRule) === 0) unset($this->aRules[$iPos]);
				} else {
					if($oRule->getRule() == $mRule) unset($this->aRules[$iPos]);
				}
			}
		}
	}
	
	public function __toString() {
		$sResult = '';
		foreach($this->aRules as $oRule) {
			$sResult .= $oRule->__toString();
		}
		return $sResult;
	}

	protected function filterRulesByName($sName, $bStrict=true) {
		
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

  /**
   * Split shorthand declarations (e.g. +margin+ or +font+) into their constituent parts.
   **/
  public function expandShorthands() {
    // border must be expanded before dimensions
    $this->expandBorderShorthand();
    $this->expandDimensionsShorthand();
    $this->expandFontShorthand();
    $this->expandBackgroundShorthand();
		$this->expandListStyleShorthand();
  }

  /**
   * Create shorthand declarations (e.g. +margin+ or +font+) whenever possible.
   **/
  public function createShorthands() {
    $this->createBackgroundShorthand();
    $this->createDimensionsShorthand();
    // border must be shortened after dimensions 
    $this->createBorderShorthand();
    $this->createFontShorthand();
		$this->createListStyleShorthand();
  }

  /**
   * Split shorthand border declarations (e.g. <tt>border: 1px red;</tt>)
   * Additional splitting happens in expandDimensionsShorthand
   * Multiple borders are not yet supported as of CSS3
   **/
  public function expandBorderShorthand() {
    $aBorderRules = array(
      'border', 'border-left', 'border-right', 'border-top', 'border-bottom' 
    );
    $aBorderSizes = array(
      'thin', 'medium', 'thick'
    );
    foreach ($aBorderRules as $sBorderRule) {
			$aRules = $this->getRules($sBorderRule);
			if(empty($aRules)) continue;
			foreach($aRules as $iPos => $oRule) {
				$mRuleValue = $oRule->getValue();
				$aValues = array();
				if(!$mRuleValue instanceof CSSRuleValueList) {
					$aValues[] = $mRuleValue;
				} else {
					$aValues = $mRuleValue->getListComponents();
				}
				foreach ($aValues as $mValue) {
					if(!$mValue instanceof CSSValue) {
						$mValue = mb_strtolower($mValue);
					}
					if($mValue instanceof CSSSize) {
						$sNewRuleName = $sBorderRule."-width";
					} else if($mValue instanceof CSSColor) {
						$sNewRuleName = $sBorderRule."-color";
					} else {
						if(in_array($mValue, $aBorderSizes)) {
							$sNewRuleName = $sBorderRule."-width";
						} else/* if(in_array($mValue, $aBorderStyles))*/ {
							$sNewRuleName = $sBorderRule."-style";
						}
					}
					$this->addRuleExpansion($iPos, $oRule, $sNewRuleName, $mValue);
				}
				$this->removeRule($oRule);
			} // end foreach $oRules
    } // end foreach $aBorderRules
  }

  /**
   * Split shorthand dimensional declarations (e.g. <tt>margin: 0px auto;</tt>)
   * into their constituent parts.
   * Handles margin, padding, border-color, border-style and border-width.
   **/
  public function expandDimensionsShorthand() {
    $aExpansions = array(
      'margin'       => 'margin-%s',
      'padding'      => 'padding-%s',
      'border-color' => 'border-%s-color', 
      'border-style' => 'border-%s-style', 
      'border-width' => 'border-%s-width'
    );
    $aRules = $this->getRules();
    foreach ($aExpansions as $sProperty => $sExpanded) {
			$aRules = $this->getRules($sProperty);
			if(empty($aRules)) continue;
			foreach($aRules as $iPos => $oRule) {
				$mRuleValue = $oRule->getValue();
				$aValues = array();
				if(!$mRuleValue instanceof CSSRuleValueList) {
					$aValues[] = $mRuleValue;
				} else {
					$aValues = $mRuleValue->getListComponents();
				}
				$top = $right = $bottom = $left = null;
				switch(count($aValues)) {
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
				foreach(array('top', 'right', 'bottom', 'left') as $sPosition) {
					$sNewRuleName = sprintf($sExpanded, $sPosition);
					$mValue = ${$sPosition};
					$this->addRuleExpansion($iPos, $oRule, $sNewRuleName, $mValue);
				}
				$this->removeRule($oRule);
			}
    }
  }

  /**
   * Convert shorthand font declarations
   * (e.g. <tt>font: 300 italic 11px/14px verdana, helvetica, sans-serif;</tt>)
   * into their constituent parts.
   **/
  public function expandFontShorthand() {
    $aRules = $this->getRules('font');
    if(empty($aRules)) return;
		foreach($aRules as $iPos => $oRule) {
			// reset properties to 'normal' per http://www.w3.org/TR/CSS21/fonts.html#font-shorthand
			$aFontProperties = array(
				'font-style'   => 'normal',
				'font-variant' => 'normal',
				'font-weight'  => 'normal',
				'font-size'    => 'normal',
				'line-height'  => 'normal'
			);    
			$mRuleValue = $oRule->getValue();
			$aValues = array();
			if(!$mRuleValue instanceof CSSRuleValueList) {
				$aValues[] = $mRuleValue;
			} else {
				$aValues = $mRuleValue->getListComponents();
			}
			foreach($aValues as $mValue) { 
				if(!$mValue instanceof CSSValue) {
					$mValue = mb_strtolower($mValue);
				}
				if(in_array($mValue, array('normal', 'inherit'))) {
					foreach(array('font-style', 'font-weight', 'font-variant') as $sProperty) {
						if(!isset($aFontProperties[$sProperty])) {
							$aFontProperties[$sProperty] = $mValue;
						}
					}
				} else if(in_array($mValue, array('italic', 'oblique'))) {
					$aFontProperties['font-style'] = $mValue;
				} else if($mValue == 'small-caps') {
					$aFontProperties['font-variant'] = $mValue;
				} else if(
					in_array($mValue, array('bold', 'bolder', 'lighter'))
					|| ($mValue instanceof CSSSize
							&& in_array($mValue->getSize(), range(100, 900, 100)))
				) {
					$aFontProperties['font-weight'] = $mValue;
				} else if($mValue instanceof CSSRuleValueList && $mValue->getListSeparator() == '/') {
					list($oSize, $oHeight) = $mValue->getListComponents();
					$aFontProperties['font-size'] = $oSize;
					$aFontProperties['line-height'] = $oHeight;
				} else if($mValue instanceof CSSSize && $mValue->getUnit() !== null) {
					$aFontProperties['font-size'] = $mValue;
				} else {
					$aFontProperties['font-family'] = $mValue;
				}
			}
			foreach ($aFontProperties as $sProperty => $mValue) {
				$this->addRuleExpansion($iPos, $oRule, $sProperty, $mValue);
			}
			$this->removeRule($oRule);
		}
  }

  /*
   * Convert shorthand background declarations
   * (e.g. <tt>background: url("chess.png") gray 50% repeat fixed;</tt>)
   * into their constituent parts.
   * @see http://www.w3.org/TR/CSS21/colors.html#propdef-background
   **/
  public function expandBackgroundShorthand() {
    $aRules = $this->getRules('background');
    if(empty($aRules)) return;
		foreach($aRules as $iPos => $oRule) {
			$aBgProperties = array(
				'background-color'      => array('transparent'),
				'background-image'      => array('none'),
				'background-repeat'     => array('repeat'),
				'background-attachment' => array('scroll'),
				'background-position'   => array(new CSSSize(0, '%'), new CSSSize(0, '%'))
			);
			$mRuleValue = $oRule->getValue();
			$aValues = array();
			if(!$mRuleValue instanceof CSSRuleValueList) {
				$aValues[] = $mRuleValue;
			} else {
				$aValues = $mRuleValue->getListComponents();
			}
			if(count($aValues) == 1 && $aValues[0] == 'inherit') {
				foreach ($aBgProperties as $sProperty => $mValue) {
					$this->addRuleExpansion($iPos, $oRule, $sProperty, 'inherit');
				}
				$this->removeRule($oRule);
				return;
			}
			$iNumBgPos = 0;
			foreach($aValues as $mValue) {
				if(!$mValue instanceof CSSValue) {
					$mValue = mb_strtolower($mValue);
				}
				if ($mValue instanceof CSSURL) {
					$aBgProperties['background-image'] = $mValue;
				} else if($mValue instanceof CSSColor) {
					$aBgProperties['background-color'] = $mValue;
				} else if(in_array($mValue, array('scroll', 'fixed'))) {
					$aBgProperties['background-attachment'] = $mValue;
				} else if(in_array($mValue, array('repeat','no-repeat', 'repeat-x', 'repeat-y'))) {
					$aBgProperties['background-repeat'] = $mValue;
				} else if(in_array($mValue, array('left','center','right','top','bottom'))
						|| $mValue instanceof CSSSize
				){
					if($iNumBgPos == 0) {
						$aBgProperties['background-position'][0] = $mValue;
						$aBgProperties['background-position'][1] = 'center';
					} else {
						$aBgProperties['background-position'][$iNumBgPos] = $mValue;
					}
					$iNumBgPos++;
				}
			}
			foreach ($aBgProperties as $sProperty => $mValue) {
				$this->addRuleExpansion($iPos, $oRule, $sProperty, $mValue);
			}
			$this->removeRule($oRule);
		}
  }

	public function expandListStyleShorthand() {
		$aListStyleTypes = array(
			'none', 'disc', 'circle', 'square', 'decimal-leading-zero', 'decimal',
			'lower-roman', 'upper-roman', 'lower-greek', 'lower-alpha', 'lower-latin',
			'upper-alpha', 'upper-latin', 'hebrew', 'armenian', 'georgian', 'cjk-ideographic',
			'hiragana', 'hira-gana-iroha', 'katakana-iroha', 'katakana'	
		);
		$aListStylePositions = array(
			'inside', 'outside'
		);
    $aRules = $this->getRules('list-style');
		if(empty($aRules)) return;
		foreach($aRules as $iPos => $oRule) {
			$aListProperties = array(
				'list-style-type'     => 'disc',
				'list-style-position' => 'outside',
				'list-style-image'    => 'none'
			);
			$mRuleValue = $oRule->getValue();
			$aValues = array();
			if(!$mRuleValue instanceof CSSRuleValueList) {
				$aValues[] = $mRuleValue;
			} else {
				$aValues = $mRuleValue->getListComponents();
			}
			if(count($aValues) == 1 && $aValues[0] == 'inherit') {
				foreach ($aListProperties as $sProperty => $mValue) {
					$this->addRuleExpansion($iPos, $oRule, $sProperty, 'inherit');
				}
				$this->removeRule($oRule);
				return;
			}
			foreach($aValues as $mValue) {
				if(!$mValue instanceof CSSValue) {
					$mValue = mb_strtolower($mValue);
				}
				if($mValue instanceof CSSUrl) {
					$aListProperties['list-style-image'] = $mValue;
				} else if(in_array($mValue, $aListStyleTypes)) {
					$aListProperties['list-style-types'] = $mValue;
				} else if(in_array($mValue, $aListStylePositions)) {
					$aListProperties['list-style-position'] = $mValue;
				}
			}
			foreach ($aListProperties as $sProperty => $mValue) {
				$this->addRuleExpansion($iPos, $oRule, $sProperty, $mValue);
			}
			$this->removeRule($oRule);
		}
	}

	protected function addRuleExpansion($iOriginalRulePosition, $oOriginalRule, $sNewRuleName, $mValue) {
		$aExpandedRules = $this->getRules($sNewRuleName);
		// Don't add if a rule already exists with the same name
		// and it comes after the un-expanded one
		if(!empty($aExpandedRules)) {
			foreach($aExpandedRules as $iNewRulePosition => $oExpRule) {
				if($iNewRulePosition > $iOriginalRulePosition) return;
			}
		}
		$oNewRule = new CSSRule($sNewRuleName);
		$oNewRule->setIsImportant($oOriginalRule->getIsImportant());
		$oNewRule->addValue($mValue);
		$this->addRulesAfter(array($oNewRule), $oOriginalRule);
	}

	protected function createShorthandProperties(array $aProperties, $sShorthand) {
    $aNewValues = array();
    foreach($aProperties as $sProperty) {
			$aRules = $this->getRules($sProperty);
      if(empty($aRules)) continue;
			foreach($aRules as $iPos => $oRule) {
				if(!$oRule->getIsImportant()) {
					$mRuleValue = $oRule->getValue();
					$aValues = array();
					if(!$mRuleValue instanceof CSSRuleValueList) {
						$aValues[] = $mRuleValue;
					} else {
						$aValues = $mRuleValue->getListComponents();
					}
					foreach($aValues as $mValue) {
						$aNewValues[] = $mValue;
					}
					$this->removeRule($oRule);
				}
			}
    }
    if(count($aNewValues)) {
      $oNewRule = new CSSRule($sShorthand);
      foreach($aNewValues as $mValue) {
        $oNewRule->addValue($mValue);  
      }
      $this->addRule($oNewRule);
    }
	}

  public function createBackgroundShorthand() {
    $aProperties = array(
      'background-color', 'background-image', 'background-repeat', 
      'background-position', 'background-attachment'
    );
		$this->createShorthandProperties($aProperties, 'background');
	}

  public function createListStyleShorthand() {
		$aProperties = array(
			'list-style-type', 'list-style-position', 'list-style-image'
		);
		$this->createShorthandProperties($aProperties, 'list-style');
	}

  /**
   * Combine border-color, border-style and border-width into border
   * Should be run after create_dimensions_shorthand!
   **/
  public function createBorderShorthand() {
    $aProperties = array(
      'border-width', 'border-style', 'border-color' 
    );
		$this->createShorthandProperties($aProperties, 'border');
  }

  /*
   * Looks for long format CSS dimensional properties
   * (margin, padding, border-color, border-style and border-width) 
   * and converts them into shorthand CSS properties.
   **/
  public function createDimensionsShorthand() {
    $aPositions = array('top', 'right', 'bottom', 'left');
    $aExpansions = array(
      'margin'       => 'margin-%s',
      'padding'      => 'padding-%s',
      'border-color' => 'border-%s-color', 
      'border-style' => 'border-%s-style', 
      'border-width' => 'border-%s-width'
    );
    $aRules = $this->getRules();
    foreach($aExpansions as $sProperty => $sExpanded) {
      $aFoldable = array();
			foreach($aPositions as $sPosition) {
				$aRules = $this->getRules(sprintf($sExpanded, $sPosition));
				if(empty($aRules)) continue;
				foreach($aRules as $iPos => $oRule) {
					$aFoldable[$oRule->getRule()] = $oRule; 
				}
			}
      // All four dimensions must be present
      if(count($aFoldable) == 4) {
        $aValues = array();
        foreach($aPositions as $sPosition) {
          $oRule = $aFoldable[sprintf($sExpanded, $sPosition)];
          $mRuleValue = $oRule->getValue();
          $aRuleValues = array();
          if(!$mRuleValue instanceof CSSRuleValueList) {
            $aRuleValues[] = $mRuleValue;
          } else {
            $aRuleValues = $mRuleValue->getListComponents();
          }
          $aValues[$sPosition] = $aRuleValues;
        }
        $oNewRule = new CSSRule($sProperty);
        if((string)$aValues['left'][0] == (string)$aValues['right'][0]) {
          if((string)$aValues['top'][0] == (string)$aValues['bottom'][0]) {
            if((string)$aValues['top'][0] == (string)$aValues['left'][0]) {
              // All 4 sides are equal
              $oNewRule->addValue($aValues['top']);
            } else {
              // Top and bottom are equal, left and right are equal
              $oNewRule->addValue($aValues['top']);
              $oNewRule->addValue($aValues['left']);
            }
          } else {
            // Only left and right are equal
            $oNewRule->addValue($aValues['top']);
            $oNewRule->addValue($aValues['left']);
            $oNewRule->addValue($aValues['bottom']);
          }
        } else {
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
  public function createFontShorthand() {
    $aFontProperties = array(
      'font-style', 'font-variant', 'font-weight', 'font-size', 'line-height', 'font-family'
    );
    $aFSRules = $this->getRules('font-size');
    $aFFRules = $this->getRules('font-family');
    if(empty($aFSRules) || empty($aFFRules)) return;
    $oNewRule = new CSSRule('font');
    foreach(array('font-style', 'font-variant', 'font-weight') as $sProperty) {
			$aRules = $this->getRules($sProperty);
			if(empty($aRules)) continue;
			$oRule = end($aRules);
			$mRuleValue = $oRule->getValue();
			$aValues = array();
			if(!$mRuleValue instanceof CSSRuleValueList) {
				$aValues[] = $mRuleValue;
			} else {
				$aValues = $mRuleValue->getListComponents();
			}
			if($aValues[0] !== 'normal') {
				$oNewRule->addValue($aValues[0]);
			}
    }
    // Get the font-size value
    $oRule = end($aFSRules);
    $mRuleValue = $oRule->getValue();
    $aFSValues = array();
    if(!$mRuleValue instanceof CSSRuleValueList) {
      $aFSValues[] = $mRuleValue;
    } else {
      $aFSValues = $mRuleValue->getListComponents();
    }
    // But wait to know if we have line-height to add it
		$aRules = $this->getRules('line-height');
    if(!empty($aRules)) {
			$oRule = end($aRules);
      $mRuleValue = $oRule->getValue();
      $aLHValues = array();
      if(!$mRuleValue instanceof CSSRuleValueList) {
        $aLHValues[] = $mRuleValue;
      } else {
        $aLHValues = $mRuleValue->getListComponents();
      }
      if($aLHValues[0] !== 'normal') {
				$val = new CSSRuleValueList('/');
				$val->addListComponent($aFSValues[0]);
				$val->addListComponent($aLHValues[0]);
        $oNewRule->addValue($val);
      }
    } else {
      $oNewRule->addValue($aFSValues[0]);
    }
		// Font-Family
    $oRule = end($aFFRules);
    $mRuleValue = $oRule->getValue();
    $aFFValues = array();
    if(!$mRuleValue instanceof CSSRuleValueList) {
      $aFFValues[] = $mRuleValue;
    } else {
      $aFFValues = $mRuleValue->getListComponents();
    }
		$oFFValue = new CSSRuleValueList(',');
		$oFFValue->setListComponents($aFFValues);
    $oNewRule->addValue($oFFValue);

    $this->addRule($oNewRule);
    foreach($aFontProperties as $sProperty) {
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
