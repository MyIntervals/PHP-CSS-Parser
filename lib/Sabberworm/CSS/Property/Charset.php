<?php

namespace Sabberworm\CSS\Property;
use Sabberworm\CSS\Value\CSSString;

/**
 * Class representing an @charset rule.
 * The following restrictions apply:
 * • May not be found in any CSSList other than the Document.
 * • May only appear at the very top of a Document’s contents.
 * • Must not appear more than once.
 */
class Charset implements AtRule {

	private $oCharset;
	protected $iLineNo;
	protected $aComments;

	/**
	 * @param CSSString $oCharset
	 * @param int $iLineNo
	 */
	public function __construct(CSSString $oCharset, $iLineNo = 0) {
		$this->oCharset = $oCharset;
		$this->iLineNo = $iLineNo;
		$this->aComments = array();
	}

	/**
	 * @return int
	 */
	public function getLineNo() {
		return $this->iLineNo;
	}

	/**
	 * @param CSSString $oCharset
	 */
	public function setCharset(CSSString $oCharset) {
		$this->oCharset = $oCharset;
	}

	/**
	 * @return CSSString
	 */
	public function getCharset() {
		return $this->oCharset;
	}

	public function __toString() {
		return $this->render(new \Sabberworm\CSS\OutputFormat());
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return "@charset {$this->oCharset->render($oOutputFormat)};";
	}

	public function atRuleName() {
		return 'charset';
	}

	public function atRuleArgs() {
		return $this->oCharset;
	}

	public function addComments(array $aComments) {
		$this->aComments = array_merge($this->aComments, $aComments);
	}

	public function getComments() {
		return $this->aComments;
	}

	public function setComments(array $aComments) {
		$this->aComments = $aComments;
	}
}