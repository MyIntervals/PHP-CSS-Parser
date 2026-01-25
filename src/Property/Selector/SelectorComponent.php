<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property\Selector;

/**
 * @see https://developer.mozilla.org/en-US/docs/Web/CSS/Guides/Selectors/Selector_structure
 *
 * A complex selector is a sequence of one or more simple and/or compound selectors that are separated by combinators,
 * including the white space descendant combinator.
 *
 * This is essentially a 'selector', as a 'selector list' is a list of comma-separated selectors.
 *
 * A compound selector is a superset that includes a simple selector:
 * - `li` is a simple selector (but can be treated as a compound selector);
 * - `li:last-child` is a compound selector;
 * - `ul li:last-child` is a complex selector as it has a combinator (the whitespace descendent combinator).
 *
 * A (complex) selector can be decomposed thus:
 * selector = compound-selector [combinator, compound-selector]*
 *
 * It's a list of alternating types.
 *
 * This interface covers both types, so they can be put into an array of the interface type.
 */
interface SelectorComponent
{
    /**
     * @return non-empty-string
     */
    public function getValue(): string;

    /**
     * @param non-empty-string $value
     */
    public function setValue(string $value): void;

    /**
     * @return int<0, max>
     */
    public function getSpecificity(): int;
}
