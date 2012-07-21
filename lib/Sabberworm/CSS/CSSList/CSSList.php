<?php

namespace Sabberworm\CSS\CSSList;

use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\RuleSet\RuleSet;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\Value\ValueList;
use Sabberworm\CSS\Value\CSSFunction;

/**
 * A CSSList is the most generic container available. Its contents include RuleSet as well as other CSSList objects.
 * Also, it may contain Import and Charset objects stemming from @-rules.
 */
abstract class CSSList {

	private $aContents;

	public function __construct() {
		$this->aContents = array();
	}

	public function append($oItem) {
		$this->aContents[] = $oItem;
	}

	/**
	 * Removes an item from the CSS list.
	 * @param RuleSet|Import|Charset|CSSList $oItemToRemove May be a RuleSet (most likely a DeclarationBlock), a Import, a Charset or another CSSList (most likely a MediaQuery)
	 */
	public function remove($oItemToRemove) {
		$iKey = array_search($oItemToRemove, $this->aContents, true);
		if ($iKey !== false) {
			unset($this->aContents[$iKey]);
		}
	}

	public function removeDeclarationBlockBySelector($mSelector, $bRemoveAll = false) {
		if ($mSelector instanceof DeclarationBlock) {
			$mSelector = $mSelector->getSelectors();
		}
		if (!is_array($mSelector)) {
			$mSelector = explode(',', $mSelector);
		}
		foreach ($mSelector as $iKey => &$mSel) {
			if (!($mSel instanceof Selector)) {
				$mSel = new Selector($mSel);
			}
		}
		foreach ($this->aContents as $iKey => $mItem) {
			if (!($mItem instanceof DeclarationBlock)) {
				continue;
			}
			if ($mItem->getSelectors() == $mSelector) {
				unset($this->aContents[$iKey]);
				if (!$bRemoveAll) {
					return;
				}
			}
		}
	}

	public function __toString() {
		$sResult = '';
		foreach ($this->aContents as $oContent) {
			$sResult .= $oContent->__toString();
		}
		return $sResult;
	}

	public function getContents() {
		return $this->aContents;
	}

	protected function allDeclarationBlocks(&$aResult) {
		foreach ($this->aContents as $mContent) {
			if ($mContent instanceof DeclarationBlock) {
				$aResult[] = $mContent;
			} else if ($mContent instanceof CSSList) {
				$mContent->allDeclarationBlocks($aResult);
			}
		}
	}

	protected function allRuleSets(&$aResult) {
		foreach ($this->aContents as $mContent) {
			if ($mContent instanceof RuleSet) {
				$aResult[] = $mContent;
			} else if ($mContent instanceof CSSList) {
				$mContent->allRuleSets($aResult);
			}
		}
	}

	protected function allValues($oElement, &$aResult, $sSearchString = null, $bSearchInFunctionArguments = false) {
		if ($oElement instanceof CSSList) {
			foreach ($oElement->getContents() as $oContent) {
				$this->allValues($oContent, $aResult, $sSearchString, $bSearchInFunctionArguments);
			}
		} else if ($oElement instanceof RuleSet) {
			foreach ($oElement->getRules($sSearchString) as $oRule) {
				$this->allValues($oRule, $aResult, $sSearchString, $bSearchInFunctionArguments);
			}
		} else if ($oElement instanceof Rule) {
			$this->allValues($oElement->getValue(), $aResult, $sSearchString, $bSearchInFunctionArguments);
		} else if ($oElement instanceof ValueList) {
			if ($bSearchInFunctionArguments || !($oElement instanceof CSSFunction)) {
				foreach ($oElement->getListComponents() as $mComponent) {
					$this->allValues($mComponent, $aResult, $sSearchString, $bSearchInFunctionArguments);
				}
			}
		} else {
			//Non-List Value or String (CSS identifier)
			$aResult[] = $oElement;
		}
	}

	protected function allSelectors(&$aResult, $sSpecificitySearch = null) {
		foreach ($this->getAllDeclarationBlocks() as $oBlock) {
			foreach ($oBlock->getSelectors() as $oSelector) {
				if ($sSpecificitySearch === null) {
					$aResult[] = $oSelector;
				} else {
					$sComparison = "\$bRes = {$oSelector->getSpecificity()} $sSpecificitySearch;";
					eval($sComparison);
					if ($bRes) {
						$aResult[] = $oSelector;
					}
				}
			}
		}
	}

}
