<?php

declare(strict_types=1);

namespace Sabberworm\CSS;

interface Renderable
{
    public function __toString(): string;

    public function render(OutputFormat $oOutputFormat): string;

    /**
     * @return int<0, max>
     */
    public function getLineNo(): int;
}
