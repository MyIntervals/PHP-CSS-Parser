<?php

namespace Sabberworm\CSS;

use Sabberworm\CSS\CSSList\CSSList;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\CSSList\KeyFrame;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Property\AtRule;
use Sabberworm\CSS\Property\Import;
use Sabberworm\CSS\Property\Charset;
use Sabberworm\CSS\Property\CSSNamespace;
use Sabberworm\CSS\RuleSet\AtRuleSet;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\Value\CSSFunction;
use Sabberworm\CSS\Value\RuleValueList;
use Sabberworm\CSS\Value\Size;
use Sabberworm\CSS\Value\Color;
use Sabberworm\CSS\Value\URL;
use Sabberworm\CSS\Value\CSSString;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Comment\Comment;

/**
 * Parser class parses CSS from text into a data structure.
 */
class Parser {

	private $sText;
	private $aText;
	private $iCurrentPosition;
	private $oParserSettings;
	private $sOriginalCharset;
	private $iLength;
	private $blockRules;
	private $aSizeUnits;
	private $iLineNo;
	private $sTextLibrary;

	const BOM8 = "\xef\xbb\xbf";
	const BOM16BE = "\xfe\xff";
	const BOM16LE = "\xff\xfe";
	const BOM32BE = "\x00\x00\xfe\xff";
	const BOM32LE = "\xff\xfe\x00\x00";

	/**
	 * Parser constructor.
	 * Note that that iLineNo starts from 1 and not 0
	 *
	 * @param $sText
	 * @param Settings|null $oParserSettings
	 * @param int $iLineNo
	 */
	public function __construct($sText, Settings $oParserSettings = null, $iLineNo = 1) {
		$this->sText = $sText;
		$this->iCurrentPosition = 0;
		$this->iLineNo = $iLineNo;
		if ($oParserSettings === null) {
			$oParserSettings = Settings::create();
		}
		$this->oParserSettings = $oParserSettings;
		$this->blockRules = explode('/', AtRule::BLOCK_RULES);

		foreach (explode('/', Size::ABSOLUTE_SIZE_UNITS.'/'.Size::RELATIVE_SIZE_UNITS.'/'.Size::NON_SIZE_UNITS) as $val) {
			$iSize = strlen($val);
			if(!isset($this->aSizeUnits[$iSize])) {
				$this->aSizeUnits[$iSize] = array();
			}
			$this->aSizeUnits[$iSize][strtolower($val)] = $val;
		}
		ksort($this->aSizeUnits, SORT_NUMERIC);
		$this->fixCharset();
	}

	private function fixCharset() {
		// We need to know the charset before the parsing starts,
		// UTF BOMs have the highest precedence and must ve removed before other processing.
		$this->sOriginalCharset = strtolower($this->oParserSettings->sDefaultCharset);
		if (strpos($this->sText, self::BOM8) === 0) {
			$this->sText = substr($this->sText, strlen(self::BOM8));
			$this->sOriginalCharset = 'utf-8';
		} else if (strpos($this->sText, self::BOM32BE) === 0) {
			$this->sText = substr($this->sText, strlen(self::BOM32BE));
			$this->sOriginalCharset = 'utf-32be';
		} else if (strpos($this->sText, self::BOM32LE) === 0) {
			$this->sText = substr($this->sText, strlen(self::BOM32LE));
			$this->sOriginalCharset = 'utf-32le';
		} else if (strpos($this->sText, self::BOM16BE) === 0) {
			$this->sText = substr($this->sText, strlen(self::BOM16BE));
			$this->sOriginalCharset = 'utf-16be';
		} else if (strpos($this->sText, self::BOM16LE) === 0) {
			$this->sText = substr($this->sText, strlen(self::BOM16LE));
			$this->sOriginalCharset = 'utf-16le';
		} else if (preg_match('/(.*)@charset\s+["\']([a-z0-9-]+)["\']\s*;/ims', $this->sText, $aMatches)) {
			// This is a simplified guessing, the charset atRule location is validated later,
			// hopefully this is not used much these days.
			if (trim($aMatches[1]) === '' and preg_match('/^@charset\s+["\']([a-z0-9-]+)["\']\s*;/im', $aMatches[0])) {
				$this->sOriginalCharset = strtolower($aMatches[2]);
			}
		}

		// Convert all text to utf-8 so that code does not have to deal with encoding conversions and incompatible characters.
		if ($this->sOriginalCharset !== 'utf-8') {
			if (function_exists('mb_convert_encoding')) {
				$this->sText = mb_convert_encoding($this->sText, 'utf-8', $this->sOriginalCharset);
			} else {
				$this->sText = iconv($this->sOriginalCharset, 'utf-8', $this->sText);
			}
		}

		// Multibyte support can make the parsing slower,
		// but even if it is disabled the unicode characters usually survive this parsing unharmed.
		$this->sTextLibrary = 'ascii';
		if (!$this->oParserSettings->bMultibyteSupport) {
			$this->iLength = strlen($this->sText);
			return;
		}

		// If there are only ASCII characters in the CSS then we can safely use good old PHP string functions here.
		if (function_exists('mb_convert_encoding')) {
			$sSubst = mb_substitute_character();
			mb_substitute_character('none');
			$asciiText = mb_convert_encoding($this->sText, 'ASCII', 'utf-8');
			mb_substitute_character($sSubst);
		} else {
			$asciiText = @iconv('utf-8', 'ASCII//IGNORE', $this->sText);
		}
		if ($this->sText !== $asciiText) {
			if (function_exists('mb_convert_encoding')) {
				// Usually mbstring extension is much faster than iconv.
				$this->sTextLibrary = 'mb';
			} else {
				$this->sTextLibrary = 'iconv';
			}
		}
		unset($asciiText);
		$this->iLength = $this->strlen($this->sText);

		// Substring operations are slower with unicode, aText array is used for faster emulation.
		if ($this->sTextLibrary !== 'ascii') {
			$this->aText = preg_split('//u', $this->sText, null, PREG_SPLIT_NO_EMPTY);
			if (!is_array($this->aText) || count($this->aText) !== $this->iLength) {
				$this->aText = null;
			}
		}
	}

	public function getCharset() {
		return 'utf-8';
	}

	public function getOriginalCharset() {
		return $this->sOriginalCharset;
	}

	public function parse() {
		$oResult = new Document($this->iLineNo);
		$this->parseDocument($oResult);
		return $oResult;
	}

	private function parseDocument(Document $oDocument) {
		$this->parseList($oDocument, true);
	}

	private function parseList(CSSList $oList, $bIsRoot = false) {
		while (true) {
			$comments = $this->consumeWhiteSpace();
			if ($this->isEnd()) {
				// End of file, ignore any trailing comments.
				break;
			}
			$oListItem = null;
			if($this->oParserSettings->bLenientParsing) {
				try {
					$oListItem = $this->parseListItem($oList, $bIsRoot);
				} catch (UnexpectedTokenException $e) {
					$oListItem = false;
				}
			} else {
				$oListItem = $this->parseListItem($oList, $bIsRoot);
			}
			if($oListItem === null) {
				// List parsing finished
				return;
			}
			if($oListItem) {
				$oListItem->setComments($comments);
				$oList->append($oListItem);
			}
		}
		if (!$bIsRoot) {
			throw new SourceException("Unexpected end of document", $this->iLineNo);
		}
	}

	private function parseListItem(CSSList $oList, $bIsRoot = false) {
		if ($this->comes('@')) {
			$oAtRule = $this->parseAtRule();
			if($oAtRule instanceof Charset) {
				if(!$bIsRoot) {
					throw new UnexpectedTokenException('@charset may only occur in root document', '', 'custom', $this->iLineNo);
				}
				if(count($oList->getContents()) > 0) {
					throw new UnexpectedTokenException('@charset must be the first parseable token in a document', '', 'custom', $this->iLineNo);
				}
				// We have already guessed the charset in the constructor, it cannot be changed now.
			}
			return $oAtRule;
		} else if ($this->comes('}')) {
			$this->consumeUnsafe(1);
			if ($bIsRoot) {
				throw new SourceException("Unopened {", $this->iLineNo);
			} else {
				return null;
			}
		} else {
			return $this->parseSelector();
		}
	}

	private function parseAtRule() {
		$this->consume('@');
		$sIdentifier = $this->parseIdentifier(false);
		$iIdentifierLineNum = $this->iLineNo;
		$this->consumeWhiteSpace();
		if ($sIdentifier === 'import') {
			$oLocation = $this->parseURLValue();
			$this->consumeWhiteSpace();
			$sMediaQuery = null;
			if (!$this->comes(';')) {
				$sMediaQuery = $this->consumeUntil(';');
			}
			$this->consume(';');
			return new Import($oLocation, $sMediaQuery, $iIdentifierLineNum);
		} else if ($sIdentifier === 'charset') {
			$oCharset = $this->parseStringValue();
			$this->consumeWhiteSpace();
			$this->consume(';');
			if (!$this->oParserSettings->bLenientParsing) {
				$sExpectedCharset = $this->getOriginalCharset();
				if ($sExpectedCharset === 'utf-16le' || $sExpectedCharset === 'utf-16be') {
					$sExpectedCharset = 'utf-16';
				} else if ($sExpectedCharset === 'utf-32le' || $sExpectedCharset === 'utf-32be') {
					$sExpectedCharset = 'utf-32';
				}
				if (strtolower($oCharset->getString() !== $sExpectedCharset)) {
					throw new UnexpectedTokenException('@charset value does not match detected value', '', 'custom', $this->iLineNo);
				}
			}
			// Replace the original charset with utf-8 because we have changed the encoding in the constructor.
			return new Charset(new CSSString('utf-8', $this->iLineNo), $iIdentifierLineNum);
		} else if ($this->identifierIs($sIdentifier, 'keyframes')) {
			$oResult = new KeyFrame($iIdentifierLineNum);
			$oResult->setVendorKeyFrame($sIdentifier);
			$oResult->setAnimationName(trim($this->consumeUntil('{', false, true)));
			$this->parseList($oResult);
			return $oResult;
		} else if ($sIdentifier === 'namespace') {
			$sPrefix = null;
			$mUrl = $this->parsePrimitiveValue();
			if (!$this->comes(';')) {
				$sPrefix = $mUrl;
				$mUrl = $this->parsePrimitiveValue();
			}
			$this->consume(';');
			if ($sPrefix !== null && !is_string($sPrefix)) {
				throw new UnexpectedTokenException('Wrong namespace prefix', $sPrefix, 'custom', $iIdentifierLineNum);
			}
			if (!($mUrl instanceof CSSString || $mUrl instanceof URL)) {
				throw new UnexpectedTokenException('Wrong namespace url of invalid type', $mUrl, 'custom', $iIdentifierLineNum);
			}
			return new CSSNamespace($mUrl, $sPrefix, $iIdentifierLineNum);
		} else {
			//Unknown other at rule (font-face or such)
			$sArgs = trim($this->consumeUntil('{', false, true));
			$bUseRuleSet = true;
			foreach($this->blockRules as $sBlockRuleName) {
				if($this->identifierIs($sIdentifier, $sBlockRuleName)) {
					$bUseRuleSet = false;
					break;
				}
			}
			if($bUseRuleSet) {
				$oAtRule = new AtRuleSet($sIdentifier, $sArgs, $iIdentifierLineNum);
				$this->parseRuleSet($oAtRule);
			} else {
				$oAtRule = new AtRuleBlockList($sIdentifier, $sArgs, $iIdentifierLineNum);
				$this->parseList($oAtRule);
			}
			return $oAtRule;
		}
	}

	private function parseIdentifier($bAllowFunctions = true, $bIgnoreCase = true) {
		$sResult = $this->parseCharacter(true);
		if ($sResult === null) {
			throw new UnexpectedTokenException($sResult, $this->peek(5), 'identifier', $this->iLineNo);
		}
		$sCharacter = null;
		while (($sCharacter = $this->parseCharacter(true)) !== null) {
			$sResult .= $sCharacter;
		}
		if ($bIgnoreCase) {
			$sResult = $this->strtolower($sResult);
		}
		if ($bAllowFunctions && $this->comes('(')) {
			$this->consumeUnsafe(1);
			$aArguments = $this->parseValue(array('=', ' ', ','));
			$sResult = new CSSFunction($sResult, $aArguments, ',', $this->iLineNo);
			$this->consume(')');
		}
		return $sResult;
	}

	private function parseStringValue() {
		$sBegin = $this->peek();
		$sQuote = null;
		if ($sBegin === "'") {
			$sQuote = "'";
		} else if ($sBegin === '"') {
			$sQuote = '"';
		}
		if ($sQuote !== null) {
			$this->consumeUnsafe(1);
		}
		$sResult = "";
		$sContent = null;
		if ($sQuote === null) {
			//Unquoted strings end in whitespace or with braces, brackets, parentheses
			while (!preg_match('/[\\s{}()<>\\[\\]]/isu', $this->peek())) {
				$sResult .= $this->parseCharacter(false);
			}
		} else {
			while (!$this->comes($sQuote)) {
				$sContent = $this->parseCharacter(false);
				if ($sContent === null) {
					throw new SourceException("Non-well-formed quoted string {$this->peek(3)}", $this->iLineNo);
				}
				$sResult .= $sContent;
			}
			$this->consumeUnsafe(1); // Consuming quote.
		}
		return new CSSString($sResult, $this->iLineNo);
	}

	private function parseCharacter($bIsForIdentifier) {
		$peek = $this->peek();
		if ($peek === '\\') {
			if ($bIsForIdentifier && $this->oParserSettings->bLenientParsing && ($this->comes('\0') || $this->comes('\9'))) {
				// Non-strings can contain \0 or \9 which is an IE hack supported in lenient parsing.
				return null;
			}
			$this->consumeUnsafe(1); // Consuming \
			$peek = $this->peek();
			if ($peek === '\n' || $peek === '\r') {
				return '';
			}
			if (preg_match('/[0-9a-fA-F]/Su', $peek) === 0) {
				$this->consumeUnsafe($peek);
				return $peek;
			}
			$peek6 = $this->peek(6);
			if (!preg_match('/^[0-9a-fA-F]{1,6}/', $peek6, $aMatches)) {
				throw new UnexpectedTokenException('Invalid hex encoded unicode character', $peek6, 'custom', $this->iLineNo);
			}
			$sUnicode = $aMatches[0];
			$iUnicodeLength = strlen($sUnicode);
			$this->consumeUnsafe($iUnicodeLength); // Consuming hex string
			if ($iUnicodeLength < 6) {
				// Consume one space after incomplete unicode escape if present
				$peek = $this->peek();
				if ($peek === ' ') {
					$this->consumeUnsafe(1);
				}
			}
			$iUnicode = intval($sUnicode, 16);
			$sUtf32 = "";
			for ($i = 0; $i < 4; ++$i) {
				$sUtf32 .= chr($iUnicode & 0xff);
				$iUnicode = $iUnicode >> 8;
			}
			$sChar = iconv('utf-32le', 'utf-8', $sUtf32);
			if ($sChar === chr(0)) {
				// PHP does not like null characters in strings for security reasons, just ignore them.
				return '';
			}
			return $sChar;
		}
		if ($bIsForIdentifier) {
			$ordPeek = ord($peek);
			// Ranges: a-z A-Z 0-9 - _
			if (($ordPeek >= 97 && $ordPeek <= 122) ||
				($ordPeek >= 65 && $ordPeek <= 90) ||
				($ordPeek >= 48 && $ordPeek <= 57) ||
				($ordPeek === 45) ||
				($ordPeek === 95) ||
				($ordPeek > 0xa1)) {
				$this->consumeUnsafe($peek);
				return $peek;
			}
			return null;
		} else {
			$this->consumeUnsafe($peek);
			return $peek;
		}
	}

	private function parseSelector() {
		$aComments = array();
		$oResult = new DeclarationBlock($this->iLineNo);
		$oResult->setSelector($this->consumeUntil('{', false, true, $aComments));
		$oResult->setComments($aComments);
		$this->parseRuleSet($oResult);
		return $oResult;
	}

	private function parseRuleSet($oRuleSet) {
		while ($this->comes(';')) {
			$this->consumeUnsafe(1);
		}
		while (true) {
			$peek = $this->peek();
			if ($peek === '}') {
				$this->consumeUnsafe(1);
				return;
			}
			if ($peek === '') {
				// End of file reached
				return;
			}

			$oRule = null;
			if($this->oParserSettings->bLenientParsing) {
				try {
					$oRule = $this->parseRule();
				} catch (UnexpectedTokenException $e) {
					try {
						$sConsume = $this->consumeUntil(array("\n", ";", '}'), true);
						// We need to “unfind” the matches to the end of the ruleSet as this will be matched later
						if(substr($sConsume, -1) === '}') { // Safe with utf-8 now
							--$this->iCurrentPosition;
						} else {
							while ($this->comes(';')) {
								$this->consumeUnsafe(1);
							}
						}
					} catch (UnexpectedTokenException $e) {
						// We’ve reached the end of the document. Just close the RuleSet.
						return;
					}
				}
			} else {
				$oRule = $this->parseRule();
			}
			if($oRule) {
				$oRuleSet->addRule($oRule);
			}
		}
	}

	private function parseRule() {
		$aComments = $this->consumeWhiteSpace();
		if ($this->peek() === '}') {
			// We have reached the end of rule set, any comments at the end will be ignored
			return null;
		}
		$oRule = new Rule($this->parseIdentifier(), $this->iLineNo);
		$oRule->setComments($aComments);
		$oRule->addComments($this->consumeWhiteSpace());
		$this->consume(':');
		$oValue = $this->parseValue(self::listDelimiterForRule($oRule->getRule()));
		$oRule->setValue($oValue);
		if ($this->oParserSettings->bLenientParsing) {
			while ($this->comes('\\')) {
				$this->consumeUnsafe(1);
				$oRule->addIeHack($this->consume());
				$this->consumeWhiteSpace();
			}
		}
		if ($this->comes('!')) {
			$this->consumeUnsafe(1);
			$this->consumeWhiteSpace();
			$this->consume('important');
			$oRule->setIsImportant(true);
		}
		while ($this->comes(';')) {
			$this->consumeUnsafe(1);
		}
		return $oRule;
	}

	private function parseValue($aListDelimiters) {
		$aStack = array();
		$this->consumeWhiteSpace();
		//Build a list of delimiters and parsed values
		while (!($this->comes('}') || $this->comes(';') || $this->comes('!') || $this->comes(')') || $this->comes('\\'))) {
			if (count($aStack) > 0) {
				$bFoundDelimiter = false;
				foreach ($aListDelimiters as $sDelimiter) {
					if ($this->comes($sDelimiter)) {
						array_push($aStack, $this->consume($sDelimiter));
						$this->consumeWhiteSpace();
						$bFoundDelimiter = true;
						break;
					}
				}
				if (!$bFoundDelimiter) {
					//Whitespace was the list delimiter
					array_push($aStack, ' ');
				}
			}
			array_push($aStack, $this->parsePrimitiveValue());
			$this->consumeWhiteSpace();
		}
		//Convert the list to list objects
		foreach ($aListDelimiters as $sDelimiter) {
			if (count($aStack) === 1) {
				return $aStack[0];
			}
			$iStartPosition = null;
			while (($iStartPosition = array_search($sDelimiter, $aStack, true)) !== false) {
				$iLength = 2; //Number of elements to be joined
				for ($i = $iStartPosition + 2; $i < count($aStack); $i+=2, ++$iLength) {
					if ($sDelimiter !== $aStack[$i]) {
						break;
					}
				}
				$oList = new RuleValueList($sDelimiter, $this->iLineNo);
				for ($i = $iStartPosition - 1; $i - $iStartPosition + 1 < $iLength * 2; $i+=2) {
					$oList->addListComponent($aStack[$i]);
				}
				array_splice($aStack, $iStartPosition - 1, $iLength * 2 - 1, array($oList));
			}
		}
		return $aStack[0];
	}

	private static function listDelimiterForRule($sRule) {
		if (preg_match('/^font($|-)/', $sRule)) {
			return array(',', '/', ' ');
		}
		return array(',', ' ', '/');
	}

	private function parsePrimitiveValue() {
		$oValue = null;
		$this->consumeWhiteSpace();
		$peek = $this->peek();
		$lowerpeek = $this->strtolower($peek);
		if ($peek === "'" || $peek === '"') {
			$oValue = $this->parseStringValue();
		} else if ($peek === '#' || ($lowerpeek === 'r' && $this->comes('rgb', true)) || ($lowerpeek === 'h' &&$this->comes('hsl', true))) {
			$oValue = $this->parseColorValue();
		} else if ($lowerpeek === 'u' && $this->comes('url', true)) {
			$oValue = $this->parseURLValue();
		} else if ($lowerpeek === 'p' && $this->oParserSettings->bLenientParsing && $this->comes("progid:")) {
			$oValue = $this->parseMicrosoftFilter();
		} else if (is_numeric($peek) || ($this->comes('-.') && is_numeric($this->peek(1, 2))) || (($peek === '-' || $peek === '.') && is_numeric($this->peek(1, 1)))) {
			$oValue = $this->parseNumericValue();
		} else {
			$oValue = $this->parseIdentifier(true, false);
		}
		$this->consumeWhiteSpace();
		return $oValue;
	}

	private function parseNumericValue($bForColor = false) {
		$sSize = '';
		$bHasDot = false;
		while (true) {
			$peek = $this->peek();
			if ($peek === '') {
				// End of file, this is weird
				break;
			}
			if ($sSize === '' && $peek === '-') {
				$sSize .= '-';
				$this->consumeUnsafe(1);
				continue;
			}
			if (!$bHasDot && $peek === '.') {
				$bHasDot = true;
				$sSize .= '.';
				$this->consumeUnsafe(1);
				continue;
			}
			if (is_numeric($peek)) {
				$sSize .= $peek;
				$this->consumeUnsafe($peek);
				continue;
			}
			break;
		}

		$sUnit = null;
		foreach ($this->aSizeUnits as $iLength => &$aValues) {
			$sKey = strtolower($this->peek($iLength)); // Length is always ascii
			if(array_key_exists($sKey, $aValues)) {
				if (($sUnit = $aValues[$sKey]) !== null) {
					$this->consume($iLength);
					break;
				}
			}
		}
		return new Size(floatval($sSize), $sUnit, $bForColor, $this->iLineNo);
	}

	private function parseColorValue() {
		$aColor = array();
		if ($this->comes('#')) {
			$this->consumeUnsafe(1);
			$sValue = $this->parseIdentifier(false);
			if ($this->strlen($sValue) === 3) {
				$sValue = $sValue[0] . $sValue[0] . $sValue[1] . $sValue[1] . $sValue[2] . $sValue[2];
			}
			$aColor = array('r' => new Size(intval($sValue[0] . $sValue[1], 16), null, true, $this->iLineNo), 'g' => new Size(intval($sValue[2] . $sValue[3], 16), null, true, $this->iLineNo), 'b' => new Size(intval($sValue[4] . $sValue[5], 16), null, true, $this->iLineNo));
		} else {
			$sColorMode = $this->parseIdentifier(false);
			$this->consumeWhiteSpace();
			$this->consume('(');
			$iLength = $this->strlen($sColorMode);
			for ($i = 0; $i < $iLength; ++$i) {
				$this->consumeWhiteSpace();
				$aColor[$sColorMode[$i]] = $this->parseNumericValue(true);
				$this->consumeWhiteSpace();
				if ($i < ($iLength - 1)) {
					$this->consume(',');
				}
			}
			$this->consume(')');
		}
		return new Color($aColor, $this->iLineNo);
	}

	private function parseMicrosoftFilter() {
		$sFunction = $this->consumeUntil('(', false, true);
		$aArguments = $this->parseValue(array(',', '='));
		return new CSSFunction($sFunction, $aArguments, ',', $this->iLineNo);
	}

	private function parseURLValue() {
		$bUseUrl = $this->comes('url', true);
		if ($bUseUrl) {
			$this->consume('url');
			$this->consumeWhiteSpace();
			$this->consume('(');
		}
		$this->consumeWhiteSpace();
		$oResult = new URL($this->parseStringValue(), $this->iLineNo);
		if ($bUseUrl) {
			$this->consumeWhiteSpace();
			$this->consume(')');
		}
		return $oResult;
	}

	/**
	 * Tests an identifier for a given value. Since identifiers are all keywords, they can be vendor-prefixed. We need to check for these versions too.
	 */
	private function identifierIs($sIdentifier, $sMatch) {
		return (strcasecmp($sIdentifier, $sMatch) === 0)
			?: preg_match("/^(-\\w+-)?$sMatch$/i", $sIdentifier) === 1;
	}

	private function comes($sString, $bCaseInsensitive = false) {
		$sPeek = $this->peek($this->strlen($sString));
		return ($sPeek === '')
			? false
			: $this->streql($sPeek, $sString, $bCaseInsensitive);
	}

	private function peek($iLength = 1, $iOffset = 0) {
		$iOffset += $this->iCurrentPosition;
		if ($iOffset >= $this->iLength) {
			return '';
		}
		if ($iLength === 1) {
			if ($this->aText !== null) {
				return $this->aText[$iOffset];
			} else {
				return $this->sText[$iOffset];
			}
		}
		return $this->substr($iOffset, $iLength);
	}

	private function consume($mValue = 1) {
		if (is_string($mValue)) {
			$iLineCount = substr_count($mValue, "\n");
			$iLength = $this->strlen($mValue);
			if (!$this->streql($this->substr($this->iCurrentPosition, $iLength), $mValue)) {
				throw new UnexpectedTokenException($mValue, $this->peek(max($iLength, 5)), $this->iLineNo);
			}
			$this->iLineNo += $iLineCount;
			$this->iCurrentPosition += $this->strlen($mValue);
			return $mValue;
		} else {
			if ($this->iCurrentPosition + $mValue > $this->iLength) {
				throw new UnexpectedTokenException($mValue, $this->peek(5), 'count', $this->iLineNo);
			}
			$sResult = $this->substr($this->iCurrentPosition, $mValue);
			$iLineCount = substr_count($sResult, "\n");
			$this->iLineNo += $iLineCount;
			$this->iCurrentPosition += $mValue;
			return $sResult;
		}
	}

	/**
	 * Consume characters without any safety checks.
	 *
	 * Make sure there are no newlines when giving integer value.
	 *
	 * NOTE: use only after peek() and comes()!
	 *
	 * @param int|string $mValue
	 * @return void
	 */
	private function consumeUnsafe($mValue) {
		if (is_string($mValue)) {
			$iLineCount = substr_count($mValue, "\n");
			$this->iLineNo += $iLineCount;
			$this->iCurrentPosition += $this->strlen($mValue);
		} else {
			// Must not have newlines!!!
			$this->iCurrentPosition += $mValue;
		}
	}

	private function consumeWhiteSpace() {
		$comments = array();
		do {
			while (preg_match('/^\\s/isSu', $this->peek(), $aMatches)) {
				$this->consumeUnsafe($aMatches[0]);
			}
			if($this->oParserSettings->bLenientParsing) {
				try {
					$oComment = $this->consumeComment();
				} catch(UnexpectedTokenException $e) {
					// When we can’t find the end of a comment, we assume the document is finished.
					$this->iCurrentPosition = $this->iLength;
					return $comments;
				}
			} else {
				$oComment = $this->consumeComment();
			}
			if ($oComment !== false) {
				$comments[] = $oComment;
			}
		} while($oComment !== false);
		return $comments;
	}

	/**
	 * @return false|Comment
	 */
	private function consumeComment() {
		if ($this->comes('/*')) {
			$iLineNo = $this->iLineNo;
			$this->consumeUnsafe(2);
			$mComment = '';
			while (true) {
				$peek = $this->peek();
				if ($peek === '') {
					if (!$this->oParserSettings->bLenientParsing) {
						throw new UnexpectedTokenException('*/', '', 'search', $this->iLineNo);
					}
					break;
				}
				if ($peek === '*' && $this->comes('*/')) {
					$this->consumeUnsafe(2);
					break;
				}
				$this->consumeUnsafe($peek);
				$mComment .= $peek;
			}
			return new Comment($mComment, $iLineNo);
		}
		return false;
	}

	private function isEnd() {
		return $this->iCurrentPosition >= $this->iLength;
	}

	private function consumeUntil($aEnd, $bIncludeEnd = false, $consumeEnd = false, array &$comments = array()) {
		$aEnd = is_array($aEnd) ? $aEnd : array($aEnd);
		$out = '';
		$start = $this->iCurrentPosition;

		while (($char = $this->consume(1)) !== '') {
			if (in_array($char, $aEnd)) {
				if ($bIncludeEnd) {
					$out .= $char;
				} elseif (!$consumeEnd) {
					$this->iCurrentPosition -= $this->strlen($char);
				}
				return $out;
			}
			$out .= $char;
			if ($comment = $this->consumeComment()) {
				$comments[] = $comment;
			}
		}

		$this->iCurrentPosition = $start;
		throw new UnexpectedTokenException('One of ("'.implode('","', $aEnd).'")', $this->peek(5), 'search', $this->iLineNo);
	}

	private function substr($iStart, $iLength) {
		if ($iLength <= 0 || $iStart >= $this->iLength) {
			return '';
		}
		if ($this->sTextLibrary === 'ascii') {
			return substr($this->sText, $iStart, $iLength);
		}
		if ($iLength > 100 || $iStart < 0 || !isset($this->aText)) {
			if ($this->sTextLibrary === 'mb') {
				return mb_substr($this->sText, $iStart, $iLength, 'utf-8');
			} else {
				return iconv_substr($this->sText, $iStart, $iLength, 'utf-8');
			}
		}
		// Use faster substr emulation for short unicode lengths.
		if ($iStart + $iLength > $this->iLength) {
			$iLength = $this->iLength - $iStart;
		}
		$sResult = '';
		while ($iLength > 0) {
			$sResult .= $this->aText[$iStart];
			$iStart++;
			$iLength--;
		}
		return $sResult;
	}

	private function strlen($sString) {
		if ($this->sTextLibrary === 'mb') {
			return mb_strlen($sString, 'utf-8');
		} else if ($this->sTextLibrary === 'iconv') {
			return iconv_strlen($sString, 'utf-8');
		} else {
			return strlen($sString);
		}
	}

	private function streql($sString1, $sString2, $bCaseInsensitive = true) {
		if($bCaseInsensitive) {
			return $this->strtolower($sString1) === $this->strtolower($sString2);
		} else {
			return $sString1 === $sString2;
		}
	}

	private function strtolower($sString) {
		if ($this->sTextLibrary === 'mb') {
			return mb_strtolower($sString, 'utf-8');
		} else {
			// Iconv cannot lowercase strings, bad luck
			return strtolower($sString);
		}
	}

}
