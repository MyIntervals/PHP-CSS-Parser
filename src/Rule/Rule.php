<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Rule;

use Sabberworm\CSS\Property\Declaration;

use function Safe\class_alias;

if (!\class_exists(Rule::class, false) && !\interface_exists(Rule::class, false)) {
    /**
     * @deprecated in v9.2, will be removed in v10.0.  Use `Property\Declaration` instead, which is a direct
     *             replacement.
     */
    class_alias(Declaration::class, Rule::class);
}
