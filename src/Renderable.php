<?php

declare(strict_types=1);

namespace Sabberworm\CSS;

interface Renderable
{
    public function render(OutputFormat $outputFormat): string;

    /**
     * @return array<string, bool|int|float|string|list<array<string, mixed>>>
     *
     * @internal
     */
    public function getArrayRepresentation(): array;
}
