<?php

namespace Sabberworm\CSS\CSSList;

use Sabberworm\CSS\Property\AtRule;

class KeyFrame extends CSSList implements AtRule {

	protected $vendorKeyFrame;
	protected $animationName;

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
		return $this->render(new \Sabberworm\CSS\OutputFormat());
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$sResult = "@{$this->vendorKeyFrame} {$this->animationName}{$oOutputFormat->spaceBeforeOpeningBrace()}{";
		$sResult .= parent::render($oOutputFormat);
		$sResult .= '}';
		return $sResult;
	}

	public function isRootList() {
		return false;
	}

	public function atRuleName() {
		return $this->vendorKeyFrame;
	}

	public function atRuleArgs() {
		return $this->animationName;
	}
}
