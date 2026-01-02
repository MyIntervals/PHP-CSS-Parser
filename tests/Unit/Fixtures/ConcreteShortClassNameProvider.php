<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Fixtures;

use Sabberworm\CSS\ShortClassNameProvider;

final class ConcreteShortClassNameProvider
{
    use ShortClassNameProvider;

    /**
     * Public wrapper for private method to allow testing.
     *
     * @return non-empty-string
     */
    public function getTheShortClassName(): string
    {
        return $this->getShortClassName();
    }
}
