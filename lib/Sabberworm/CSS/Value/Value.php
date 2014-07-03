<?php

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\Renderable;

abstract class Value implements Renderable {
	public abstract function __toString();
	public abstract function render(\Sabberworm\CSS\OutputFormat $oOutputFormat);
}
