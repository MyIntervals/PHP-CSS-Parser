<?php
class CSSParser { 
	private $sText;
	private $iCurrentPosition;
	private $iLength;
	
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
			return new CSSImport($this->parseURLValue());
		} else if($sIdentifier === 'charset') {
			$sCharset = $this->parseStringValue();
			$this->setCharset($sCharset);
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
			while(!preg_match('/[\\s{}()<>\\[\\]]/', $this->peek())) {
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
		return $sResult;
	}
	
	private function parseCharacter($bIsForIdentifier) {
		if($this->peek() === '\\') {
			$this->consume('\\');
			if($this->comes('\n') || $this->comes('\r')) {
				return '';
			}
			$aMatches;
			if(preg_match('/[0-9a-fA-F]/', $this->peek()) === 0) {
				return $this->consume(1);
			}
			$sUnicode = $this->consumeExpression('/[0-9a-fA-F]+/');
			if(mb_strlen($sUnicode, $this->sCharset) < 6) {
				if(!preg_match('\\s', $this->peek())) {
					throw new Exception("Unicode escape sequence not followed by whitespace");
				} else if($this->comes('\r\n')) {
					$this->consume(2);
				} else {
					$this->consume(1);
				}
			}
			$sUtf16 = '';
			if((strlen($sUnicode) % 2) === 1) {
				$sUnicode = "0$sUnicode";
			}
			for($i=0;$i<strlen($sUnicode);$i+=2) {
				$sUtf16 .= chr(intval($sUnicode[$i].$sUnicode[$i+1]));
			}
			return iconv('utf-16', $this->sCharset, $sUtf16);
		}
		if($bIsForIdentifier) {
			if(preg_match('/[a-zA-Z0-9]|-|_/', $this->peek()) === 1) {
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
		$oResult = new CSSSelector();
		$oResult->setSelector($this->consumeUntil('{'));
		$this->consume('{');
		$this->consumeWhiteSpace();
		$this->parseRuleSet($oResult);
		return $oResult;
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
			if($this->comes(',')) {
				$this->consume(',');
				$this->consumeWhiteSpace();
			}
			$oRule->addValue($this->parseValue());
			$this->consumeWhiteSpace();
		}
		if($this->comes('!')) {
			$this->consume('!');
			$this->consumeWhiteSpace();
			$this->consumeExpression('/important/i');
			$oRule->setIsImportant(true);
		}
		if($this->comes(';')) {
			$this->consume(';');
		}
		return $oRule;
	}
	
	private function parseValue() {
		if(is_numeric($this->peek()) || $this->comes('-')) {
			return $this->parseNumericValue();
		} else if($this->comes('#') || $this->comes('rgb') || $this->comes('hsl')) {
			return $this->parseColorValue();
		} else if($this->comes('url')){
			return $this->parseURLValue();
		} else if($this->comes("'") || $this->comes('"')){
			return $this->parseStringValue();
		} else {
			return $this->parseIdentifier();
		}
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
				$sSize .= $this->consumeExpression('/\\d+/');
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
			$fAlpha = 1.0;
			if($this->comes('#')) {
				$this->consume('#');
				$sValue = $this->parseIdentifier();
				if(mb_strlen($sValue, $this->sCharset) === 3) {
					$sValue = $sValue[0].$sValue[0].$sValue[1].$sValue[1].$sValue[2].$sValue[2];
				}
				$aColor = array('r' => intval($sValue[0].$sValue[1], 16), 'g' => intval($sValue[2].$sValue[3], 16), 'b' => intval($sValue[4].$sValue[5], 16));
			}
			$aColor = array('r' => $iR, 'g' => $iG, 'b' => $iB);
			if($fAlpha != 1.0) {
				$aColor['a'] = $fAlpha;
			}
		} else {
			$sColorMode = $this->parseIdentifier();
			$this->consumeWhiteSpace();
			$this->consume('(');
			$iLength = mb_strlen($sColorMode, $this->sCharset);
			for($i=0;$i<$iLength;$i++) {
				$this->consumeWhiteSpace();
				$aColor[$sColorMode[$i]] = $this->parseNumericValue();
				if($i < ($iLength-1)) {
					$this->consume(',');
				}
				$this->consumeWhiteSpace();
			}
			$this->consume(')');
		}
	}
	
	private function parseURLValue() {
		$this->consume('url');
		$this->consumeWhiteSpace();
		$this->consume('(');
		$oResult = new CSSURL($this->parseStringValue());
		$this->consume(')');
		return $oResult;
	}
	
	private function comes($sString, $iOffset = 0) {
		return $this->peek($sString, $iOffset) == $sString;
	}
	
	private function peek($iLength = 1, $iOffset = 0) {
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
			if($this->iCurrentPosition+$mValue >= $this->iLength) {
				throw new Exception("Tried to consume $mValue chars, exceeded file end");
			}
			$sResult = mb_substr($this->sText, $this->iCurrentPosition, $mValue, $this->sCharset);
			$this->iCurrentPosition += $mValue;
			return $sResult;
		}
	}
	
	private function consumeExpression($mExpression) {
		$aMatches;
		if(preg_match($mExpression, $this->sText, $aMatches, PREG_OFFSET_CAPTURE, $this->iCurrentPosition) === 1) {
			if($aMatches[0][1] === $this->iCurrentPosition) {
				return $this->consume($aMatches[0][0]);
			}
		}
		throw new Exception("Expected pattern $mExpression not found, got: {$this->peek(5)}");
	}
	
	private function consumeWhiteSpace() {
		while(preg_match('/\\s/', $this->peek()) === 1) {
			$this->consume(1);
		}
		$this->consumeComment();
	}
	
	private function consumeComment() {
		if($this->comes('/*')) {
			$this->consumeUntil('*/');
			$this->consume('*/');
		}
	}
	
	private function isEnd() {
		return $this->iCurrentPosition >= $this->iLength;
	}
	
	private function consumeUntil($sEnd) {
		$iEndPos = strpos($this->sText, $sEnd, $this->iCurrentPosition);
		if($iEndPos === false) {
			throw new Exception("Required $sEnd not found, got {$this->peek(5)}");
		}
		return $this->consume($iEndPos-$this->iCurrentPosition);
	}
	
	private function inputLeft() {
		return mb_substr($this->sText, $this->iCurrentPosition, -1, $this->sCharset);
	}
}

class CSSList {
	private $aContents;
	
	public function __construct() {
		$aContents = array();
	}
	
	public function append($oItem) {
		$this->aContents[] = $oItem;
	}
}

class CSSDocument extends CSSList {
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
}

class CSSImport {
	private $oLocation;
	
	public function __construct(CSSURL $oLocation) {
		$this->oLocation = $oLocation;
	}
	
	public function setLocation($oLocation) {
			$this->oLocation = $oLocation;
	}

	public function getLocation() {
			return $this->oLocation;
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
}

class CSSRuleSet {
	private $aRules;
	
	public function __construct() {
		$this->aRules = array();
	}
	public function addRule(CSSRule $oRule) {
		$this->aRules[$oRule->getRule()] = $oRule;
	}
}

class CSSAtRule extends CSSRuleSet {
	private $sType;
	
	public function __construct($sType) {
		$this->sType = $sType;
	}
}

class CSSSelector extends CSSRuleSet {
	private $aSelector;
	
	public function __construct() {
		parent::__construct();
		$this->aSelector = array();
	}
	
	public function setSelector($mSelector) {
		if(is_array($mSelector)) {
			$this->aSelector = $mSelector;
		} else {
			$this->aSelector = explode(',', $mSelector);
		}
		foreach($this->aSelector as $iKey => $sSelector) {
			$this->aSelector[$iKey] = trim($sSelector);
		}
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
}

class CSSValue {
	
}

class CSSSize extends CSSValue {
	private $fSize;
	private $sUnit;
	
	public function __construct($fSize, $sUnit) {
		$this->fSize = $fSize;
		$this->sUnit = $sUnit;
	}
	
	public function setUnit($sUnit) {
	    $this->sUnit = $sUnit;
	}

	public function getUnit() {
	    return $this->sUnit;
	}
	
	public function setSize($fSize) {
	    $this->fSize = $fSize;
	}

	public function getSize() {
	    return $this->fSize;
	}
}

class CSSColor extends CSSValue {
	private $aColor;
	
	public function __construct($aColor) {
		$this->aColor = $aColor;
	}
}

class CSSURL extends CSSValue {
	private $sURL;
	
	public function __construct($sURL) {
		$this->sURL = $sURL;
	}
}