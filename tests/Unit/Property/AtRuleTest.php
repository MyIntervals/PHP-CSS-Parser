<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Property\AtRule;

/**
 * @coversNothing
 */
final class AtRuleTest extends TestCase
{
    /**
     * @test
     */
    public function blockRulesConstantIsCorrect(): void
    {
        self::assertEqualsCanonicalizing(
            ['media', 'document', 'supports', 'region-style', 'font-feature-values', 'container'],
            explode('/', AtRule::BLOCK_RULES)
        );
    }
}
