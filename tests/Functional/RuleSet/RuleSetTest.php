<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Property\Declaration;
use Sabberworm\CSS\RuleSet\RuleSet;
use Sabberworm\CSS\Settings;

/**
 * @covers \Sabberworm\CSS\RuleSet\RuleSet
 */
final class RuleSetTest extends TestCase
{
    /**
     * @var RuleSet
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new RuleSet();
    }

    /**
     * @return array<string, array{0: list<array{name: string, value: string}>, 1: string}>
     */
    public static function providePropertyNamesAndValuesAndExpectedCss(): array
    {
        return [
            'no properties' => [[], ''],
            'one property' => [
                [['name' => 'color', 'value' => 'green']],
                'color: green;',
            ],
            'two different properties' => [
                [
                    ['name' => 'color', 'value' => 'green'],
                    ['name' => 'display', 'value' => 'block'],
                ],
                'color: green;display: block;',
            ],
            'two of the same property' => [
                [
                    ['name' => 'color', 'value' => '#40A040'],
                    ['name' => 'color', 'value' => 'rgba(0, 128, 0, 0.25)'],
                ],
                'color: #40A040;color: rgba(0, 128, 0, 0.25);',
            ],
        ];
    }

    /**
     * @test
     *
     * @param list<array{name: string, value: string}> $propertyNamesAndValuesToSet
     *
     * @dataProvider providePropertyNamesAndValuesAndExpectedCss
     */
    public function renderReturnsCssForDeclarationsSet(array $propertyNamesAndValuesToSet, string $expectedCss): void
    {
        $this->setDeclarationsFromPropertyNamesAndValues($propertyNamesAndValuesToSet);

        $result = $this->subject->render(OutputFormat::create());

        self::assertSame($expectedCss, $result);
    }

    /**
     * @test
     */
    public function renderWithCompactOutputFormatReturnsCssWithoutWhitespaceOrTrailingSemicolon(): void
    {
        $this->setDeclarationsFromPropertyNamesAndValues([
            ['name' => 'color', 'value' => 'green'],
            ['name' => 'display', 'value' => 'block'],
        ]);

        $result = $this->subject->render(OutputFormat::createCompact());

        self::assertSame('color:green;display:block', $result);
    }

    /**
     * @test
     */
    public function renderWithPrettyOutputFormatReturnsCssWithNewlinesAroundIndentedDeclarations(): void
    {
        $this->setDeclarationsFromPropertyNamesAndValues([
            ['name' => 'color', 'value' => 'green'],
            ['name' => 'display', 'value' => 'block'],
        ]);

        $result = $this->subject->render(OutputFormat::createPretty());

        self::assertSame("\n\tcolor: green;\n\tdisplay: block;\n", $result);
    }

    /**
     * @param list<array{name: string, value: string}> $propertyNamesAndValues
     */
    private function setDeclarationsFromPropertyNamesAndValues(array $propertyNamesAndValues): void
    {
        $declarationsToSet = \array_map(
            /**
             * @param array{name: string, value: string} $nameAndValue
             */
            static function (array $nameAndValue): Declaration {
                $declaration = new Declaration($nameAndValue['name']);
                $declaration->setValue($nameAndValue['value']);
                return $declaration;
            },
            $propertyNamesAndValues
        );
        $this->subject->setDeclarations($declarationsToSet);
    }

    /**
     * The opening `{` is consumed by the caller of `parseRuleSet()`, so the CSS starts right after it.
     * The tests append the closing `}` that `parseRuleSet()` consumes.
     *
     * @return array<non-empty-string, array{0: non-empty-string, 1: list<non-empty-string>}>
     */
    public static function provideValidDeclarationsAndExpectedPropertyNames(): array
    {
        return [
            'one declaration' => ['color: red;', ['color']],
            'leading semicolons' => [';; color: red;', ['color']],
            'two declarations' => ['color: red; margin: 0;', ['color', 'margin']],
        ];
    }

    /**
     * @test
     *
     * @param list<non-empty-string> $expectedPropertyNames
     *
     * @dataProvider provideValidDeclarationsAndExpectedPropertyNames
     */
    public function parseRuleSetInStrictModeParsesDeclarationsAndClosingBrace(
        string $css,
        array $expectedPropertyNames
    ): void {
        $parserState = new ParserState($css . '}', Settings::create()->beStrict());
        $subject = new RuleSet();

        RuleSet::parseRuleSet($parserState, $subject);

        self::assertSame($expectedPropertyNames, self::getPropertyNames($subject));
        self::assertTrue($parserState->isEnd());
    }

    /**
     * @test
     */
    public function parseRuleSetInStrictModeThrowsForInvalidDeclaration(): void
    {
        $parserState = new ParserState('color: red; foo; margin: 0; }', Settings::create()->beStrict());

        $this->expectException(UnexpectedTokenException::class);

        RuleSet::parseRuleSet($parserState, new RuleSet());
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: list<non-empty-string>}>
     */
    public static function provideInvalidDeclarationsAndExpectedPropertyNames(): array
    {
        return [
            'invalid declaration followed by semicolon' => ['color: red; foo; margin: 0;', ['color', 'margin']],
            'invalid declaration followed by closing brace' => ['color: red; foo', ['color']],
        ];
    }

    /**
     * @test
     *
     * @param list<non-empty-string> $expectedPropertyNames
     *
     * @dataProvider provideInvalidDeclarationsAndExpectedPropertyNames
     */
    public function parseRuleSetInLenientModeSkipsInvalidDeclarations(
        string $css,
        array $expectedPropertyNames
    ): void {
        $parserState = new ParserState($css . '}', Settings::create());
        $subject = new RuleSet();

        RuleSet::parseRuleSet($parserState, $subject);

        self::assertSame($expectedPropertyNames, self::getPropertyNames($subject));
        self::assertTrue($parserState->isEnd());
    }

    /**
     * @return list<non-empty-string>
     */
    private static function getPropertyNames(RuleSet $ruleSet): array
    {
        return \array_map(
            static function (Declaration $declaration): string {
                return $declaration->getPropertyName();
            },
            $ruleSet->getDeclarations()
        );
    }
}
