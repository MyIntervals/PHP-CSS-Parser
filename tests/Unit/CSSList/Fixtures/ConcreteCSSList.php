<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\CSSList\Fixtures;

use Sabberworm\CSS\CSSList\CSSList;
use Sabberworm\CSS\OutputFormat;

final class ConcreteCSSList extends CSSList
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
