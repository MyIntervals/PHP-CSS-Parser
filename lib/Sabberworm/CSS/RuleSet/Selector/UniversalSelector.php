<?php

namespace Sabberworm\CSS\RuleSet\Selector;

use Sabberworm\CSS\Renderable;

abstract class UniversalSelector extends SelectorPart {
	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return '*';
	}
}
