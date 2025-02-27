<?php

declare(strict_types=1);

namespace Sabberworm\CSS;

interface Renderable
{
    /**
     * @deprecated in V8.8.0, will be removed in V9.0.0. Use `render` instead.
     */
    public function __toString(): string;

    public function render(OutputFormat $outputFormat): string;

    /**
     * @return int<0, max>
     */
    public function getLineNo(): int;
}
