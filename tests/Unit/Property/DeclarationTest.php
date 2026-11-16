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
        $propertyName = 'color';
        $subject = new Declaration($propertyName);

        self::assertSame($propertyName, $subject->getPropertyName());
    }

    /**
     * @test
     */
    public function setPropertyNameSetsPropertyName(): void
    {
        $subject = new Declaration('color');
        $propertyName = 'background-color';

        $subject->setPropertyName($propertyName);

        self::assertSame($propertyName, $subject->getPropertyName());
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
     */
    public function setIsImportantCanSetImportantFlagToTrue(): void
    {
        $subject = new Declaration('color');

        $subject->setIsImportant(true);

        self::assertTrue($subject->getIsImportant());
    }

    /**
     * @test
     */
    public function setIsImportantCanSetImportantFlagToFalse(): void
    {
        $subject = new Declaration('color');
        $subject->setIsImportant(true);

        $subject->setIsImportant(false);

        self::assertFalse($subject->getIsImportant());
    }

    /**
     * @test
     */
    public function addValueWithoutExistingValueCreatesListWithOnlyTheNewValue(): void
    {
        $subject = new Declaration('margin');
        $value = new RuleValueList(' ');
        $value->addListComponent('1px');

        $subject->addValue($value);

        $result = $subject->getValue();
        self::assertInstanceOf(RuleValueList::class, $result);
        self::assertSame(' ', $result->getListSeparator());
        $components = $result->getListComponents();
        self::assertCount(1, $components);
        self::assertInstanceOf(RuleValueList::class, $components[0]);
        self::assertSame(['1px'], $components[0]->getListComponents());
    }

    /**
     * @test
     */
    public function addValueWithExistingNonListValueWrapsBothInList(): void
    {
        $subject = new Declaration('font-family');
        $subject->setValue('Arial');
        $value = new RuleValueList(',');
        $value->addListComponent('Verdana');

        $subject->addValue($value, ',');

        $result = $subject->getValue();
        self::assertInstanceOf(RuleValueList::class, $result);
        self::assertSame(',', $result->getListSeparator());
        $components = $result->getListComponents();
        self::assertCount(2, $components);
        self::assertSame('Arial', $components[0]);
        self::assertInstanceOf(RuleValueList::class, $components[1]);
        self::assertSame(['Verdana'], $components[1]->getListComponents());
    }

    /**
     * @test
     */
    public function addValueWithExistingListOfSameSeparatorAppendsToIt(): void
    {
        $subject = new Declaration('margin');
        $list = new RuleValueList(' ');
        $subject->setValue($list);
        $value1 = new RuleValueList(' ');
        $value1->addListComponent('1px');
        $value2 = new RuleValueList(' ');
        $value2->addListComponent('2px');

        $subject->addValue([$value1, $value2]);

        $result = $subject->getValue();
        self::assertInstanceOf(RuleValueList::class, $result);
        $components = $result->getListComponents();
        self::assertCount(2, $components);
        self::assertInstanceOf(RuleValueList::class, $components[0]);
        self::assertSame(['1px'], $components[0]->getListComponents());
        self::assertInstanceOf(RuleValueList::class, $components[1]);
        self::assertSame(['2px'], $components[1]->getListComponents());
    }

    /**
     * @test
     */
    public function addValueWithExistingListOfOtherSeparatorWrapsItInNewList(): void
    {
        $subject = new Declaration('font-family');
        $list = new RuleValueList(' ');
        $list->addListComponent('Arial');
        $subject->setValue($list);
        $value = new RuleValueList(',');
        $value->addListComponent('Verdana');

        $subject->addValue($value, ',');

        $result = $subject->getValue();
        self::assertInstanceOf(RuleValueList::class, $result);
        self::assertSame(',', $result->getListSeparator());
        $components = $result->getListComponents();
        self::assertCount(2, $components);
        self::assertInstanceOf(RuleValueList::class, $components[0]);
        self::assertSame(' ', $components[0]->getListSeparator());
        self::assertSame(['Arial'], $components[0]->getListComponents());
        self::assertInstanceOf(RuleValueList::class, $components[1]);
        self::assertSame(['Verdana'], $components[1]->getListComponents());
    }
}
