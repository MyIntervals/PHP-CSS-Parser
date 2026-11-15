<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\UnitDeprecated\Property;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Property\Declaration;

/**
 * @covers \Sabberworm\CSS\Property\Declaration
 */
final class DeclarationTest extends TestCase
{
    /**
     * @test
     */
    public function getRuleReturnsPropertyName(): void
    {
        $subject = new Declaration('color');

        self::assertSame('color', $subject->getRule());
    }

    /**
     * @test
     */
    public function setRuleSetsPropertyName(): void
    {
        $subject = new Declaration('color');

        $subject->setRule('background-color');

        self::assertSame('background-color', $subject->getPropertyName());
    }
}
