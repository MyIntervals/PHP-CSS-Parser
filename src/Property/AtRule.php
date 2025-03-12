<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property;

use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\Renderable;

interface AtRule extends Renderable, Commentable
{
    /**
     * Since there are more set rules than block rules,
     * we’re whitelisting the block rules and have anything else be treated as a set rule.
     *
     * @var non-empty-string
     *
     * @internal since 8.5.2
     */
    public const BLOCK_RULES = 'media/document/supports/region-style/font-feature-values';

    /**
     * @return non-empty-string
     */
    public function atRuleName(): string;
}
