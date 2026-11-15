<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Tests\Unit\Value\Fixtures\ConcreteValueList;
use Sabberworm\CSS\Value\Size;

/**
 * @covers \Sabberworm\CSS\Value\ValueList
 */
final class ValueListTest extends TestCase
{
    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new ConcreteValueList();

        $result = $subject->getArrayRepresentation();

        self::assertSame('ConcreteValueList', $result['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesStringComponent(): void
    {
        $subject = new ConcreteValueList(['Helvetica']);

        $result = $subject->getArrayRepresentation();

        self::assertSame('Helvetica', $result['components'][0]['value']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesValueComponent(): void
    {
        $subject = new ConcreteValueList([new Size(1)]);

        $result = $subject->getArrayRepresentation();

        self::assertSame('Size', $result['components'][0]['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesMultipleMixedComponents(): void
    {
        $subject = new ConcreteValueList([new Size(1), '+', new Size(2)]);

        $result = $subject->getArrayRepresentation();

        self::assertSame('Size', $result['components'][0]['class']);
        self::assertSame('+', $result['components'][1]['value']);
        self::assertSame('Size', $result['components'][2]['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesSeparator(): void
    {
        $subject = new ConcreteValueList();

        $result = $subject->getArrayRepresentation();

        self::assertSame(',', $result['separator']);
    }

    /**
     * @test
     */
    public function constructorWrapsSingleComponentInArray(): void
    {
        $component = 'Helvetica';

        $subject = new ConcreteValueList($component);

        self::assertSame([$component], $subject->getListComponents());
    }

    /**
     * @test
     */
    public function setListComponentsReplacesComponents(): void
    {
        $subject = new ConcreteValueList(['Helvetica']);
        $components = ['Arial', 'sans-serif'];

        $subject->setListComponents($components);

        self::assertSame($components, $subject->getListComponents());
    }

    /**
     * @test
     */
    public function setListSeparatorSetsSeparator(): void
    {
        $subject = new ConcreteValueList();

        $separator = '/';

        $subject->setListSeparator($separator);

        self::assertSame($separator, $subject->getListSeparator());
    }
}
