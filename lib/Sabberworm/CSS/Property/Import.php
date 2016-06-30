<?php

namespace Sabberworm\CSS\Property;

use Sabberworm\CSS\Value\URL;

/**
* Class representing an @import rule.
*/
class Import implements AtRule {
	private $oLocation;
	private $sMediaQuery;
	protected $iLineNo;
	
	public function __construct(URL $oLocation, $sMediaQuery, $iLineNo = 0) {
		$this->oLocation = $oLocation;
		$this->sMediaQuery = $sMediaQuery;
		$this->iLineNo = $iLineNo;
	}

	/**
	 * @return int
	 */
	public function getLineNo() {
		return $this->iLineNo;
	}

	public function setLocation($oLocation) {
			$this->oLocation = $oLocation;
	}

	public function getLocation() {
			return $this->oLocation;
	}
	
	public function __toString() {
		return $this->render(new \Sabberworm\CSS\OutputFormat());
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return "@import ".$this->oLocation->render($oOutputFormat).($this->sMediaQuery === null ? '' : ' '.$this->sMediaQuery).';';
	}

	public function atRuleName() {
		return 'import';
	}

	public function atRuleArgs() {
		$aResult = array($this->oLocation);
		if($this->sMediaQuery) {
			array_push($aResult, $this->sMediaQuery);
		}
		return $aResult;
	}
}