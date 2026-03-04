<?php

declare(strict_types=1);

namespace Sabberworm\CSS\RuleSet;

use function Safe\class_alias;

if (!\class_exists(RuleContainer::class, false) && !\interface_exists(RuleContainer::class, false)) {
    class_alias(DeclarationList::class, RuleContainer::class);
    // The test is expected to evaluate to false,
    // but allows for the deprecation notice to be picked up by IDEs like PHPStorm.
    // @phpstan-ignore booleanNot.alwaysTrue, booleanNot.alwaysTrue, booleanAnd.alwaysTrue
    if (!\class_exists(RuleContainer::class, false) && !\interface_exists(RuleContainer::class, false)) {
        /**
         * @deprecated in v9.2, will be removed in v10.0.  Use `DeclarationList` instead, which is a direct replacement.
         */
        interface RuleContainer {}
    }
}
