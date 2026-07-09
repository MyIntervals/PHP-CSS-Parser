<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Value\RuleValueList;
use Sabberworm\CSS\Value\Size;

/**
 * @covers \Sabberworm\CSS\Value\RuleValueList
 * @covers \Sabberworm\CSS\Value\Value
 * @covers \Sabberworm\CSS\Value\ValueList
 */
final class RuleValueListTest extends TestCase
{
    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new RuleValueList();

        $result = $subject->getArrayRepresentation();

        self::assertSame('RuleValueList', $result['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesStringComponent(): void
    {
        $subject = new RuleValueList();
        $subject->addListComponent('Helvetica');

        $result = $subject->getArrayRepresentation();

        self::assertSame('Helvetica', $result['components'][0]['value']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesValueComponent(): void
    {
        $subject = new RuleValueList();
        $subject->addListComponent(new Size(1));

        $result = $subject->getArrayRepresentation();

        self::assertSame('Size', $result['components'][0]['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesMultipleMixedComponents(): void
    {
        $subject = new RuleValueList();
        $subject->addListComponent(new Size(1));
        $subject->addListComponent('+');
        $subject->addListComponent(new Size(2));

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
        $separator = ', ';
        $subject = new RuleValueList($separator);

        $result = $subject->getArrayRepresentation();

        self::assertSame($separator, $result['separator']);
    }
}
