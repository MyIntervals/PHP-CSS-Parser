<?php

namespace Sabberworm\CSS\Value;

class RuleValueList extends ValueList
{
    public function __construct($sSeparator = ',', $iLineNo = 0)
    {
        parent::__construct([], $sSeparator, $iLineNo);
    }
}
