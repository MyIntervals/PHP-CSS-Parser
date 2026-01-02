<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Tests\Unit\Fixtures\ConcreteShortClassNameProvider;

/**
 * @covers \Sabberworm\CSS\ShortClassNameProvider
 */
final class ShortClassNameProviderTest extends TestCase
{
    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new ConcreteShortClassNameProvider();

        $result = $subject->getTheShortClassName();

        self::assertSame('ConcreteShortClassNameProvider', $result);
    }
}
