<?php

declare(strict_types=1);

namespace Sabberworm\CSS\RuleSet;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Property\AtRule;

/**
 * This class represents rule sets for generic at-rules which are not covered by specific classes, i.e., not
 * `@import`, `@charset` or `@media`.
 *
 * A common example for this is `@font-face`.
 */
class AtRuleSet extends RuleSet implements AtRule
{
    /**
     * @var non-empty-string
     */
    private $type;

    /**
     * @var string
     */
    private $arguments;

    /**
     * @param non-empty-string $type
     * @param int<0, max> $lineNumber
     */
    public function __construct(string $type, string $arguments = '', int $lineNumber = 0)
    {
        parent::__construct($lineNumber);
        $this->type = $type;
        $this->arguments = $arguments;
    }

    /**
     * @return non-empty-string
     */
    public function atRuleName(): string
    {
        return $this->type;
    }

    public function atRuleArgs(): string
    {
        return $this->arguments;
    }

    /**
     * @return non-empty-string
     */
    public function render(OutputFormat $outputFormat): string
    {
        $formatter = $outputFormat->getFormatter();
        $result = $formatter->comments($this);
        $arguments = $this->arguments;
        if ($arguments !== '') {
            $arguments = ' ' . $arguments;
        }
        $result .= "@{$this->type}$arguments{$formatter->spaceBeforeOpeningBrace()}{";
        $result .= $this->renderRules($outputFormat);
        $result .= '}';
        return $result;
    }
}
