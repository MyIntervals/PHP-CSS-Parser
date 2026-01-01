<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Fixtures;

use Sabberworm\CSS\ArrayRepresentationClassProvider;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Renderable;

final class ConcreteArrayRepresentationClassProvider implements Renderable
{
    use ArrayRepresentationClassProvider;

    /**
     * @return never
     */
    public function render(OutputFormat $outputFormat): string
    {
        throw new \BadMethodCallException('Nothing to see here :/', 1767308395);
    }
}
