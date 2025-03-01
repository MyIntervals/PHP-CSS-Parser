<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property;

/**
 * Utility class to calculate the specificity of a CSS selector.
 *
 * The results are cached to avoid recalculating the specificity of the same selector multiple times.
 *
 * @internal
 */
final class DependencyCalculator
{
    /**
     * regexp for specificity calculations
     *
     * @var non-empty-string
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
     * @var non-empty-string
     */
    private const ELEMENTS_AND_PSEUDO_ELEMENTS_RX = '/
        ((^|[\\s\\+\\>\\~]+)[\\w]+   # elements
        |
        \\:{1,2}(                    # pseudo-elements
            after|before|first-letter|first-line|selection
        ))
        /ix';

    /**
     * @var array<string, int<0, max>>
     */
    private static $specificityCache = [];

    /**
     * @return int<0, max>
     */
    public static function calculateSpecificity(string $selector): int
    {
        if (!isset(self::$specificityCache[$selector])) {
            $a = 0;
            /// @todo should exclude \# as well as "#"
            $aMatches = null;
            $b = \substr_count($selector, '#');
            $c = \preg_match_all(self::NON_ID_ATTRIBUTES_AND_PSEUDO_CLASSES_RX, $selector, $aMatches);
            $d = \preg_match_all(self::ELEMENTS_AND_PSEUDO_ELEMENTS_RX, $selector, $aMatches);
            self::$specificityCache[$selector] = ($a * 1000) + ($b * 100) + ($c * 10) + $d;
        }

        return self::$specificityCache[$selector];
    }
}
