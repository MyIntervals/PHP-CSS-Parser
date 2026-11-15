<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Value\CSSFunction;
use Sabberworm\CSS\Value\Size;

/**
 * @covers \Sabberworm\CSS\Value\CSSFunction
 * @covers \Sabberworm\CSS\Value\Value
 * @covers \Sabberworm\CSS\Value\ValueList
 */
final class CSSFunctionTest extends TestCase
{
    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new CSSFunction('filter', []);

        $result = $subject->getArrayRepresentation();

        self::assertSame('CSSFunction', $result['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesFunctionName(): void
    {
        $subject = new CSSFunction('filter', []);

        $result = $subject->getArrayRepresentation();

        self::assertSame('filter', $result['name']);
    }

    /**
     * @test
     */
    public function getNameReturnsNameProvidedToConstructor(): void
    {
        $name = 'filter';
        $subject = new CSSFunction($name, []);

        self::assertSame($name, $subject->getName());
    }

    /**
     * @test
     */
    public function setNameSetsName(): void
    {
        $subject = new CSSFunction('filter', []);
        $name = 'blur';

        $subject->setName($name);

        self::assertSame($name, $subject->getName());
    }

    /**
     * @test
     */
    public function getArgumentsReturnsArgumentsProvidedToConstructor(): void
    {
        $arguments = [new Size(1), new Size(2)];
        $subject = new CSSFunction('rgb', $arguments);

        self::assertSame($arguments, $subject->getArguments());
    }
}
