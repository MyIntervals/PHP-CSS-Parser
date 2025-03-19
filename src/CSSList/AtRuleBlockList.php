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
        $result .= $outputFormat->getContentBeforeAtRuleBlock();
        $arguments = $this->arguments;
        if ($arguments) {
            $arguments = ' ' . $arguments;
        }
        $result .= "@{$this->type}$arguments{$formatter->spaceBeforeOpeningBrace()}{";
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
