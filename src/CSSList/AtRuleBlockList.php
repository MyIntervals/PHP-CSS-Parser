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
     * @param int<0, max> $lineNumber
     */
    public function __construct(string $type, string $arguments = '', int $lineNumber = 0)
    {
        parent::__construct($lineNumber);
        $this->type = $type;
        $this->arguments = $arguments;
    }

    public function atRuleName(): string
    {
        return $this->type;
    }

    public function atRuleArgs(): string
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
        $result .= $outputFormat->getContentBeforeAtRuleBlock();
        $arguments = $this->arguments;
        if ($arguments) {
            $arguments = ' ' . $arguments;
        }
        $result .= "@{$this->type}$arguments{$outputFormat->spaceBeforeOpeningBrace()}{";
        $result .= $this->renderListContents($outputFormat);
        $result .= '}';
        $result .= $outputFormat->getContentAfterAtRuleBlock();
        return $result;
    }

    public function isRootList(): bool
    {
        return false;
    }
}
