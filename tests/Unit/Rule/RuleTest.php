<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Rule;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\RuleValueList;
use Sabberworm\CSS\Value\Value;
use Sabberworm\CSS\Value\ValueList;

/**
 * @covers \Sabberworm\CSS\Rule\Rule
 */
final class RuleTest extends TestCase
{
    /**
     * @test
     */
    public function implementsCSSElement(): void
    {
        $subject = new Rule('beverage-container');

        self::assertInstanceOf(CSSElement::class, $subject);
    }

    /**
     * @return array<string, array{0: string, 1: list<class-string>}>
     */
    public static function provideRulesAndExpectedParsedValueListTypes(): array
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
     * @dataProvider provideRulesAndExpectedParsedValueListTypes
     */
    public function parsesValuesIntoExpectedTypeList(string $rule, array $expectedTypeClassnames): void
    {
        $subject = Rule::parse(new ParserState($rule, Settings::create()));

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
}
