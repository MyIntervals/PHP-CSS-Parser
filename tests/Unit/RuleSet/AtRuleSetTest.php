<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\RuleSet\AtRuleSet;

/**
 * @covers \Sabberworm\CSS\RuleSet\AtRuleSet
 */
final class AtRuleSetTest extends TestCase
{
    /**
     * @var AtRuleSet
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new AtRuleSet('supports');
    }

    /**
     * @test
     */
    public function implementsCSSListItem(): void
    {
        self::assertInstanceOf(CSSListItem::class, $this->subject);
    }
}
