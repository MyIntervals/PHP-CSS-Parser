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
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $arguments;

    /**
     * @param string $type
     * @param string $arguments
     * @param int<0, max> $lineNumber
     */
    public function __construct($type, $arguments = '', int $lineNumber = 0)
    {
        parent::__construct($lineNumber);
        $this->type = $type;
        $this->arguments = $arguments;
    }

    /**
     * @return string
     */
    public function atRuleName()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function atRuleArgs()
    {
        return $this->arguments;
    }

    /**
     * @deprecated in V8.8.0, will be removed in V9.0.0. Use `render` instead.
     */
    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $outputFormat): string
    {
        $result = $outputFormat->comments($this);
        $arguments = $this->arguments;
        if ($arguments) {
            $arguments = ' ' . $arguments;
        }
        $result .= "@{$this->type}$arguments{$outputFormat->spaceBeforeOpeningBrace()}{";
        $result .= $this->renderRules($outputFormat);
        $result .= '}';
        return $result;
    }
}
