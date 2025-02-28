<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\FunctionalDeprecated\Selector;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Property\Selector;

/**
 * @covers \Sabberworm\CSS\Property\Selector
 */
final class SelectorTest extends TestCase
{
    /**
     * @test
     */
    public function toStringReturnsSelectorPassedToConstructor(): void
    {
        $selector = 'a';
        $subject = new Selector($selector);

        self::assertSame($selector, (string) $subject);
    }
}
