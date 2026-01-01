<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Property\KeyframeSelector;

/**
 * @covers \Sabberworm\CSS\Property\KeyframeSelector
 * @covers \Sabberworm\CSS\Property\Selector
 */
final class KeyframeSelectorTest extends TestCase
{
    /**
     * @test
     */
    public function getArrayRepresentationThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $subject = new KeyframeSelector('a');

        $subject->getArrayRepresentation();
    }
}
