<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property;

use Sabberworm\CSS\OutputFormat;
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
     */
    private const NON_ID_ATTRIBUTES_AND_PSEUDO_CLASSES_RX = '/
        (\\.[\\w]+)                   # classes
        |
        \\[(\\w+)                     # attributes
        |
        (\\:(                         # pseudo classes
            link|visited|active
            |hover|focus
            |lang
            |target
            |enabled|disabled|checked|indeterminate
            |root
            |nth-child|nth-last-child|nth-of-type|nth-last-of-type
            |first-child|last-child|first-of-type|last-of-type
            |only-child|only-of-type
            |empty|contains
        ))
        /ix';

    /**
     * regexp for specificity calculations
     *
     * @var string
     */
    private const ELEMENTS_AND_PSEUDO_ELEMENTS_RX = '/
        ((^|[\\s\\+\\>\\~]+)[\\w]+   # elements
        |
        \\:{1,2}(                    # pseudo-elements
            after|before|first-letter|first-line|selection
        ))
        /ix';

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
                [a-zA-Z0-9\\x{00A0}-\\x{FFFF}_^$|*="\'~\\[\\]()\\-\\s\\.:#+>]* # any sequence of valid unescaped characters
                (?:\\\\.)?                                                     # a single escaped character
                (?:([\'"]).*?(?<!\\\\)\\2)?                                    # a quoted text like [id="example"]
            )*
        )$
        /ux';

    /**
     * @var string
     */
    private $selector;

    /**
     * @return bool
     *
     * @internal since V8.8.0
     */
    public static function isValid(string $selector)
    {
        return \preg_match(static::SELECTOR_VALIDATION_RX, $selector);
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
     * @deprecated in V8.8.0, will be removed in V9.0.0. Use `render` instead.
     */
    public function __toString(): string
    {
        return $this->getSelector();
    }

    /**
     * @return int<0, max>
     */
    public function getSpecificity(): int
    {
        $a = 0;
        // @todo should exclude \# as well as "#"
        $aMatches = null;
        $b = \substr_count($this->selector, '#');
        $c = \preg_match_all(self::NON_ID_ATTRIBUTES_AND_PSEUDO_CLASSES_RX, $this->selector, $aMatches);
        $d = \preg_match_all(self::ELEMENTS_AND_PSEUDO_ELEMENTS_RX, $this->selector, $aMatches);

        return ($a * 1000) + ($b * 100) + ($c * 10) + $d;
    }

    public function render(OutputFormat $outputFormat): string
    {
        return $this->getSelector();
    }
}
