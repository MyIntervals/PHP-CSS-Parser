<?php

declare(strict_types=1);

use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\RuleContainer;
use Sabberworm\CSS\Property\Declaration;
use Sabberworm\CSS\RuleSet\DeclarationList;

use function Safe\class_alias;

/**
 * @deprecated in v9.2, will be removed in v10.0.  Use `Property\Declaration` instead, which is a direct replacement.
 */
class_alias(Declaration::class, Rule::class);

/**
 * @deprecated in v9.2, will be removed in v10.0.  Use `DeclarationList` instead, which is a direct replacement.
 */
class_alias(DeclarationList::class, RuleContainer::class);
