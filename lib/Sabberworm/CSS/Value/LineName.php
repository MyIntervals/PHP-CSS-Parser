<?php

namespace Sabberworm\CSS\Value;

class LineName extends ValueList {
	public function __construct($aComponents = array(), $iLineNo = 0) {
		parent::__construct($aComponents, ' ', $iLineNo);
	}

	public function __toString() {
		return $this->render(new \Sabberworm\CSS\OutputFormat());
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return '[' . parent::render(\Sabberworm\CSS\OutputFormat::createCompact()) . ']';
	}

}
