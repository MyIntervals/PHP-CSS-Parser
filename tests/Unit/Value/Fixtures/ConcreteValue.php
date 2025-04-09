<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value\Fixtures;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Value\Value;

final class ConcreteValue extends Value
{
    /**
     * @return never
     */
    public function render(OutputFormat $outputFormat): string
    {
        throw new \BadMethodCallException('Nothing to see here :/', 1744067951);
    }
}
