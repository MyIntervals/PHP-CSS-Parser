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

	public function __toString() {
		if (isset($this->aComponents['a'])) {
            return parent::__toString();
        }

		$out = sprintf(
		    '%o2x%02x%02x',
		    $this->aComponents['r'],
		    $this->aComponents['g'],
		    $this->aComponents['b'],
        );

		return (($out[0] == $out[1]) && ($out[2] == $out[3]) && ($out[4] == $out[5]))
		    ? '#' . $out[0] . $out[2] . $out[4]
		    : '#' . $out;
	}

}
