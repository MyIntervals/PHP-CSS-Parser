<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\CSSList\Fixtures;

use Sabberworm\CSS\CSSList\CSSList;
use Sabberworm\CSS\OutputFormat;

final class ConcreteCSSList extends CSSList
{
    /**
     * @return never
     */
    public function isRootList()
    {
        throw new \BadMethodCallException('Not implemented', 1740395831);
    }

    /**
     * @param OutputFormat|null $outputFormat
     *
     * @return never
     */
    public function render($outputFormat)
    {
        throw new \BadMethodCallException('Not implemented', 1740395836);
    }
}
