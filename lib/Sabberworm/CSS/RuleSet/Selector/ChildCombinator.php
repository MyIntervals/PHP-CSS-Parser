<?php

namespace Sabberworm\CSS\RuleSet\Selector;

abstract class ChildCombinator extends SelectorPart {
	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return $oOutputFormat->spaceBeforeSelectorCombinator().'>'.$oOutputFormat->spaceAfterSelectorCombinator();
	}
}
