<?php

declare(strict_types=1);

namespace Sabberworm\CSS\CSSList;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Property\AtRule;

/**
 * A `BlockList` constructed by an unknown at-rule. `@media` rules are rendered into `AtRuleBlockList` objects.
 */
class AtRuleBlockList extends CSSBlockList implements AtRule
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $sArgs;

    /**
     * @param string $type
     * @param string $arguments
     * @param int<0, max> $lineNumber
     */
    public function __construct($type, $arguments = '', $lineNumber = 0)
    {
        parent::__construct($lineNumber);
        $this->type = $type;
        $this->sArgs = $arguments;
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
        return $this->sArgs;
    }

    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $outputFormat): string
    {
        $sResult = $outputFormat->comments($this);
        $sResult .= $outputFormat->sBeforeAtRuleBlock;
        $sArgs = $this->sArgs;
        if ($sArgs) {
            $sArgs = ' ' . $sArgs;
        }
        $sResult .= "@{$this->type}$sArgs{$outputFormat->spaceBeforeOpeningBrace()}{";
        $sResult .= $this->renderListContents($outputFormat);
        $sResult .= '}';
        $sResult .= $outputFormat->sAfterAtRuleBlock;
        return $sResult;
    }

    public function isRootList(): bool
    {
        return false;
    }
}
