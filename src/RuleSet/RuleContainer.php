<?php

declare(strict_types=1);

namespace Sabberworm\CSS\RuleSet;

use function Safe\class_alias;

if (!\class_exists(RuleContainer::class, false) && !\interface_exists(RuleContainer::class, false)) {
    class_alias(DeclarationList::class, RuleContainer::class);
    // The test is expected to evuluate to false,
    // but allows for the deprecation notice to be picked up by IDEs like PHPStorm.
    // @phpstan-ignore-next-line
    if (!\class_exists(RuleContainer::class, false) && !\interface_exists(RuleContainer::class, false)) {
        /**
         * @deprecated in v9.2, will be removed in v10.0.  Use `DeclarationList` instead, which is a direct replacement.
         */
        interface RuleContainer {}
    }
}
