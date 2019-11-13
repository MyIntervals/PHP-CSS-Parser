<?php

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\Parsing\ParserState;

class URL extends PrimitiveValue {

	private $oURL;

	public function __construct(CSSString $oURL, $iLineNo = 0) {
		parent::__construct($iLineNo);
		$this->oURL = $oURL;
	}

	public static function parse(ParserState $oParserState) {
		$oParserState->setAnchor();
		$sIdentifier = '';
		for ($i = 0; $i < 3; $i++) {
			$sChar = $oParserState->parseCharacter(true);
			if ($sChar === null) {
				break;
			}
			$sIdentifier .= $sChar;
		}
		$bUseUrl = $oParserState->streql($sIdentifier, 'url');
		if ($bUseUrl) {
			$oParserState->consumeWhiteSpace();
			$oParserState->consume('(');
		} else {
			$oParserState->backtrackToAnchor();
		}
		$oParserState->consumeWhiteSpace();
		$oResult = new URL(CSSString::parse($oParserState), $oParserState->currentLine());
		if ($bUseUrl) {
			$oParserState->consumeWhiteSpace();
			$oParserState->consume(')');
		}
		return $oResult;
	}


	public function setURL(CSSString $oURL) {
		$this->oURL = $oURL;
	}

	public function getURL() {
		return $this->oURL;
	}

	public function __toString() {
		return $this->render(new \Sabberworm\CSS\OutputFormat());
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return "url({$this->oURL->render($oOutputFormat)})";
	}

}
