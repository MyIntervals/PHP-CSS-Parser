<?php

namespace Sabberworm\CSS\Value;


class URL extends PrimitiveValue {

	private $oURL;

	public function __construct(String $oURL) {
		$this->oURL = $oURL;
	}

	public function setURL(String $oURL) {
		$this->oURL = $oURL;
	}

	public function getURL() {
		return $this->oURL;
	}

	public function __toString() {
		return $this->render();
	}

	public function render($oOutputFormat = null) {
		if($oOutputFormat === null) {
			$oOutputFormat = new \Sabberworm\CSS\OutputFormat();
		}
		return "url({$this->oURL->render($oOutputFormat)})";
	}

}