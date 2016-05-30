<?php

namespace Sabberworm\CSS\Value;

class RuleValueList extends ValueList {
	public function __construct($sSeparator = ',', $iLineNum = 0) {
		parent::__construct(array(), $sSeparator, $iLineNum);
	}
}