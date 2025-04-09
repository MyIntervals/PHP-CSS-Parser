<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\Tests\Unit\RuleSet\Fixtures\ConcreteRuleSet;

/**
 * @covers \Sabberworm\CSS\RuleSet\RuleSet
 */
final class RuleSetTest extends TestCase
{
    /**
     * @test
     */
    public function implementsCSSElement(): void
    {
        $subject = new ConcreteRuleSet();

        self::assertInstanceOf(CSSElement::class, $subject);
    }
}
