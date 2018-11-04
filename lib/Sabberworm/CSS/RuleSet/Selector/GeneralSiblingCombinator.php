<?php

namespace Sabberworm\CSS\RuleSet\Selector;

abstract class GeneralSiblingCombinator extends SelectorPart {
	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return $oOutputFormat->spaceBeforeSelectorCombinator().'~'.$oOutputFormat->spaceAfterSelectorCombinator();
	}
}
