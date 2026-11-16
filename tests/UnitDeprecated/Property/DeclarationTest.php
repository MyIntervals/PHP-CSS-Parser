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
        $propertyName = 'color';
        $subject = new Declaration($propertyName);

        self::assertSame($propertyName, $subject->getRule());
    }

    /**
     * @test
     */
    public function setRuleSetsPropertyName(): void
    {
        $subject = new Declaration('color');
        $propertyName = 'background-color';

        $subject->setRule($propertyName);

        self::assertSame($propertyName, $subject->getPropertyName());
    }
}
