<?php

/**
* CSSRuleSet is a generic superclass denoting rules. The typical example for rule sets are declaration block.
* However, unknown At-Rules (like @font-face) are also rule sets.
*/
abstract class CSSRuleSet {
	protected $aRules;
	
	public function __construct() {
		$this->aRules = array();
	}

  /**
   * Checks if this sule set contains the given rule or rule name
   *
   * @param CSSRule|string $mRule The CSSRule or rule name to search for.
   *
   * @return bool
   **/
  public function containsRule($mRule) {
    if($mRule instanceof CSSRule) {
      return array_search($mRule, $this->aRules) !== false;
    }
		foreach($this->aRules as $oRule) {
			if($mRule === $oRule->getRule()) return true;
		}
		return false;
	}

  /**
   * Appends a CSSRule to this CSSRuleSet instance
   *
   * @param CSSRule $oRule The CSSRule to append
   *
   * @return CSSRuleSet The current CSSRuleSet instance
   **/
	public function appendRule(CSSRule $oRule) {
    $this->aRules[] = $oRule;
    return $this;
	}

  /**
   * Prepends a CSSRule to this CSSRuleSet instance
   *
   * @param CSSRule $oRule The CSSRule to prepend
   *
   * @return CSSRuleSet The current CSSRuleSet instance
   **/
	public function prependRule(CSSRule $oRule) {
    array_unshift($this->aRules, $oRule);
    return $this;
  }

	/**
	 * Inserts a rule or an array of rules after the specified rule.
	 *
	 * @param CSSRule|array $mRule The rule or array of rules to insert
   * @param CSSRule       $oRule The CSSRule after which rules will be added.
   *
   * @return CSSRuleSet The current CSSRuleSet instance
	 **/
  public function insertRuleAfter($mRule, CSSRule $oRule) {
    if(!is_array($mRule)) $mRule = array($mRule);
		$index = array_search($oRule, $this->aRules);
    array_splice($this->aRules, $index, 0, $mRule);
    return $this;
	}

	/**
	 * Inserts a rule or an array of rules before the specified rule.
	 *
	 * @param CSSRule|array $aRules The rule or array of rules to insert
	 * @param CSSRule       $oRule  The CSSRule before which rules will be added.
   *
   * @return CSSRuleSet The current CSSRuleSet instance
	 **/
	public function insertRuleBefore($mRule, CSSRule $oRule) {
    if(!is_array($mRule)) $mRule = array($mRule);
		$index = array_search($oRule, $this->aRules);
    array_splice($this->aRules, $index-1, 0, $mRule);
    return $this;
	}

	/**
	 * Replaces a rule by another rule or an array of rules.
	 *
	 * @param CSSRule       $oOldRule  The CSSRule to replace
	 * @param CSSRule|array $mNewRule  A CSSRule or an array of rules to add
   *
   * @return CSSRuleSet The current CSSRuleSet instance
	 **/
  public function replaceRule(CSSRule $oOldRule, $mNewRule) {
    if(!is_array($mNewRule)) $mNewRule = array($mNewRule);
		$index = array_search($oOldRule, $this->aRules);
    array_splice($this->aRules, $index, 1, $mNewRule);
    return $this;
	}

  /**
   * Removes a rule from this rule set.
   *
   * @param int|string|CSSRule|array $mRule An index, CSSRule instance or rule name to remove, or an array thereof.
   *                                  If int, removes the rule at given position.
   *                                  If CSSRule, removes the specified rule.
   *                                  If string, all matching rules will be removed.
   * @param bool           $bWildcard If true, all rules starting with the pattern are returned.
   *                                  If false only rules wich strictly match the pattern.
   **/
  public function removeRule($mSearch, $bWildcard=false) {
    if(!is_array($mSearch)) $mSearch = array($mSearch);
    foreach($mSearch as $mRule) {
      if(is_int($mRule)) {
        unset($this->aRules[$mRule]);
      } else if($mRule instanceof CSSRule) {
        $index = array_search($mRule, $this->aRules);
        unset($this->aRules[$index]);
      } else {
        foreach($this->aRules as $iPos => $oRule) {
          if($bWildcard) {
            if(strpos($oRule->getRule(), $mRule) === 0) unset($this->aRules[$iPos]);
          } else {
            if($oRule->getRule() === $mRule) unset($this->aRules[$iPos]);
          }
        }
      }
    }
    $this->aRules = array_values($this->aRules);
	}

	/**
	 * Get the position of a rule in the rule set.
	 * @param CSSRule $oRule The CSSRule to search for.
	 *
	 * @return int The position of the rule or false if it is not found; 
	 **/
	public function getRulePosition(CSSRule $oRule) {
		return array_search($oRule, $this->aRules);
	}
	
	/**
   * Returns all rules matching the given rule name.
   *
   * @param (null|string|CSSRule) $mRule     Pattern to search for.
   *                                         If null, returns all rules.
   *                                         If CSSRule, the rule name is used for searching.
   * @param bool                  $bWildcard If true, all rules starting with the pattern are returned.
   *                                         If false only rules wich strictly match the pattern.
   *
   * @return array An array of matching CSSRules
   *
	 * @example $oRuleSet->getRules('font', true) //returns an array of all rules beginning with font.
	 * @example $oRuleSet->getRules('font') //returns array([index] => $oRule) or empty array().
	 **/
	public function getRules($mRule = null, $bWildcard=false) {
		if(!$mRule) return $this->aRules;
		if($mRule instanceof CSSRule) $mRule = $mRule->getRule();
		$aResult = array();
		foreach($this->aRules as $iPos => $oRule) {
			if($bWildcard) {
				if(strpos($oRule->getRule(), $mRule) === 0) $aResult[$iPos] = $oRule;
			} else {
				if($oRule->getRule() === $mRule) $aResult[$iPos] = $oRule;
			}
		}
		return $aResult;
	}

  /**
   * Returns the first rule or the first rule matching name.
   *
   * @param null|string A rule name to match
   *
   * @return CSSRule
   **/
  public function getFirstRule($mRule=null) {
    if(!$mRule && isset($this->aRules[0])) return $this->aRules[0];
    foreach($this->aRules as $oRule) {
			if($oRule->getRule() === $mRule) return $oRule;
    }
  }

  /**
   * Returns the first rule or the first rule matching name.
   *
   * @param null|string A rule name to match
   *
   * @return CSSRule
   **/
  public function getLastRule($mRule=null) {
    if(!$mRule && count($this->aRules)) return $this->aRules[count($this->aRules)-1];
    foreach(array_reverse($this->aRules) as $oRule) {
			if($oRule->getRule() === $mRule) return $oRule;
    }
  }

  /**
   * Returns the last rule matching name, taking !important declaration into account.
   *
   * @param string  $sRule         A rule name
   * @param bool    $bWithPosition If true return an associative array containing
   *                               the rule object and its position.
   *                               If false return the matched rule.
   *
   * @return null|CSSRule|array
   **/
  public function getAppliedRule($sRule, $bWithPosition=false) {
    $aLastImportantRule = array();
    $aLastRule = array();
    foreach($this->aRules as $iPos => $oRule) {
      if($oRule->getRule() === $sRule) {
        if($oRule->getIsImportant()) {
          $aLastImportantRule['position'] = $iPos;
          $aLastImportantRule['rule'] = $oRule;
        } else {
          $aLastRule['position'] = $iPos;
          $aLastRule['rule'] = $oRule;
        }
      }
    }
    if($aLastImportantRule) {
      return $bWithPosition ? $aLastImportantRule : $aLastImportantRule['rule'];
    } else if($aLastRule) {
      return $bWithPosition ? $aLastRule : $aLastRule['rule'];
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
				} else if($mValue instanceof CSSRuleValueList && $mValue->getListSeparator() === '/') {
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
			if(count($aValues) === 1 && $aValues[0] === 'inherit') {
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
				if($mValue instanceof CSSColor) {
					$aBgProperties['background-color'] = $mValue;
				} else if ($mValue instanceof CSSURL || $mValue instanceof CSSFunction) {
					$aBgProperties['background-image'] = $mValue;
				} else if(in_array($mValue, array('scroll', 'fixed'))) {
					$aBgProperties['background-attachment'] = $mValue;
				} else if(in_array($mValue, array('repeat','no-repeat', 'repeat-x', 'repeat-y'))) {
					$aBgProperties['background-repeat'] = $mValue;
				} else if(in_array($mValue, array('left','center','right','top','bottom'))
						|| $mValue instanceof CSSSize
				){
					if($iNumBgPos === 0) {
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
			if(count($aValues) === 1 && $aValues[0] === 'inherit') {
				foreach ($aListProperties as $sProperty => $mValue) {
					$this->addRuleExpansion($iPos, $oRule, $sProperty, 'inherit');
				}
        $this->removeRule($iPos);
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

	private function addRuleExpansion($iShorthandPosition, $oShorthandRule, $sNewRuleName, $mValue) {
		$aExistingRules = $this->getRules($sNewRuleName);
		// Don't add if a rule already exists with the same name
    // and it comes after the un-expanded one
    $bShorthandIsImportant = $oShorthandRule->getIsImportant();
    if(!empty($aExistingRules)) {
      foreach($aExistingRules as $iPos => $oRule) {
        $bRuleIsImportant = $oRule->getIsImportant();
        if($iPos > $iShorthandPosition
           && ($bShorthandIsImportant == $bRuleIsImportant
                || ($bRuleIsImportant && !$bShorthandIsImportant)
              ) 
        ) return;
      }
    }
		$oNewRule = new CSSRule($sNewRuleName);
		$oNewRule->setIsImportant($bShorthandIsImportant);
		$oNewRule->addValue($mValue);
		$this->insertRuleAfter($oNewRule, $oShorthandRule);
	}

  public function createBackgroundShorthand() {
    $aProperties = array(
      'background-color', 'background-image', 'background-repeat', 
      'background-position', 'background-attachment'
    );
		$this->createShorthandProperties($aProperties, 'background', true);
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

  /**
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
    $iImportantCount = 0;
    foreach($aExpansions as $sProperty => $sExpanded) {
      $aFoldable = array();
			foreach($aPositions as $sPosition) {
				$oRule = $this->getAppliedRule(sprintf($sExpanded, $sPosition));
        if(!$oRule) continue;
        if($oRule->getIsImportant()) $iImportantCount++;
				$aFoldable[$oRule->getRule()] = $oRule; 
			}
      // All four dimensions must be present
      if(count($aFoldable) !== 4) return;
      // All four dimensions must have same importance
      if($iImportantCount && $iImportantCount !== 4) return;

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
      $oNewRule->setIsImportant(!!$iImportantCount);
      if((string)$aValues['left'][0] === (string)$aValues['right'][0]) {
        if((string)$aValues['top'][0] === (string)$aValues['bottom'][0]) {
          if((string)$aValues['top'][0] === (string)$aValues['left'][0]) {
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
      $this->appendRule($oNewRule);
      foreach ($aPositions as $sPosition)
      {
        $this->removeRule(sprintf($sExpanded, $sPosition));
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
    $oFSRule = $this->getAppliedRule('font-size');
    $oFFRule = $this->getAppliedRule('font-family');
    if(!$oFSRule || !$oFFRule) return;
    $oNewRule = new CSSRule('font');
    foreach(array('font-style', 'font-variant', 'font-weight') as $sProperty) {
			$oRule = $this->getAppliedRule($sProperty);
			if(!$oRule) continue;
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
    $mRuleValue = $oFSRule->getValue();
    $aFSValues = array();
    if(!$mRuleValue instanceof CSSRuleValueList) {
      $aFSValues[] = $mRuleValue;
    } else {
      $aFSValues = $mRuleValue->getListComponents();
    }
    // But wait to know if we have line-height to add it
		$oLHRule = $this->getAppliedRule('line-height');
    if($oLHRule) {
      $mRuleValue = $oLHRule->getValue();
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
    $mRuleValue = $oFFRule->getValue();
    $aFFValues = array();
    if(!$mRuleValue instanceof CSSRuleValueList) {
      $aFFValues[] = $mRuleValue;
    } else {
      $aFFValues = $mRuleValue->getListComponents();
    }
		$oFFValue = new CSSRuleValueList(',');
		$oFFValue->setListComponents($aFFValues);
    $oNewRule->addValue($oFFValue);

    $this->appendRule($oNewRule);
    $this->removeRule($aFontProperties);
	}

  private function createShorthandProperties(array $aProperties, $sShorthand, $bSafe=false) {
    if($bSafe) {
      $bCanProceed = $this->safeCleanup($aProperties, $sShorthand);
    } else {
      $bCanProceed = $this->destructiveCleanup($aProperties, $sShorthand);
    }
    if(!$bCanProceed) return;
    // Now we collapse the rules
    $aNewValues = array('normal' => array(), 'important' => array());
    $aOldRules = array('normal' => array(), 'important' => array());
    foreach($aProperties as $sProperty) {
      $aRules = $this->getRules($sProperty);
      foreach($aRules as $iPos => $oRule) {
        $mRuleValue = $oRule->getValue();
        $aValues = array();
        if($mRuleValue instanceof CSSRuleValueList) {
          $aValues = $mRuleValue->getListComponents();
        } else {
          $aValues[] = $mRuleValue;
        }
        $sDest = $oRule->getIsImportant() ? 'important' : 'normal';
        $aOldRules[$sDest][] = $iPos;
        foreach($aValues as $mValue) {
          $aNewValues[$sDest][] = $mValue;
        }
      }
    }
    $iImportantCount = count($aNewValues['important']);
    $iNormalCount = count($aNewValues['normal']);
    // Merge important values only if no normal values are present
    if($iNormalCount) {
      $this->mergeValues($sShorthand, $aNewValues['normal'], $aOldRules['normal'], false);
    } else if($iImportantCount) {
      $this->mergeValues($sShorthand, $aNewValues['important'], $aOldRules['important'], true);
    }
	}

  private function mergeValues($sShorthand, $aValues, $aOldRules, $bImportant) {
    $this->removeRule($aOldRules);
    $oNewRule = new CSSRule($sShorthand);
    $oNewRule->setIsImportant($bImportant);
    foreach($aValues as $mValue) {
      $oNewRule->addValue($mValue);  
    }
    $this->appendRule($oNewRule);
  }
  
  /**
   * Destructively cleans up rules before creating shorthand properties.
   * Keeps only significant properties according to their
   * respective order and importance.
   * This is the method we want to use in most cases.
   *
   **/
  private function destructiveCleanup(Array $aProperties, $sShorthand) {
    // first we check if a shorthand already exists, and keep only the relevant one.
    $aLastExistingShorthand = $this->getAppliedRule($sShorthand, true);
    foreach($this->getRules($sShorthand) as $iPos => $oRule) {
      if($iPos !== $aLastExistingShorthand['position']) $this->removeRule($iPos);
    }
    // next we try to get rid of unused rules
    foreach($aProperties as $sProperty) {
      $aRule = $this->getAppliedRule($sProperty, true);
      if(!$aRule) continue;
			foreach($this->getRules($sProperty) as $iPos => $oRule) {
        if($iPos !== $aRule['position']) $this->removeRule($iPos);
      }
      if($aLastExistingShorthand) {
        $bRuleIsImportant = $aRule['rule']->getIsImportant();
        $bShorthandIsImportant = $aLastExistingShorthand['rule']->getIsImportant();
        $iRulePosition = $aRule['position'];
        $iShorthandPosition = $aLastExistingShorthand['position'];
        // IF rule comes before shorthand AND they have the same importance,
        // OR IF shorthand is important AND rule is not,
        // we can get rid of the rule.
        if(($iRulePosition < $iShorthandPosition && $bRuleIsImportant === $bShorthandIsImportant)
           || (!$bRuleIsImportant && $bShorthandIsImportant)) {
          $this->removeRule($iRulePosition);
        }
      }
    }
    if($aLastExistingShorthand) {
      // Now that we made sure that there is no duplicate shorthand
      // we can expand the corresponding rule as expanding doesn't create duplicates.
      $sExpandMethod = 'expand'.str_replace(' ', '', ucwords(str_replace('-', ' ', $sShorthand))).'Shorthand';
      $this->$sExpandMethod();
    }
    return true;
  }

  /**
   * Safely cleans up rules before creating shorthand properties.
   * Avoids creating a shorthand if:
   * <ul>
   *   <li>
   *     More than one shorthand is already present,
   *     as it is generally done on purpose, ie for vendor specific values.
   *     <code>
   *       background: -webkit-linear-gradient(#000, #fff);
   *       background: -moz-linear-gradient(#000, #fff);
   *     </code>
   *   </li>
   *   <li>
   *     More than one identical rules are found,
   *     for the very same reasons
   *   </li>
   * </ul>
   *
   * @return bool True if collapsing can continue after cleanup, false otherwise
   **/
  private function safeCleanup(Array $aProperties, $sShorthand) {
    $aExistingShorthands = $this->getRules($sShorthand);
    $iNumShorthands = count($aExistingShorthands);
    // Don't create shorthands if more than one are already present,
    if($iNumShorthands > 1) return false;
    if($iNumShorthands === 1) {
      $iExistingShorthandPosition = key($aExistingShorthands);
    }
    foreach($aProperties as $sProperty) {
      $aRules = $this->getRules($sProperty);
      // Don't merge anything if several identical rules are present.
      if(count($aRules) > 1) return false;
      // Can't merge property if no value
      if(count($aRules) === 0) continue;
			foreach($aRules as $iPos => $oRule) {
        if($iNumShorthands && !$oRule->getIsImportant() && $iPos < $iExistingShorthandPosition) {
          // If rule is not important and comes before a shorthand, we can safely remove it.
          $this->removeRule($iPos);
          continue;
				}
      }
    }
    if($iNumShorthands) {
      // Now that we made sure that there is no duplicate shorthand
      // we can expand the corresponding rule as expanding doesn't create duplicates.
      $sExpandMethod = 'expand'.str_replace(' ', '', ucwords(str_replace('-', ' ', $sShorthand))).'Shorthand';
      $this->$sExpandMethod();
    }
    return true;
  }

	
	public function __toString() {
		$sResult = implode(', ', $this->aSelectors).' {';
		$sResult .= parent::__toString();
		$sResult .= '}';
		return $sResult;
	}
}
