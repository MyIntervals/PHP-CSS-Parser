<?php

/**
* @package html
* CSSParser class parses CSS from text into a data structure.
*/
class CSSParser {
  const
    NON_ID_ATTRIBUTES_AND_PSEUDO_CLASSES_RX = '/
      (\.[\w]+)                    # classes
      |
      \[(\w+)                      # attributes
      |
      (\:(                         # pseudo classes
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
      \:{1,2}(                 # pseudo-elements
        after|before
        |first-letter|first-line
        |selection
      )
    )/ix';
    
	private $sText;
	private $iCurrentPosition;
	private $iLength;
	
  public static function computeSpecificity($selector){
    $a = 0;
    $b = substr_count($selector, '#');
    $c = preg_match_all(self::NON_ID_ATTRIBUTES_AND_PSEUDO_CLASSES_RX, $selector, $matches);
    $d = preg_match_all(self::ELEMENTS_AND_PSEUDO_ELEMENTS_RX, $selector, $matches);
    return ($a*1000) + ($b*100) + ($c*10) + $d;
  }
	
	public function __construct($sText, $sDefaultCharset = 'utf-8') {
		$this->sText = $sText;
		$this->iCurrentPosition = 0;
		$this->setCharset($sDefaultCharset);
	}
	
	public function setCharset($sCharset) {
		$this->sCharset = $sCharset;
		$this->iLength = mb_strlen($this->sText, $this->sCharset);
	}

	public function getCharset() {
			return $this->sCharset;
	}
	
	public function parse() {
		$oResult = new CSSDocument();
		$this->parseDocument($oResult);
		return $oResult;
	}
	
	private function parseDocument(CSSDocument $oDocument) {
		$this->consumeWhiteSpace();
		$this->parseList($oDocument, true);
	}
	
	private function parseList(CSSList $oList, $bIsRoot = false) {
		while(!$this->isEnd()) {
			if($this->comes('@')) {
				$oList->append($this->parseAtRule());
			} else if($this->comes('}')) {
				$this->consume('}');
				if($bIsRoot) {
					throw new Exception("Unopened {");
				} else {
					return;
				}
			} else {
				$oList->append($this->parseSelector());
			}
			$this->consumeWhiteSpace();
		}
		if(!$bIsRoot) {
			throw new Exception("Unexpected end of document");
		}
	}
	
	private function parseAtRule() {
		$this->consume('@');
		$sIdentifier = $this->parseIdentifier();
		$this->consumeWhiteSpace();
		if($sIdentifier === 'media') {
			$oResult = new CSSMediaQuery();
			$oResult->setQuery(trim($this->consumeUntil('{')));
			$this->consume('{');
			$this->consumeWhiteSpace();
			$this->parseList($oResult);
			return $oResult;
		} else if($sIdentifier === 'import') {
			$oLocation = $this->parseURLValue();
			$this->consumeWhiteSpace();
			$sMediaQuery = null;
			if(!$this->comes(';')) {
				$sMediaQuery = $this->consumeUntil(';');
			}
			$this->consume(';');
			return new CSSImport($oLocation, $sMediaQuery);
		} else if($sIdentifier === 'charset') {
			$sCharset = $this->parseStringValue();
			$this->consumeWhiteSpace();
			$this->consume(';');
			$this->setCharset($sCharset->getString());
			return new CSSCharset($sCharset);
		} else {
			//Unknown other at rule (font-face or such)
			$this->consume('{');
			$this->consumeWhiteSpace();
			$oAtRule = new CSSAtRule($sIdentifier);
			$this->parseRuleSet($oAtRule);
			return $oAtRule;
		}
	}
	
	private function parseIdentifier() {
		$sResult = $this->parseCharacter(true);
		if($sResult === null) {
			throw new Exception("Identifier expected, got {$this->peek(5)}");
		}
		$sCharacter;
		while(($sCharacter = $this->parseCharacter(true)) !== null) {
			$sResult .= $sCharacter;
		}
		return $sResult;
	}
	
	private function parseStringValue() {
		$sBegin = $this->peek();
		$sQuote = null;
		if($sBegin === "'") {
			$sQuote = "'";
		} else if($sBegin === '"') {
			$sQuote = '"';
		}
		if($sQuote !== null) {
			$this->consume($sQuote);
		}
		$sResult = "";
		$sContent = null;
		if($sQuote === null) {
			//Unquoted strings end in whitespace or with braces, brackets, parentheses
			while(!preg_match('/[\\s{}()<>\\[\\]]/isu', $this->peek())) {
				$sResult .= $this->parseCharacter(false);
			}
		} else {
			while(!$this->comes($sQuote)) {
				$sContent = $this->parseCharacter(false);
				if($sContent === null) {
					throw new Exception("Non-well-formed quoted string {$this->peek(3)}");
				}
				$sResult .= $sContent;
			}
			$this->consume($sQuote);
		}
		return new CSSString($sResult);
	}
	
	private function parseCharacter($bIsForIdentifier) {
		if($this->peek() === '\\') {
			$this->consume('\\');
			if($this->comes('\n') || $this->comes('\r')) {
				return '';
			}
			$aMatches;
			if(preg_match('/[0-9a-fA-F]/Su', $this->peek()) === 0) {
				return $this->consume(1);
			}
			$sUnicode = $this->consumeExpression('/^[0-9a-fA-F]{1,6}/u');
			if(mb_strlen($sUnicode, $this->sCharset) < 6) {
				//Consume whitespace after incomplete unicode escape
				if(preg_match('/\\s/isSu', $this->peek())) {
					if($this->comes('\r\n')) {
						$this->consume(2);
					} else {
						$this->consume(1);
					}
				}
			}
			$iUnicode = intval($sUnicode, 16);
			$sUtf32 = "";
			for($i=0;$i<4;$i++) {
				$sUtf32 .= chr($iUnicode & 0xff);
				$iUnicode = $iUnicode >> 8;
			}
			return iconv('utf-32le', $this->sCharset, $sUtf32);
		}
		if($bIsForIdentifier) {
			if(preg_match('/[a-zA-Z0-9]|-|_/u', $this->peek()) === 1) {
				return $this->consume(1);
			} else if(ord($this->peek()) > 0xa1) {
				return $this->consume(1);
			} else {
				return null;
			}
		} else {
			return $this->consume(1);
		}
		// Does not reach here
		return null;
	}
	
  private function parseSelector() {
    $oResult = new NullCSSRuleSet();
    $selectors = explode( ',', $this->consumeUntil('{') );
    $this->consume('{');
    $this->consumeWhiteSpace();
    $this->parseRuleSet($oResult);

    $aSelectors = array();
    foreach($selectors as $selector)
    {
      $oSelector = new CSSSelector();
      $oSelector->setSelector(trim($selector));
      foreach ($oResult->getRules() as $rule)
      {
        $oSelector->addRule($rule);
      }
      $aSelectors[] = $oSelector;      
    }
		return $aSelectors;
	}
	
	private function parseRuleSet($oRuleSet) {
		while(!$this->comes('}')) {
			$oRuleSet->addRule($this->parseRule());
			$this->consumeWhiteSpace();
		}
		$this->consume('}');
	}
	
	private function parseRule() {
		$oRule = new CSSRule($this->parseIdentifier());
		$this->consumeWhiteSpace();
		$this->consume(':');
		$this->consumeWhiteSpace();
		while(!($this->comes('}') || $this->comes(';') || $this->comes('!'))) {
			$oRule->addValue($this->parseValue());
			$this->consumeWhiteSpace();
		}
		if($this->comes('!')) {
			$this->consume('!');
			$this->consumeWhiteSpace();
			$sImportantMarker = $this->consume(strlen('important'));
			if(mb_convert_case($sImportantMarker, MB_CASE_LOWER) !== 'important') {
				throw new Exception("! was followed by “".$sImportantMarker."”. Expected “important”");
			}
			$oRule->setIsImportant(true);
		}
		if($this->comes(';')) {
			$this->consume(';');
		}
		return $oRule;
	}
	
	private function parseValue() {
		$aResult = array();
		do {
			$this->consumeWhiteSpace();
			if(is_numeric($this->peek()) || $this->comes('-') || $this->comes('.')) {
				$aResult[] = $this->parseNumericValue();
			} else if($this->comes('#') || $this->comes('rgb') || $this->comes('hsl')) {
				$aResult[] = $this->parseColorValue();
			} else if($this->comes('url')){
				$aResult[] = $this->parseURLValue();
			} else if($this->comes("'") || $this->comes('"')){
				$aResult[] = $this->parseStringValue();
			} else {
				$aResult[] = $this->parseIdentifier();
			}
			$this->consumeWhiteSpace();
		} while($this->comes(',') && is_string($this->consume(',')));
		
		return $aResult;
	}
	
	private function parseNumericValue() {
		$sSize = '';
		if($this->comes('-')) {
			$sSize .= $this->consume('-');
		}
		while(is_numeric($this->peek()) || $this->comes('.')) {
			if($this->comes('.')) {
				$sSize .= $this->consume('.');
			} else {
				$sSize .= $this->consume(1);
			}
		}
		$fSize = floatval($sSize);
		$sUnit = null;
		if($this->comes('%')) {
			$sUnit = $this->consume('%');
		} else if($this->comes('em')) {
			$sUnit = $this->consume('em');
		} else if($this->comes('ex')) {
			$sUnit = $this->consume('ex');
		} else if($this->comes('px')) {
			$sUnit = $this->consume('px');
		} else if($this->comes('cm')) {
			$sUnit = $this->consume('cm');
		} else if($this->comes('pt')) {
			$sUnit = $this->consume('pt');
		} else if($this->comes('in')) {
			$sUnit = $this->consume('in');
		} else if($this->comes('pc')) {
			$sUnit = $this->consume('pc');
		} else if($this->comes('cm')) {
			$sUnit = $this->consume('cm');
		} else if($this->comes('mm')) {
			$sUnit = $this->consume('mm');
		}
		return new CSSSize($fSize, $sUnit);
	}
	
	private function parseColorValue() {
		$aColor = array();
		if($this->comes('#')) {
			$this->consume('#');
			$sValue = $this->parseIdentifier();
			if(mb_strlen($sValue, $this->sCharset) === 3) {
				$sValue = $sValue[0].$sValue[0].$sValue[1].$sValue[1].$sValue[2].$sValue[2];
			}
			$aColor = array('r' => new CSSSize(intval($sValue[0].$sValue[1], 16)), 'g' => new CSSSize(intval($sValue[2].$sValue[3], 16)), 'b' => new CSSSize(intval($sValue[4].$sValue[5], 16)));
		} else {
			$sColorMode = $this->parseIdentifier();
			$this->consumeWhiteSpace();
			$this->consume('(');
			$iLength = mb_strlen($sColorMode, $this->sCharset);
			for($i=0;$i<$iLength;$i++) {
				$this->consumeWhiteSpace();
				$aColor[$sColorMode[$i]] = $this->parseNumericValue();
				$this->consumeWhiteSpace();
				if($i < ($iLength-1)) {
					$this->consume(',');
				}
			}
			$this->consume(')');
		}
		return new CSSColor($aColor);
	}
	
	private function parseURLValue() {
		$bUseUrl = $this->comes('url');
		if($bUseUrl) {
			$this->consume('url');
			$this->consumeWhiteSpace();
			$this->consume('(');
		}
		$this->consumeWhiteSpace();
		$oResult = new CSSURL($this->parseStringValue());
		if($bUseUrl) {
			$this->consumeWhiteSpace();
			$this->consume(')');
		}
		return $oResult;
	}
	
	private function comes($sString, $iOffset = 0) {
		if($this->isEnd()) {
			return false;
		}
		return $this->peek($sString, $iOffset) == $sString;
	}
	
	private function peek($iLength = 1, $iOffset = 0) {
		if($this->isEnd()) {
			return '';
		}
		if(is_string($iLength)) {
			$iLength = mb_strlen($iLength, $this->sCharset);
		}
		if(is_string($iOffset)) {
			$iOffset = mb_strlen($iOffset, $this->sCharset);
		}
		return mb_substr($this->sText, $this->iCurrentPosition+$iOffset, $iLength, $this->sCharset);
	}
	
	private function consume($mValue = 1) {
		if(is_string($mValue)) {
			$iLength = mb_strlen($mValue, $this->sCharset);
			if(mb_substr($this->sText, $this->iCurrentPosition, $iLength, $this->sCharset) !== $mValue) {
				throw new Exception("Expected $mValue, got ".$this->peek(5));
			}
			$this->iCurrentPosition += mb_strlen($mValue, $this->sCharset);
			return $mValue;
		} else {
			if($this->iCurrentPosition+$mValue > $this->iLength) {
				throw new Exception("Tried to consume $mValue chars, exceeded file end");
			}
			$sResult = mb_substr($this->sText, $this->iCurrentPosition, $mValue, $this->sCharset);
			$this->iCurrentPosition += $mValue;
			return $sResult;
		}
	}
	
	private function consumeExpression($mExpression) {
		$aMatches;
		if(preg_match($mExpression, $this->inputLeft(), $aMatches, PREG_OFFSET_CAPTURE) === 1) {
			return $this->consume($aMatches[0][0]);
		}
		throw new Exception("Expected pattern $mExpression not found, got: {$this->peek(5)}");
	}
	
	private function consumeWhiteSpace() {
		do {
			while(preg_match('/\\s/isSu', $this->peek()) === 1) {
				$this->consume(1);
			}
		} while($this->consumeComment());
	}
	
	private function consumeComment() {
		if($this->comes('/*')) {
			$this->consumeUntil('*/');
			$this->consume('*/');
			return true;
		}
		return false;
	}
	
	private function isEnd() {
		return $this->iCurrentPosition >= $this->iLength;
	}
	
	private function consumeUntil($sEnd) {
		$iEndPos = mb_strpos($this->sText, $sEnd, $this->iCurrentPosition, $this->sCharset);
		if($iEndPos === false) {
			throw new Exception("Required $sEnd not found, got {$this->peek(5)}");
		}
		return $this->consume($iEndPos-$this->iCurrentPosition);
	}
	
	private function inputLeft() {
		return mb_substr($this->sText, $this->iCurrentPosition, -1, $this->sCharset);
	}
}

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
	
	protected function allSelectors(&$aResult) {
		foreach($this->aContents as $mContent) {
			if($mContent instanceof CSSSelector) {
				$aResult[] = $mContent;
			} else if($mContent instanceof CSSList) {
				$mContent->allSelectors($aResult);
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
}

class CSSDocument extends CSSList {
	public function getAllSelectors() {
		$aResult = array();
		$this->allSelectors($aResult);
		return $aResult;
	}
	
	public function getAllRuleSets() {
		$aResult = array();
		$this->allRuleSets($aResult);
		return $aResult;
	}
	
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
}

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

abstract class CSSRuleSet {
	private $aRules;
	
	public function __construct() {
		$this->aRules = array();
	}
	
	public function addRule(CSSRule $oRule) {
		$this->aRules[$oRule->getRule()] = $oRule;
	}
	
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

class NullCSSRuleSet extends CSSRuleSet {}

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

class CSSSelector extends CSSRuleSet {
	private
	  $sSelector = '',
	  $iSpecificity = 0,
	  $bIsInlineStyle = false;
	
	public function __construct() {
		parent::__construct();
	}
	
	public function setSelector($mSelector) {
    $this->sSelector = $mSelector;
    $this->iSpecificity = CSSParser::computeSpecificity($mSelector);
	}
	
	public function getSelector() {
		return $this->sSelector;
	}
	
	public function setSpecificity($iSpecificity)
  {
    $this->iSpecificity = $iSpecificity;
  }
	
	public function getSpecificity()
  {
    return $this->iSpecificity;
  }
	
	public function __toString() {
		$sResult = $this->sSelector . ' {';
		$sResult .= parent::__toString();
		$sResult .= '}';
		return $sResult;
	}
}

class CSSRule {
	private $sRule;
	private $aValues;
	private $bIsImportant;
	
	public function __construct($sRule) {
		$this->sRule = $sRule;
		$this->bIsImportant = false;
	}
	
	public function setRule($sRule) {
			$this->sRule = $sRule;
	}

	public function getRule() {
			return $this->sRule;
	}
	
	public function addValue($mValue) {
		$this->aValues[] = $mValue;
	}
	
	public function setValues($aValues) {
			$this->aValues = $aValues;
	}

	public function getValues() {
			return $this->aValues;
	}
	
	public function setIsImportant($bIsImportant) {
			$this->bIsImportant = $bIsImportant;
	}

	public function getIsImportant() {
			return $this->bIsImportant;
	}
	public function __toString() {
		$sResult = "{$this->sRule}: ";
		foreach($this->aValues as $aValues) {
			$sResult .= implode(', ', $aValues).' ';
		}
		if($this->bIsImportant) {
			$sResult .= '!important';
		} else {
			$sResult = substr($sResult, 0, -1);
		}
		$sResult .= ';';
		return $sResult;
	}
}

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
