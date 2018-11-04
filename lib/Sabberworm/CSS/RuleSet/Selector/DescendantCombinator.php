<?php

namespace Sabberworm\CSS\RuleSet\Selector;

abstract class DescendantCombinator extends SelectorPart {
	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return ' ';
	}
}
