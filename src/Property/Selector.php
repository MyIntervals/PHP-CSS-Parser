<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Property\Selector\SpecificityCalculator;
use Sabberworm\CSS\Renderable;

/**
 * Class representing a single CSS selector. Selectors have to be split by the comma prior to being passed into this
 * class.
 */
class Selector implements Renderable
{
    /**
     * regexp for specificity calculations
     *
     * @var string
     *
     * @internal since 8.5.2
     */
    public const SELECTOR_VALIDATION_RX = '/
        ^(
            (?:
                [a-zA-Z0-9\\x{00A0}-\\x{FFFF}_^$|*="\'~\\[\\]()\\-\\s\\.:#+>,]* # any sequence of valid unescaped characters
                (?:\\\\.)?                                                      # a single escaped character
                (?:([\'"]).*?(?<!\\\\)\\2)?                                     # a quoted text like [id="example"]
            )*
        )$
        /ux';

    /**
     * @var string
     */
    private $selector;

    /**
     * @internal since V8.8.0
     */
    public static function isValid(string $selector): bool
    {
        // Note: We need to use `static::` here as the constant is overridden in the `KeyframeSelector` class.
        $numberOfMatches = \preg_match(static::SELECTOR_VALIDATION_RX, $selector);

        return $numberOfMatches === 1;
    }

    public function __construct(string $selector)
    {
        $this->setSelector($selector);
    }

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function setSelector(string $selector): void
    {
        $this->selector = \trim($selector);
    }

    /**
     * @return int<0, max>
     */
    public function getSpecificity(): int
    {
        return SpecificityCalculator::calculate($this->selector);
    }

    public function render(OutputFormat $outputFormat): string
    {
        return $this->getSelector();
    }
}
