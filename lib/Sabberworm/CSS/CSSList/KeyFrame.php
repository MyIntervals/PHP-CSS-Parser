<?php

namespace Sabberworm\CSS\CSSList;


class KeyFrame extends CSSList {

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
		$sResult = "@{$this->vendorKeyFrame} {$this->animationName} {";
		$sResult .= parent::__toString();
		$sResult .= '}';
		return $sResult;
	}

}
