<?php

namespace Sabberworm\CSS\CSSList;

use Sabberworm\CSS\Property\AtRule;

class KeyFrame extends CSSList implements AtRule {

	private $vendorKeyFrame;
	private $animationName;

	public function __construct() {
		parent::__construct();
		$this->vendorKeyFrame = null;
		$this->animationName  = null;
	}

	public function setVendorKeyFrame($vendorKeyFrame) {
		$this->vendorKeyFrame = $vendorKeyFrame;
	}

	public function getVendorKeyFrame() {
		return $this->vendorKeyFrame;
	}

	public function setAnimationName($animationName) {
		$this->animationName = $animationName;
	}

	public function getAnimationName() {
		return $this->animationName;
	}

	public function __toString() {
		return $this->render();
	}

	public function render($oOutputFormat = null) {

		if($oOutputFormat === null) {
			$oOutputFormat = new \Sabberworm\CSS\OutputFormat();
		}
		$sResult = "@{$this->vendorKeyFrame} {$this->animationName} {";
		$sResult .= parent::render($oOutputFormat->nextLevel());
		$sResult .= '}';
		return $sResult;
	}

	public function atRuleName() {
		return $this->vendorKeyFrame;
	}

	public function atRuleArgs() {
		return $this->animationName;
	}
}
