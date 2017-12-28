<?php

namespace Sabberworm\CSS;

use Sabberworm\CSS\OutputFormat;

interface Renderable {

	/**
	 * @return string
	 */
	public function __toString();

	/**
	 * Renders this component
	 *
	 * @param OutputFormat $oOutputFormat Formatting options
	 *
	 * @return string Rendered CSS
	 */
	public function render(OutputFormat $oOutputFormat);

	/**
	 * @return int Line number
	 */
	public function getLineNo();
}
