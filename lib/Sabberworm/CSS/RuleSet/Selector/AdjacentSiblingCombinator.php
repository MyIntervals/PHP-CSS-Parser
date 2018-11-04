<?php

namespace Sabberworm\CSS\RuleSet\Selector;

abstract class AdjacentSiblingCombinator extends SelectorPart {
	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return $oOutputFormat->spaceBeforeSelectorCombinator().'+'.$oOutputFormat->spaceAfterSelectorCombinator();
	}
}
