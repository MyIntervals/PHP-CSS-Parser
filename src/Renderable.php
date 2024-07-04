<?php

namespace Sabberworm\CSS;

interface Renderable
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * @param OutputFormat $oOutputFormat
     *
     * @return string
     */
    public function render($oOutputFormat);

    /**
     * @return int
     */
    public function getLineNo();
}
