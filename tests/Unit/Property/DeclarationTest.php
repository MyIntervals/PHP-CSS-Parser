<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\Property\Declaration;
use Sabberworm\CSS\Value\RuleValueList;

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

    /**
     * @test
     */
    public function getPropertyNameReturnsPropertyNameProvidedToConstructor(): void
    {
        $subject = new Declaration('color');

        self::assertSame('color', $subject->getPropertyName());
    }

    /**
     * @test
     */
    public function setPropertyNameSetsPropertyName(): void
    {
        $subject = new Declaration('color');

        $subject->setPropertyName('background-color');

        self::assertSame('background-color', $subject->getPropertyName());
    }

    /**
     * @test
     */
    public function isNotImportantByDefault(): void
    {
        $subject = new Declaration('color');

        self::assertFalse($subject->getIsImportant());
    }

    /**
     * @test
     *
     * @dataProvider provideBooleans
     */
    public function setIsImportantSetsImportantFlag(bool $important): void
    {
        $subject = new Declaration('color');

        $subject->setIsImportant($important);

        self::assertSame($important, $subject->getIsImportant());
    }

    /**
     * @test
     */
    public function addValueWithoutExistingValueCreatesListWithOnlyTheNewValue(): void
    {
        $subject = new Declaration('margin');
        $value = new RuleValueList();

        $subject->addValue($value);

        $result = $subject->getValue();
        self::assertInstanceOf(RuleValueList::class, $result);
        self::assertSame(' ', $result->getListSeparator());
        self::assertSame([$value], $result->getListComponents());
    }

    /**
     * @test
     */
    public function addValueWithExistingNonListValueWrapsBothInList(): void
    {
        $subject = new Declaration('font-family');
        $subject->setValue('Arial');
        $value = new RuleValueList();

        $subject->addValue($value, ',');

        $result = $subject->getValue();
        self::assertInstanceOf(RuleValueList::class, $result);
        self::assertSame(',', $result->getListSeparator());
        self::assertSame(['Arial', $value], $result->getListComponents());
    }

    /**
     * @test
     */
    public function addValueWithExistingListOfSameSeparatorAppendsToIt(): void
    {
        $subject = new Declaration('margin');
        $list = new RuleValueList(' ');
        $subject->setValue($list);
        $value1 = new RuleValueList();
        $value2 = new RuleValueList();

        $subject->addValue([$value1, $value2]);

        self::assertSame($list, $subject->getValue());
        self::assertSame([$value1, $value2], $list->getListComponents());
    }

    /**
     * @test
     */
    public function addValueWithExistingListOfOtherSeparatorWrapsItInNewList(): void
    {
        $subject = new Declaration('font-family');
        $list = new RuleValueList(' ');
        $subject->setValue($list);
        $value = new RuleValueList();

        $subject->addValue($value, ',');

        $result = $subject->getValue();
        self::assertInstanceOf(RuleValueList::class, $result);
        self::assertNotSame($list, $result);
        self::assertSame(',', $result->getListSeparator());
        self::assertSame([$list, $value], $result->getListComponents());
    }
}
