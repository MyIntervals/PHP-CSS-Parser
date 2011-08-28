<?php

/**
* Class representing an @import rule.
*/
class CSSImport {
	private $oLocation;
	private $sMediaQuery;
	
	public function __construct(CSSURL $oLocation, $sMediaQuery) {
		$this->oLocation = $oLocation;
		$this->sMediaQuery = $sMediaQuery;
	}
	
	public function setLocation($oLocation) {
			$this->oLocation = $oLocation;
	}

	public function getLocation() {
			return $this->oLocation;
	}
	
	public function __toString() {
		return "@import ".$this->oLocation->__toString().($this->sMediaQuery === null ? '' : ' '.$this->sMediaQuery).';';
	}
}

/**
* Class representing an @charset rule.
* The following restrictions apply:
* • May not be found in any CSSList other than the CSSDocument.
* • May only appear at the very top of a CSSDocument’s contents.
* • Must not appear more than once.
*/
class CSSCharset {
	private $sCharset;
	
	public function __construct($sCharset) {
		$this->sCharset = $sCharset;
	}
	
	public function setCharset($sCharset) {
			$this->sCharset = $sCharset;
	}

	public function getCharset() {
			return $this->sCharset;
	}
	
	public function __toString() {
		return "@charset {$this->sCharset->__toString()};";
	}
}

/**
* Class representing a single CSS selector. Selectors have to be split by the comma prior to being passed into this class.
*/
class CSSSelector {
	const
		NON_ID_ATTRIBUTES_AND_PSEUDO_CLASSES_RX = '/
			(\.[\w]+)										 # classes
			|
			\[(\w+)											 # attributes
			|
			(\:(												 # pseudo classes
				link|visited|active
				|hover|focus
				|lang
				|target
				|enabled|disabled|checked|indeterminate
				|root
				|nth-child|nth-last-child|nth-of-type|nth-last-of-type
				|first-child|last-child|first-of-type|last-of-type
				|only-child|only-of-type
				|empty|contains
			))
		/ix',
		ELEMENTS_AND_PSEUDO_ELEMENTS_RX = '/
			((^|[\s\+\>\~]+)[\w]+ # elements
			|										
			\:{1,2}(								 # pseudo-elements
				after|before
				|first-letter|first-line
				|selection
			)
		)/ix';
	
	private $sSelector;
	private $iSpecificity;
	
	public function __construct($sSelector, $bCalculateSpecificity = false) {
		$this->setSelector($sSelector);
		if($bCalculateSpecificity) {
			$this->getSpecificity();
		}
	}
	
	public function getSelector() {
		return $this->sSelector;
	}
	
	public function setSelector($sSelector) {
		$this->sSelector = trim($sSelector);
		$this->iSpecificity = null;
	}

	public function __toString() {
		return $this->getSelector();
	}

	public function getSpecificity() {
		if($this->iSpecificity === null) {
			$a = 0;
			/// @todo should exclude \# as well as "#"
			$aMatches;
			$b = substr_count($this->sSelector, '#');
			$c = preg_match_all(self::NON_ID_ATTRIBUTES_AND_PSEUDO_CLASSES_RX, $this->sSelector, $aMatches);
			$d = preg_match_all(self::ELEMENTS_AND_PSEUDO_ELEMENTS_RX, $this->sSelector, $aMatches);
			$this->iSpecificity = ($a*1000) + ($b*100) + ($c*10) + $d;
		}
		return $this->iSpecificity;
	}
}

