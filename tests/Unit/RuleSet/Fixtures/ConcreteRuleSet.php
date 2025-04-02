<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet\Fixtures;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\RuleSet\RuleSet;

final class ConcreteRuleSet extends RuleSet
{
    public function render(OutputFormat $outputFormat): string
    {
        throw new \BadMethodCallException('Nothing to see here :/', 1744067015);
    }
}
