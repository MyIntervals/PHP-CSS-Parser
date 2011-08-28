<?php

/**
* A CSSList is the most generic container available. Its contents include CSSRuleSet as well as other CSSList objects.
* Also, it may contain CSSImport and CSSCharset objects stemming from @-rules.
*/
abstract class CSSList {
	private $aContents;
	
	public function __construct() {
		$this->aContents = array();
	}
	
	public function append($oItem) {
		$this->aContents[] = $oItem;
	}
	
	public function __toString() {
		$sResult = '';
		foreach($this->aContents as $oContent) {
			$sResult .= $oContent->__toString();
		}
		return $sResult;
	}
	
	public function getContents() {
		return $this->aContents;
	}
	
	protected function allDeclarationBlocks(&$aResult) {
		foreach($this->aContents as $mContent) {
			if($mContent instanceof CSSDeclarationBlock) {
				$aResult[] = $mContent;
			} else if($mContent instanceof CSSList) {
				$mContent->allDeclarationBlocks($aResult);
			}
		}
	}
	
	protected function allRuleSets(&$aResult) {
		foreach($this->aContents as $mContent) {
			if($mContent instanceof CSSRuleSet) {
				$aResult[] = $mContent;
			} else if($mContent instanceof CSSList) {
				$mContent->allRuleSets($aResult);
			}
		}
	}
	
	protected function allValues($oElement, &$aResult, $sSearchString = null) {
		if($oElement instanceof CSSList) {
			foreach($oElement->getContents() as $oContent) {
				$this->allValues($oContent, $aResult, $sSearchString);
			}
		} else if($oElement instanceof CSSRuleSet) {
			foreach($oElement->getRules($sSearchString) as $oRule) {
				$this->allValues($oRule, $aResult, $sSearchString);
			}
		} else if($oElement instanceof CSSRule) {
			foreach($oElement->getValues() as $aValues) {
				foreach($aValues as $mValue) {
					$aResult[] = $mValue;
				}
			}
		}
	}

	protected function allSelectors(&$aResult, $sSpecificitySearch = null) {
		foreach($this->getAllDeclarationBlocks() as $oBlock) {
			foreach($oBlock->getSelectors() as $oSelector) {
				if($sSpecificitySearch === null) {
					$aResult[] = $oSelector;
				} else {
					$sComparison = "\$bRes = {$oSelector->getSpecificity()} $sSpecificitySearch;";
					eval($sComparison);
					if($bRes) {
						$aResult[] = $oSelector;
					}
				}
			}
		}
	}
}

/**
* The root CSSList of a parsed file. Contains all top-level css contents, mostly declaration blocks, but also any @-rules encountered.
*/
class CSSDocument extends CSSList {
	/**
	* Gets all CSSDeclarationBlock objects recursively.
	*/
	public function getAllDeclarationBlocks() {
		$aResult = array();
		$this->allDeclarationBlocks($aResult);
		return $aResult;
	}

	/**
	* @deprecated use getAllDeclarationBlocks()
	*/
	public function getAllSelectors() {
		return $this->getAllDeclarationBlocks();
	}
	
	/**
	* Returns all CSSRuleSet objects found recursively in the tree.
	*/
	public function getAllRuleSets() {
		$aResult = array();
		$this->allRuleSets($aResult);
		return $aResult;
	}
	
	/**
	* Returns all CSSValue objects found recursively in the tree.
	*/
	public function getAllValues($mElement = null) {
		$sSearchString = null;
		if($mElement === null) {
			$mElement = $this;
		} else if(is_string($mElement)) {
			$sSearchString = $mElement;
			$mElement = $this;
		}
		$aResult = array();
		$this->allValues($mElement, $aResult, $sSearchString);
		return $aResult;
	}

	/**
	* Returns all CSSSelector objects found recursively in the tree.
	* Note that this does not yield the full CSSDeclarationBlock that the selector belongs to (and, currently, there is no way to get to that).
	* @param $sSpecificitySearch An optional filter by specificity. May contain a comparison operator and a number or just a number (defaults to "==").
	* @example getSelectorsBySpecificity('>= 100')
	*/
	public function getSelectorsBySpecificity($sSpecificitySearch = null) {
		if(is_numeric($sSpecificitySearch) || is_numeric($sSpecificitySearch[0])) {
			$sSpecificitySearch = "== $sSpecificitySearch";
		}
		$aResult = array();
		$this->allSelectors($aResult, $sSpecificitySearch);
		return $aResult;
	}
}

/**
* A CSSList consisting of the CSSList and CSSList objects found in a @media query.
*/
class CSSMediaQuery extends CSSList {
	private $sQuery;
	
	public function __construct() {
		parent::__construct();
		$this->sQuery = null;
	}
	
	public function setQuery($sQuery) {
			$this->sQuery = $sQuery;
	}

	public function getQuery() {
			return $this->sQuery;
	}
	
	public function __toString() {
		$sResult = "@media {$this->sQuery} {";
		$sResult .= parent::__toString();
		$sResult .= '}';
		return $sResult;
	}
}
