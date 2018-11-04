<?php

namespace Sabberworm\CSS\RuleSet\Selector;

use Sabberworm\CSS\Renderable;

abstract class PseudoSelector extends SelectorPart {
	private $mValue;
	private $bIsPseudoElement;

	public function __construct($mValue, $bIsPseudoElement = false, $iLineNo = 0) {
		parent::__construct($iLineNo);
		$this->mValue = $mValue;
		$this->bIsPseudoElement = $bIsPseudoElement;
	}

	public function getValue() {
		return $this->mValue;
	}

	public function setValue($mValue) {
		$this->mValue = $mValue;
	}

	public function isPseudoFunction() {
		return $this->mValue instanceof CSSFunction;
	}

	public function isPseudoElement() {
		return $bIsPseudoElement;
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$sValue = $this->mValue;
		if($sValue instanceof Renderable) {
			$sValue = $sValue->render($oOutputFormat);
		}
		return ':' . ($this->bIsPseudoElement ? ':' : '') . $sValue;
	}
}
