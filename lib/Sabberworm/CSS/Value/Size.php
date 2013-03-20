<?php

namespace Sabberworm\CSS\Value;

class Size extends PrimitiveValue {

	const ABSOLUTE_SIZE_UNITS = 'px/deg/s/cm/pt/in/pc/cm/mm';
	const RELATIVE_SIZE_UNITS = '%/em/ex';
	const NON_SIZE_UNITS = 'deg/grad/rad/turns/s/ms/Hz/kHz';

	private $fSize;
	private $sUnit;
	private $bIsColorComponent;

	public function __construct($fSize, $sUnit = null, $bIsColorComponent = false) {
		$this->fSize = floatval($fSize);
		$this->sUnit = $sUnit;
		$this->bIsColorComponent = $bIsColorComponent;
	}

	public function setUnit($sUnit) {
		$this->sUnit = $sUnit;
	}

	public function getUnit() {
		return $this->sUnit;
	}

	public function setSize($fSize) {
		$this->fSize = floatval($fSize);
	}

	public function getSize() {
		return $this->fSize;
	}

	public function isColorComponent() {
		return $this->bIsColorComponent;
	}

	/**
	 * Returns whether the number stored in this Size really represents a size (as in a length of something on screen).
	 * @return false if the unit an angle, a duration, a frequency or the number is a component in a Color object.
	 */
	public function isSize() {
		if (in_array($this->sUnit, explode('/', self::NON_SIZE_UNITS))) {
			return false;
		}
		return !$this->isColorComponent();
	}

	public function isRelative() {
		if (in_array($this->sUnit, explode('/', self::RELATIVE_SIZE_UNITS))) {
			return true;
		}
		if ($this->sUnit === null && $this->fSize != 0) {
			return true;
		}
		return false;
	}

	public function __toString() {
		return str_replace('0.', '.', $this->fSize) . ($this->sUnit === null ? '' : $this->sUnit);
	}

}
