<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\OutputFormat;

class CalcRuleValueList extends RuleValueList
{
    /**
     * @param int<0, max> $lineNumber
     */
    public function __construct($lineNumber = 0)
    {
        parent::__construct(',', $lineNumber);
    }

    public function render(OutputFormat $outputFormat): string
    {
        return $outputFormat->implode(' ', $this->aComponents);
    }
}
