<?php

namespace Sabberworm\CSS;

use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\Parsing\ParserState;

/**
 * Parser class parses CSS from text into a data structure.
 */
class Parser {
	private $sText;
	private $iLineNo;

	private $oParserState;

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
		if ($oParserSettings === null) {
			$oParserSettings = Settings::create();
		}
		$this->oParserSettings = $oParserSettings;
		$this->iLineNo = $iLineNo;
	}

	public function setCharset($sCharset) {
		$this->oParserHelper->setCharset($sCharset);
	}

	public function getCharset() {
		$this->oParserHelper->getCharset();
	}

	public function parse() {
		$this->oParserState = new ParserState($this->sText, $this->oParserSettings);
		return Document::parse($this->oParserState);
	}

}
