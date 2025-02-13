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
    private $arguments;

    /**
     * @param string $type
     * @param string $arguments
     * @param int<0, max> $lineNumber
     */
    public function __construct($type, $arguments = '', $lineNumber = 0)
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

    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $outputFormat): string
    {
        $sResult = $outputFormat->comments($this);
        $sResult .= $outputFormat->sBeforeAtRuleBlock;
        $arguments = $this->arguments;
        if ($arguments) {
            $arguments = ' ' . $arguments;
        }
        $sResult .= "@{$this->type}$arguments{$outputFormat->spaceBeforeOpeningBrace()}{";
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
