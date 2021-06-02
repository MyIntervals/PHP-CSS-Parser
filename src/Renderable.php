<?php

namespace Sabberworm\CSS;

interface Renderable
{
    public function __toString();

    /**
     * @param OutputFormat $oOutputFormat
     *
     * @return string
     */
    public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat);

    public function getLineNo();
}
