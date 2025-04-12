<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet\Fixtures;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\RuleSet\RuleSet;

final class ConcreteRuleSet extends RuleSet
{
    /**
     * @param OutputFormat|null $outputFormat
     *
     * @return never
     */
    public function render($outputFormat)
    {
        throw new \BadMethodCallException('Nothing to see here :/', 1744067015);
    }
}
