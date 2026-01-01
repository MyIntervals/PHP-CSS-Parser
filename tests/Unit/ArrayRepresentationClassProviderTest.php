<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Tests\Unit\Fixtures\ConcreteArrayRepresentationClassProvider;

/**
 * @covers \Sabberworm\CSS\ArrayRepresentationClassProvider
 */
final class ArrayRepresentationClassProviderTest extends TestCase
{
    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new ConcreteArrayRepresentationClassProvider();

        $result = $subject->getArrayRepresentation();

        self::assertArrayHasKey('class', $result);
        self::assertSame('ConcreteArrayRepresentationClassProvider', $result['class']);
    }
}
