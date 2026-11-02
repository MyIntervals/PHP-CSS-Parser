<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\Property\Declaration;

/**
 * @covers \Sabberworm\CSS\Property\Declaration
 */
final class DeclarationTest extends TestCase
{
    /**
     * @test
     */
    public function implementsCSSElement(): void
    {
        $subject = new Declaration('beverage-container');

        self::assertInstanceOf(CSSElement::class, $subject);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new Declaration('line-height');

        $result = $subject->getArrayRepresentation();

        self::assertSame('Declaration', $result['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesPropertyName(): void
    {
        $propertyName = 'font-weight';
        $subject = new Declaration($propertyName);

        $result = $subject->getArrayRepresentation();

        self::assertSame($propertyName, $result['propertyName']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesPropertyValue(): void
    {
        $subject = new Declaration('font-weight');
        $propertyValue = 'bold';
        $subject->setValue($propertyValue);

        $result = $subject->getArrayRepresentation();

        self::assertSame($propertyValue, $result['propertyValue']);
    }

    /**
     * @return array<non-empty-string, array{0: bool}>
     */
    public static function provideBooleans(): array
    {
        return [
            'true' => [true],
            'false' => [false],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideBooleans
     */
    public function getArrayRepresentationIncludesImportantFlag(bool $important): void
    {
        $subject = new Declaration('font-weight');
        $subject->setIsImportant($important);

        $result = $subject->getArrayRepresentation();

        self::assertSame($important, $result['important']);
    }
}
