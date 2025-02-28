<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property;

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
    public function getSelectorByDefaultReturnsSelectorProvidedToConstructor(): void
    {
        $selector = 'a';
        $subject = new Selector($selector);

        self::assertSame($selector, $subject->getSelector());
    }

    /**
     * @test
     */
    public function setSelectorOverwritesSelectorProvidedToConstructor(): void
    {
        $subject = new Selector('a');

        $selector = 'input';
        $subject->setSelector($selector);

        self::assertSame($selector, $subject->getSelector());
    }
}
