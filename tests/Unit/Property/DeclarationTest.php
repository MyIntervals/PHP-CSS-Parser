<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Property\Declaration;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\RuleValueList;
use Sabberworm\CSS\Value\Value;
use Sabberworm\CSS\Value\ValueList;

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
     * @return array<string, array{0: string, 1: list<class-string>}>
     */
    public static function provideDeclarationsAndExpectedParsedValueListTypes(): array
    {
        return [
            'src (e.g. in @font-face)' => [
                "
                    src: url('../fonts/open-sans-italic-300.woff2') format('woff2'),
                         url('../fonts/open-sans-italic-300.ttf') format('truetype');
                ",
                [RuleValueList::class, RuleValueList::class],
            ],
        ];
    }

    /**
     * @test
     *
     * @param list<class-string> $expectedTypeClassnames
     *
     * @dataProvider provideDeclarationsAndExpectedParsedValueListTypes
     */
    public function parsesValuesIntoExpectedTypeList(string $declaration, array $expectedTypeClassnames): void
    {
        $subject = Declaration::parse(new ParserState($declaration, Settings::create()));

        $value = $subject->getValue();
        self::assertInstanceOf(ValueList::class, $value);

        $actualClassnames = \array_map(
            /**
             * @param Value|string $component
             */
            static function ($component): string {
                return \is_string($component) ? 'string' : \get_class($component);
            },
            $value->getListComponents()
        );

        self::assertSame($expectedTypeClassnames, $actualClassnames);
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
