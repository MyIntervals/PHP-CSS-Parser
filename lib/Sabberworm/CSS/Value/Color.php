<?php

namespace Sabberworm\CSS\Value;

class Color extends CSSFunction {

	public function __construct($aColor) {
		parent::__construct(implode('', array_keys($aColor)), $aColor);
	}

	public function getColor() {
		return $this->aComponents;
	}

	public function setColor($aColor) {
		$this->setName(implode('', array_keys($aColor)));
		$this->aComponents = $aColor;
	}

	public function getColorDescription() {
		return $this->getName();
	}

}