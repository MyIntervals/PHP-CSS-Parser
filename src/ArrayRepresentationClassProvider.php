<?php

declare(strict_types=1);

namespace Sabberworm\CSS;

/**
 * Provides a reusable implementation of `getArrayRepresentation()` which will populate the `class` element with the
 * short name of the actual class.
 * It is expected that this will be `use`d by (possibly-abstract) base classes, with extended classes calling on to the
 * parent implementation then further populating the array.
 * If a base class using this trait needs to further populate the array, the trait method can be `use`d `as`.
 *
 * @internal
 *
 * @phpstan-require-implements Renderable
 */
trait ArrayRepresentationClassProvider
{
    /**
     * @return array<string, bool|int|float|string|list<array<string, mixed>>>
     *
     * @internal
     */
    public function getArrayRepresentation(): array
    {
        $reflect = new \ReflectionClass($this);

        return ['class' => $reflect->getShortName()];
    }
}
