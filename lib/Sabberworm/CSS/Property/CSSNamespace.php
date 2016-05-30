<?php

namespace Sabberworm\CSS\Property;

/**
* CSSNamespace represents an @namespace rule.
*/
class CSSNamespace implements AtRule {
	private $mUrl;
	private $sPrefix;
	private $iLineNum;
	
	public function __construct($mUrl, $sPrefix = null, $iLineNum = 0) {
		$this->mUrl = $mUrl;
		$this->sPrefix = $sPrefix;
		$this->iLineNum = $iLineNum;
	}

	/**
	 * @return int
	 */
	public function getLineNum() {
		return $this->iLineNum;
	}

	public function __toString() {
		return $this->render(new \Sabberworm\CSS\OutputFormat());
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return '@namespace '.($this->sPrefix === null ? '' : $this->sPrefix.' ').$this->mUrl->render($oOutputFormat).';';
	}
	
	public function getUrl() {
		return $this->mUrl;
	}

	public function getPrefix() {
		return $this->sPrefix;
	}

	public function setUrl($mUrl) {
		$this->mUrl = $mUrl;
	}

	public function setPrefix($sPrefix) {
		$this->sPrefix = $sPrefix;
	}

	public function atRuleName() {
		return 'namespace';
	}

	public function atRuleArgs() {
		$aResult = array($this->mUrl);
		if($this->sPrefix) {
			array_unshift($aResult, $this->sPrefix);
		}
		return $aResult;
	}
}