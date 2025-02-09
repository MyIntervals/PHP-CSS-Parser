<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\OutputFormat;

class CalcRuleValueList extends RuleValueList
{
    /**
     * @param int $lineNumber
     *
     * @internal since 8.8.0
     */
    public function __construct($lineNumber = 0)
    {
        parent::__construct(',', $lineNumber);
    }

    /**
     * @return string
     */
    public function render(OutputFormat $oOutputFormat)
    {
        return $oOutputFormat->implode(' ', $this->aComponents);
    }
}
