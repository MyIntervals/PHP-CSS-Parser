<?php

declare(strict_types=1);

namespace Sabberworm\CSS\RuleSet;

use function Safe\class_alias;

/**
 * @deprecated in v9.2, will be removed in v10.0.  Use `DeclarationList` instead, which is a direct replacement.
 */
class_alias(DeclarationList::class, RuleContainer::class);
