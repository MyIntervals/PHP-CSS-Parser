<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Value;

/**
 * This class is used to represent all multivalued rules like `font: bold 12px/3 Helvetica, Verdana, sans-serif;`
 * (where the value would be a whitespace-separated list of the primitive value `bold`, a slash-separated list
 * and a comma-separated list).
 */
class RuleValueList extends ValueList
{
    /**
     * @param non-empty-string $separator
     * @param int<0, max> $lineNumber
     */
    public function __construct(string $separator = ',', int $lineNumber = 0)
    {
        parent::__construct([], $separator, $lineNumber);
    }
}
