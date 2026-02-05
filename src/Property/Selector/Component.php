<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property\Selector;

use Sabberworm\CSS\Renderable;

/**
 * This interface is for a class that represents a part of a selector which is either a compound selector (or a simple
 * selector, which is effectively a compound selector without any compounding) or a selector combinator.
 *
 * It allows a selector to be represented as an array of objects that implement this interface.
 * This is the formal definition:
 * selector = compound-selector [combinator, compound-selector]*
 *
 * The selector is comprised of an array of alternating types that can't be easily represented in a type-safe manner
 * without this.
 *
 * 'Selector component' is not a known grammar in the spec, but a convenience for the implementation.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/CSS/Guides/Selectors/Selector_structure
 * @see https://www.w3.org/TR/selectors-4/#structure
 */
interface Component extends Renderable
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
