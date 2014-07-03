<?php

namespace Sabberworm\CSS\Value;

abstract class Value {
	public abstract function __toString();
	public abstract function render(\Sabberworm\CSS\OutputFormat $oOutputFormat);
}
