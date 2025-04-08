<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\RuleSet\DeclarationBlock;

/**
 * @covers \Sabberworm\CSS\RuleSet\DeclarationBlock
 */
final class DeclarationBlockTest extends TestCase
{
    /**
     * @var DeclarationBlock
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new DeclarationBlock();
    }

    /**
     * @test
     */
    public function implementsCSSListItem(): void
    {
        self::assertInstanceOf(CSSListItem::class, $this->subject);
    }
}
