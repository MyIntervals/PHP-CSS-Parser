<?php

namespace Sabberworm\CSS\Value;

abstract class ValueList extends Value {

	protected $aComponents;
	protected $sSeparator;

	public function __construct($aComponents = array(), $sSeparator = ',') {
		if (!is_array($aComponents)) {
			$aComponents = array($aComponents);
		}
		$this->aComponents = $aComponents;
		$this->sSeparator = $sSeparator;
	}

	public function addListComponent($mComponent) {
		$this->aComponents[] = $mComponent;
	}

	public function getListComponents() {
		return $this->aComponents;
	}

	public function setListComponents($aComponents) {
		$this->aComponents = $aComponents;
	}

	public function getListSeparator() {
		return $this->sSeparator;
	}

	public function setListSeparator($sSeparator) {
		$this->sSeparator = $sSeparator;
	}

	public function __toString() {
		return $this->render();
	}

	public function render($oOutputFormat = null) {
		if($oOutputFormat === null) {
			$oOutputFormat = new \Sabberworm\CSS\OutputFormat();
		}
		return implode($this->sSeparator, $this->aComponents);
	}

}
