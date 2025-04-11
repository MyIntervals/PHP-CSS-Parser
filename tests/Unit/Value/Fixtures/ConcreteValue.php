<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value\Fixtures;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Value\Value;

final class ConcreteValue extends Value
{
    /**
     * @param OutputFormat|null $outputFormat
     *
     * @return never
     */
    public function render($outputFormat)
    {
        throw new \BadMethodCallException('Nothing to see here :/', 1744067951);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render(new OutputFormat());
    }
}
