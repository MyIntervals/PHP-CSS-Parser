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
     *
     * @return void
     */
    public function implementsCSSElement()
    {
        $subject = new ConcreteRuleSet();

        self::assertInstanceOf(CSSElement::class, $subject);
    }
}
