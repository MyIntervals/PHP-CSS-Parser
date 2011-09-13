<?php
require_once('lib/CSSCharsetUtils.php');
require_once('lib/CSSUrlUtils.php');
require_once('lib/CSSProperties.php');
require_once('lib/CSSList.php');
require_once('lib/CSSRuleSet.php');
require_once('lib/CSSRule.php');
require_once('lib/CSSValue.php');
require_once('lib/CSSValueList.php');

/**
* @package html
* CSSParser class parses CSS from text into a data structure.
*/
class CSSParser {
  /**
   * User options
   **/
  protected $aOptions = array(
    'resolve_imports' => false,
    'absolute_urls'   => false,
    'base_url'        => null,
    'input_encoding'  => null,
    'output_encoding' => null
  );

  /**
   * Parser internal pointers
   **/
	private $sText;
	private $iCurrentPosition;
	private $iLength;
  private $aLoadedFiles = array();

  /**
   * Data for resolving imports
   **/
  const IMPORT_FILE    = 'file';
  const IMPORT_URL     = 'url';
  const IMPORT_NONE    = 'none';
  private $sImportMode = 'none';

  /**
   * flags
   **/
  private $bIgnoreCharsetRules = false;
  private $bIgnoreImportRules  = false;
  private $bIsAbsBaseUrl;
	
  /**
   * @param $aOptions array of options
   * 
   * Valid options are:
   * <ul>
   *   <li>
   *     <b>input_encoding:</b>
   *     Force the input to be read with this encoding.
   *     This also force encoding for all imported stylesheets if resolve_imports is set to true.
   *     If not specified, the input encoding will be detected according to:
   *     http://www.w3.org/TR/CSS2/syndata.html#charset
   *   </li>
   *   <li>
   *     <b>output_encoding:</b>
   *     Converts the output to given encoding.
   *   </li>
   *   <li>
   *     <b>resolve_imports:</b>
   *     Recursively import embedded stylesheets.
   *   </li>
   *   <li>
   *     <b>absolute_urls:</b>
   *     Make all urls absolute.
   *   </li>
   *   <li>
   *     <b>base_url:</b>
   *     The base url to use for absolute urls and resolving imports.
   *     If not specified, will be computed from the file path or url.
   *   </li>
   * </ul>
   **/
  public function __construct(array $aOptions=array()) {
    $this->setOptions($aOptions);
  }

  /**
   * Gets an option value.
   *
   * @param  string $sName    The option name
   * @param  mixed  $mDefault The default value (null by default)
   *
   * @return mixed  The option value or the default value
   */
  public function getOption($sName, $mDefault=null) {
    return isset($this->aOptions[$sName]) ? $this->aOptions[$sName] : $mDefault;
  }
  /**
   * Sets an option value.
   *
   * @param  string $sName  The option name
   * @param  mixed  $mValue The default value
   *
   * @return CSSParser The current CSSParser instance
   */
  public function setOption($sName, $mValue) {
    $this->aOptions[$sName] = $mValue;
    return $this;
  }

  /**
   * Returns the options of the current instance.
   *
   * @return array The current instance's options
   **/
  public function getOptions() {
    return $this->aOptions;
  }

  /**
   * Merge given options with the current options
   *
   * @param array $aOptions The options to merge
   *
   * @return CSSParser The current CSSParser instance
   **/
  public function setOptions(array $aOptions) {
    $this->aOptions = array_merge($this->aOptions, $aOptions);
    return $this;
  }

  /**
   * @todo Access should be private, since calling this method
   *       from the outside world could lead to unpredicable results.
   **/
	public function setCharset($sCharset) {
		$this->sCharset = $sCharset;
		$this->iLength = mb_strlen($this->sText, $this->sCharset);
	}

	public function getCharset() {
		return $this->sCharset;
  }

  /**
   * Returns an array of all the loaded stylesheets.
   *
   * @return array The loaded stylesheets
   **/
  public function getLoadedFiles() {
    return $this->aLoadedFiles;
  }

  /**
   * Parses a local stylesheet into a CSSDocument object.
   *
   * @param string $sPath        Path to a file to load
   * @param array  $aLoadedFiles An array of files to exclude
   *
   * @return CSSDocument the resulting CSSDocument
   **/
  public function parseFile($sPath, $aLoadedFiles=array()) {
    if(!$this->getOption('base_url')) {
      $this->setOption('base_url', dirname($sPath));
    }
    if($this->getOption('absolute_urls') && !CSSUrlUtils::isAbsUrl($this->getOption('base_url'))) {
      $this->setOption('base_url', realpath($this->getOption('base_url')));
    }
    $this->sImportMode = self::IMPORT_FILE;
    $sPath = realpath($sPath);
    $aLoadedFiles[] = $sPath;
    $this->aLoadedFiles = array_merge($this->aLoadedFiles, $aLoadedFiles);
    $sCss = file_get_contents($sPath);
    return $this->parseString($sCss);
  }

  /**
   * Parses a remote stylesheet into a CSSDocument object.
   *
   * @param string $sPath        URL of a file to load
   * @param array  $aLoadedFiles An array of files to exclude
   *
   * @return CSSDocument the resulting CSSDocument
   **/
  public function parseURL($sPath, $aLoadedFiles=array()) {
    if(!$this->getOption('base_url')) {
      $this->setOption('base_url', CSSUrlUtils::dirname($sPath));
    }
    $this->sImportMode = self::IMPORT_URL;
    $aLoadedFiles[] =$sPath;
    $this->aLoadedFiles = array_merge($this->aLoadedFiles, $aLoadedFiles);
    $aResult = CSSUrlUtils::loadURL($sPath);
    $sResponse = $aResult['response'];
    // charset from Content-Type HTTP header
    // TODO: what do we do if the header returns a wrong charset ?
    if($aResult['charset']) {
      return $this->parseString($sResponse, $aResult['charset']);
    }
    return $this->parseString($sResponse);
  }

  /**
   * Parses a string into a CSSDocument object.
   *
   * @param string $sString  A CSS String
   * @param array  $sCharset An optional charset to use (overridden by the "input_encoding" option).
   *
   * @return CSSDocument the resulting CSSDocument
   **/

  public function parseString($sString, $sCharset=null) {
    $this->bIsAbsBaseUrl = CSSUrlUtils::isAbsUrl($this->getOption('base_url'));
    if($this->getOption('input_encoding')) {
      // The input encoding has been overriden by user.
      $sCharset = $this->getOption('input_encoding');
      $this->bIgnoreCharsetRules = true;
    }
    if(!$sCharset) {
      // detect charset from BOM and/or @charset rule
      $sCharset = CSSCharsetUtils::detectCharset($sString);
      if(!$sCharset) {
        $sCharset = 'UTF-8';
      }
    }
    $sString = CSSCharsetUtils::removeBOM($sString);
    if($this->getOption('output_encoding')) {
      $sString = CSSCharsetUtils::convert($sString, $sCharset, $this->getOption('output_encoding'));
      $sCharset = $this->getOption('output_encoding');
      $this->bIgnoreCharsetRules = true;
    }
		$this->sText = $sString;
		$this->iCurrentPosition = 0;
    $this->setCharset($sCharset);
		$oResult = new CSSDocument();
		$this->parseDocument($oResult);
    $this->postParse($oResult);
		return $oResult;
  }

  /**
   * Post processes the parsed CSSDocument object.
   *
   * Handles removal of ignored values and resolving of @import rules.
   *
   * @todo Should CSSIgnoredValue exist ?
   *       Another solution would be to add values only if they are not === null,
   *       i.e. in CSSList::append(), CSSRule::addValue() etc...
   **/
  private function postParse($oDoc) {
    $aCharsets = array();
    $aImports = array();
    $aContents = $oDoc->getContents();
    foreach($aContents as $i => $oItem) {
      if($oItem instanceof CSSIgnoredValue) {
        unset($aContents[$i]);
      } else if($oItem instanceof CSSCharset) {
        $aCharsets[] = $oItem;
        unset($aContents[$i]);
      } else if($oItem instanceof CSSImport) {
        $aImports[] = $oItem;
        unset($aContents[$i]);
      }
    }
    $aImportedItems = array();
    $aImportOptions = array_merge($this->getOptions(), array(
      'output_encoding' => $this->sCharset,
      'base_url'        => null
    ));
    foreach($aImports as $oImport) {
      if($this->getOption('resolve_imports')) {
        $parser = new CSSParser($aImportOptions);
        $sPath = $oImport->getLocation()->getURL()->getString();
        $bIsAbsUrl = CSSUrlUtils::isAbsUrl($sPath);
        if($this->sImportMode == self::IMPORT_URL || $bIsAbsUrl) {
          if(!in_array($sPath, $this->aLoadedFiles)) {          
            $oImportedDoc = $parser->parseURL($sPath, $this->aLoadedFiles);
            $this->aLoadedFiles = $parser->getLoadedFiles();
            $aImportedContents = $oImportedDoc->getContents();
          }
        } else if($this->sImportMode == self::IMPORT_FILE) {
          $sPath = realpath($sPath);
          if(!in_array($sPath, $this->aLoadedFiles)) {
            $oImportedDoc = $parser->parseFile($sPath, $this->aLoadedFiles);
            $this->aLoadedFiles = $parser->getLoadedFiles();
            $aImportedContents = $oImportedDoc->getContents();
          }
        }
        if($oImport->getMediaQuery() !== null) {
          $sMediaQuery = $oImport->getMediaQuery();
          $oMediaQuery = new CSSMediaQuery();
          $oMediaQuery->setQuery($sMediaQuery);
          $oMediaQuery->setContents($aImportedContents);
          $aImportedContents = array($oMediaQuery); 
        }
      } else {
        $aImportedContents = array($oImport);
      }
      $aImportedItems = array_merge($aImportedItems, $aImportedContents);
    }
    $aContents = array_merge($aImportedItems, $aContents);
    if(isset($aCharsets[0])) array_unshift($aContents, $aCharsets[0]);
    $oDoc->setContents($aContents);
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
        $this->bIgnoreCharsetRules = true;
        $this->bIgnoreImportRules = true;
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
      $this->bIgnoreCharsetRules = true;
      $this->bIgnoreImportRules = true;
			$oResult = new CSSMediaQuery();
			$oResult->setQuery(trim($this->consumeUntil('{')));
			$this->consume('{');
			$this->consumeWhiteSpace();
			$this->parseList($oResult);
			return $oResult;
		} else if($sIdentifier === 'import') {
      $this->bIgnoreCharsetRules = true;
			$oLocation = $this->parseURLValue();
			$this->consumeWhiteSpace();
			$sMediaQuery = null;
			if(!$this->comes(';')) {
				$sMediaQuery = $this->consumeUntil(';');
			}
			$this->consume(';');
      $oImport = new CSSImport($oLocation, $sMediaQuery);
      if($this->bIgnoreImportRules) {
        return new CSSIgnoredValue($oImport);
      }
      return $oImport;
		} else if($sIdentifier === 'charset') {
        $sCharset = $this->parseStringValue();
        $this->consumeWhiteSpace();
        $this->consume(';');
        $oCharset = new CSSCharset($sCharset);
        if($this->bIgnoreCharsetRules) {
          return new CSSIgnoredValue($oCharset);
        }
        $this->bIgnoreCharsetRules = true;
        return $oCharset;
		} else {
			//Unknown other at rule (font-face or such)
      $this->bIgnoreCharsetRules = true;
      $this->bIgnoreImportRules = true;
			$this->consume('{');
			$this->consumeWhiteSpace();
			$oAtRule = new CSSAtRule($sIdentifier);
			$this->parseRuleSet($oAtRule);
			return $oAtRule;
		}
	}
	
	private function parseIdentifier($bAllowFunctions = true) {
		$sResult = $this->parseCharacter(true);
		if($sResult === null) {
			throw new Exception("Identifier expected, got {$this->peek(5)}");
		}
		$sCharacter;
		while(($sCharacter = $this->parseCharacter(true)) !== null) {
			$sResult .= $sCharacter;
		}
		if($bAllowFunctions && $this->comes('(')) {
			$this->consume('(');
			$sResult = new CSSFunction($sResult, $this->parseValue(array('=', ',')));
			$this->consume(')');
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
      return CSSCharsetUtils::convert($sUtf32, 'UTF-32LE', $this->sCharset);
		}
		if($bIsForIdentifier) {
			if(preg_match('/\*|[a-zA-Z0-9]|-|_/u', $this->peek()) === 1) {
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
		$oResult = new CSSDeclarationBlock();
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
		$oValue = $this->parseValue(self::listDelimiterForRule($oRule->getRule()));
		$oRule->setValue($oValue);
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

	private function parseValue($aListDelimiters) {
		$aStack = array();
		$this->consumeWhiteSpace();
		while(!($this->comes('}') || $this->comes(';') || $this->comes('!') || $this->comes(')'))) {
			if(count($aStack) > 0) {
				$bFoundDelimiter = false;
				foreach($aListDelimiters as $sDelimiter) {
					if($this->comes($sDelimiter)) {
						array_push($aStack, $this->consume($sDelimiter));
						$this->consumeWhiteSpace();
						$bFoundDelimiter = true;
						break;
					}
				}
				if(!$bFoundDelimiter) {
					//Whitespace was the list delimiter
					array_push($aStack, ' ');
				}
			}
			array_push($aStack, $this->parsePrimitiveValue());
			$this->consumeWhiteSpace();
		}
		foreach($aListDelimiters as $sDelimiter) {
			if(count($aStack) === 1) {
				return $aStack[0];
			}
			$iStartPosition = null;
			while(($iStartPosition = array_search($sDelimiter, $aStack, true)) !== false) {
				$iLength = 2; //Number of elements to be joined
				for($i=$iStartPosition+2;$i<count($aStack);$i+=2) {
					if($sDelimiter !== $aStack[$i]) {
						break;
					}
					$iLength++;
				}
				$oList = new CSSRuleValueList($sDelimiter);
				for($i=$iStartPosition-1;$i-$iStartPosition+1<$iLength*2;$i+=2) {
					$oList->addListComponent($aStack[$i]);
				}
				array_splice($aStack, $iStartPosition-1, $iLength*2-1, array($oList));
			}
		}
		return $aStack[0];
	}

	private static function listDelimiterForRule($sRule) {
		if(preg_match('/^font($|-)/', $sRule)) {
			return array(',', '/', ' ');
		}
		return array(',', ' ', '/');
	}
	
	private function parsePrimitiveValue() {
		$oValue = null;
		$this->consumeWhiteSpace();
		if(is_numeric($this->peek()) || (($this->comes('-') || $this->comes('.')) && is_numeric($this->peek(1, 1)))) {
			$oValue = $this->parseNumericValue();
		} else if($this->comes('#') || $this->comes('rgb') || $this->comes('hsl')) {
			$oValue = $this->parseColorValue();
		} else if($this->comes('url')){
			$oValue = $this->parseURLValue();
		} else if($this->comes("'") || $this->comes('"')){
			$oValue = $this->parseStringValue();
		} else {
			$oValue = $this->parseIdentifier();
		}
		$this->consumeWhiteSpace();
		return $oValue;
	}
	
	private function parseNumericValue($bForColor = false) {
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
		} else if($this->comes('deg')) {
			$sUnit = $this->consume('deg');
		} else if($this->comes('s')) {
			$sUnit = $this->consume('s');
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
		return new CSSSize($fSize, $sUnit, $bForColor);
	}
	
	private function parseColorValue() {
		$aColor = array();
		if($this->comes('#')) {
			$this->consume('#');
			$sValue = $this->parseIdentifier(false);
			if(mb_strlen($sValue, $this->sCharset) === 3) {
				$sValue = $sValue[0].$sValue[0].$sValue[1].$sValue[1].$sValue[2].$sValue[2];
			}
			$aColor = array('r' => new CSSSize(intval($sValue[0].$sValue[1], 16), null, true), 'g' => new CSSSize(intval($sValue[2].$sValue[3], 16), null, true), 'b' => new CSSSize(intval($sValue[4].$sValue[5], 16), null, true));
		} else {
			$sColorMode = $this->parseIdentifier(false);
			$this->consumeWhiteSpace();
			$this->consume('(');
			$iLength = mb_strlen($sColorMode, $this->sCharset);
			for($i=0;$i<$iLength;$i++) {
				$this->consumeWhiteSpace();
				$aColor[$sColorMode[$i]] = $this->parseNumericValue(true);
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
    $sValue = $this->parseStringValue();
    if($this->getOption('absolute_urls') || $this->getOption('resolve_imports')) {
      $sURL = $sValue->getString(); 
      // resolve only if:
      // (url is not absolute) OR IF (url is absolute path AND base_url is absolute)
      $bIsAbsPath = CSSUrlUtils::isAbsPath($sURL);
      $bIsAbsUrl = CSSUrlUtils::isAbsUrl($sURL);
      if( (!$bIsAbsUrl && !$bIsAbsPath)
          || ($bIsAbsPath && $this->bIsAbsBaseUrl)) {
        $sURL = CSSUrlUtils::joinPaths(
          $this->getOption('base_url'), $sURL
        );
        $sValue = new CSSString($sURL);
      }
    }
		$oResult = new CSSURL($sValue);
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

