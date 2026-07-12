<?php

declare(strict_types=1);

namespace Sabberworm\CSS\RuleSet;

// @phpstan-ignore function.impossibleType
if (!\class_exists(RuleContainer::class, false) && !\interface_exists(RuleContainer::class, false)) {
    /** @phpstan-ignore theCodingMachineSafe.function */
    if (\class_alias(DeclarationList::class, RuleContainer::class) === false) {
        throw new \RuntimeException('Unexpected error');
    }
    // The test is expected to evaluate to false,
    // but allows for the deprecation notice to be picked up by IDEs like PHPStorm.
    // @phpstan-ignore booleanNot.alwaysTrue, booleanAnd.alwaysTrue, function.impossibleType
    if (!\class_exists(RuleContainer::class, false) && !\interface_exists(RuleContainer::class, false)) {
        /**
         * @deprecated in v9.2, will be removed in v10.0.  Use `DeclarationList` instead, which is a direct replacement.
         */
        interface RuleContainer {}
    }
}
