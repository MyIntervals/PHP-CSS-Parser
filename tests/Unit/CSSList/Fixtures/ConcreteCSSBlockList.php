<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\CSSList\Fixtures;

use Sabberworm\CSS\CSSList\CSSBlockList;
use Sabberworm\CSS\OutputFormat;

final class ConcreteCSSBlockList extends CSSBlockList
{
    public function isRootList(): bool
    {
        throw new \BadMethodCallException('Not implemented', 1740395831);
    }

    public function render(OutputFormat $outputFormat): string
    {
        throw new \BadMethodCallException('Not implemented', 1740395836);
    }
}
