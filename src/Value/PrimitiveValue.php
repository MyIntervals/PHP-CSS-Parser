<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Value;

abstract class PrimitiveValue extends Value
{
    /**
     * @param int $lineNumber
     *
     * @internal since 8.8.0
     */
    public function __construct($lineNumber = 0)
    {
        parent::__construct($lineNumber);
    }
}
