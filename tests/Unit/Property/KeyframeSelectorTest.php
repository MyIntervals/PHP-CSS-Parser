<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Property\KeyframeSelector;
use Sabberworm\CSS\Property\Selector\CompoundSelector;

/**
 * @covers \Sabberworm\CSS\Property\KeyframeSelector
 * @covers \Sabberworm\CSS\Property\Selector
 */
final class KeyframeSelectorTest extends TestCase
{
    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new KeyframeSelector('50%');

        $result = $subject->getArrayRepresentation();

        self::assertSame('KeyframeSelector', $result['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesComponent(): void
    {
        $subject = new KeyframeSelector([new CompoundSelector('50%')]);

        $result = $subject->getArrayRepresentation();

        self::assertSame('50%', $result['components'][0]['value']);
    }
}
