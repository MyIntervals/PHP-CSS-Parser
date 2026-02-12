<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Rule;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Property\Declaration;
use Sabberworm\CSS\Rule\Rule;

/**
 * @covers \Sabberworm\CSS\Rule\Rule
 */
final class RuleTest extends TestCase
{
    /**
     * @test
     */
    public function isAliasedToPropertyDeclaration(): void
    {
        $subject = new Rule('beverage-container');

        self::assertInstanceOf(Declaration::class, $subject);
    }
}
