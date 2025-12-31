<?php

declare(strict_types=1);

namespace Sabberworm\CSS;

interface Renderable
{
    public function render(OutputFormat $outputFormat): string;

    /**
     * @internal
     *
     * @return array<string, bool|int|float|string|list<array<string, mixed>>>
     */
    public function getArrayRepresentation(): array;
}
