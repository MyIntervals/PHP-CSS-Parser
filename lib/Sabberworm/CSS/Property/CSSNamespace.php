<?php

namespace Sabberworm\CSS\Property;

/**
* CSSNamespace represents an @namespace rule.
*/
class CSSNamespace {
	private $mUrl;
	private $sPrefix;
	
	public function __construct($mUrl, $sPrefix = null) {
		$this->mUrl = $mUrl;
		$this->sPrefix = $sPrefix;
	}
	
	public function __toString() {
		return '@namespace '.($this->sPrefix === null ? '' : $this->sPrefix.' ').$this->mUrl->__toString().';';
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

}