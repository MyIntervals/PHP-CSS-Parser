<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\Property\CSSNamespace;
use Sabberworm\CSS\Value\CSSString;

/**
 * @covers \Sabberworm\CSS\Property\CSSNamespace
 */
final class CSSNamespaceTest extends TestCase
{
    /**
     * @var CSSNamespace
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new CSSNamespace(new CSSString('http://www.w3.org/2000/svg'));
    }

    /**
     * @test
     */
    public function implementsCSSListItem(): void
    {
        self::assertInstanceOf(CSSListItem::class, $this->subject);
    }
}
