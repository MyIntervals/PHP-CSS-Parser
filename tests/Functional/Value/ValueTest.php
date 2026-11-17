<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\CalcFunction;
use Sabberworm\CSS\Value\Color;
use Sabberworm\CSS\Value\CSSFunction;
use Sabberworm\CSS\Value\CSSString;
use Sabberworm\CSS\Value\LineName;
use Sabberworm\CSS\Value\RuleValueList;
use Sabberworm\CSS\Value\Size;
use Sabberworm\CSS\Value\URL;
use Sabberworm\CSS\Value\Value;

/**
 * @covers \Sabberworm\CSS\Value\Value
 */
final class ValueTest extends TestCase
{
    /**
     * the default set of delimiters for parsing most values
     *
     * @see \Sabberworm\CSS\Property\Declaration::getDelimitersForPropertyValue
     */
    private const DEFAULT_DELIMITERS = [',', ' ', '/'];

    /**
     * @return array<string, array{0: string}>
     */
    public static function provideArithmeticOperator(): array
    {
        return [
            '+' => ['+'],
            '-' => ['-'],
            '*' => ['*'],
            '/' => ['/'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideArithmeticOperator
     */
    public function parsesArithmeticInFunctions(string $operator): void
    {
        $subject = Value::parseValue(
            new ParserState('max(300px, 50vh ' . $operator . ' 10px);', Settings::create()),
            self::DEFAULT_DELIMITERS
        );

        $result = $subject->getArrayRepresentation();
        self::assertSame(
            [
                'class' => 'CSSFunction',
                'name' => 'max',
                'components' => [
                    [
                        'class' => 'Size',
                        'number' => 300.0,
                        'unit' => 'px',
                    ],
                    [
                        'class' => 'RuleValueList',
                        'components' => [
                            [
                                'class' => 'Size',
                                'number' => 50.0,
                                'unit' => 'vh',
                            ],
                            [
                                'class' => 'string',
                                'value' => $operator,
                            ],
                            [
                                'class' => 'Size',
                                'number' => 10.0,
                                'unit' => 'px',
                            ],
                        ],
                        'separator' => ' ',
                    ],
                ],
                'separator' => ',',
            ],
            $result
        );
    }

    /**
     * @return array<string, array{'to be parsed': non-empty-string, 'expected': non-empty-string}>
     * The first datum is a template for the parser (using `sprintf` insertion marker `%s` for some expression).
     * The second is for the expected result, which may have whitespace and trailing semicolon removed.
     */
    public static function provideCssFunctionTemplates(): array
    {
        return [
            'calc' => [
                'to be parsed' => 'calc(%s);',
                'expected' => 'calc(%s)',
            ],
            'max' => [
                'to be parsed' => 'max(300px, %s);',
                'expected' => 'max(300px,%s)',
            ],
            'clamp' => [
                'to be parsed' => 'clamp(2.19rem, %s, 2.5rem);',
                'expected' => 'clamp(2.19rem,%s,2.5rem)',
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideCssFunctionTemplates
     */
    public function parsesArithmeticWithMultipleOperatorsInFunctions(
        string $parserTemplate,
        string $expectedResultTemplate
    ): void {
        static $expression = '300px + 10% + 10vw';

        $subject = Value::parseValue(
            new ParserState(\sprintf($parserTemplate, $expression), Settings::create()),
            self::DEFAULT_DELIMITERS
        );

        self::assertInstanceOf(CSSFunction::class, $subject);
        self::assertSame(
            \sprintf($expectedResultTemplate, $expression),
            $subject->render(OutputFormat::createCompact())
        );
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function provideMalformedLengthOperands(): array
    {
        return [
            'LHS missing number' => ['vh', '10px'],
            'RHS missing number' => ['50vh', 'px'],
            'LHS missing unit' => ['50', '10px'],
            'RHS missing unit' => ['50vh', '10'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideMalformedLengthOperands
     */
    public function parsesArithmeticWithMalformedOperandsInFunctions(string $leftOperand, string $rightOperand): void
    {
        $subject = Value::parseValue(
            new ParserState('max(300px, ' . $leftOperand . ' + ' . $rightOperand . ');', Settings::create()),
            self::DEFAULT_DELIMITERS
        );

        self::assertInstanceOf(CSSFunction::class, $subject);
        self::assertSame(
            'max(300px,' . $leftOperand . ' + ' . $rightOperand . ')',
            $subject->render(OutputFormat::createCompact())
        );
    }

    /**
     * @test
     */
    public function parsesFirstArgumentInMaxFunction(): void
    {
        $parsedValue = Value::parseValue(
            new ParserState('max(300px, 400px);', Settings::create()),
            self::DEFAULT_DELIMITERS
        );

        self::assertInstanceOf(CSSFunction::class, $parsedValue);
        $size = $parsedValue->getArguments()[0];
        self::assertInstanceOf(Size::class, $size);
        self::assertSame(300.0, $size->getSize());
        self::assertSame('px', $size->getUnit());
        self::assertFalse($size->isColorComponent());
    }

    /**
     * @test
     */
    public function parsesTrailingEmptyArgumentWhenAllowed(): void
    {
        $parsedValue = Value::parseValue(
            new ParserState('--w,)', Settings::create()),
            self::DEFAULT_DELIMITERS,
            true
        );

        self::assertInstanceOf(RuleValueList::class, $parsedValue);
        self::assertSame(',', $parsedValue->getListSeparator());
        self::assertSame(['--w', ''], $parsedValue->getListComponents());
    }

    /**
     * @test
     */
    public function rejectsTrailingEmptyArgumentByDefault(): void
    {
        $this->expectException(UnexpectedTokenException::class);

        Value::parseValue(
            new ParserState('--w,)', Settings::create()),
            self::DEFAULT_DELIMITERS
        );
    }

    /**
     * @return \Generator<non-empty-string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function provideIdentifiersStartingWithColorFunctionNameAndExpectedRendering(): \Generator
    {
        yield 'rgb prefix' => ['animation-name:rgbshift', 'animation-name: rgbshift;'];
        yield 'hsl prefix' => ['font-family:hslfont', 'font-family: hslfont;'];
        yield 'uppercase prefix' => ['animation-name:RGBshift', 'animation-name: RGBshift;'];
        yield 'bare rgb' => ['animation-name:rgb', 'animation-name: rgb;'];
        yield 'color function' => ['color:rgb(1,2,3)', 'color: #010203;'];
        yield 'color function after prefixed identifier' => [
            'animation-name:rgbshift;color:hsl(0,0%,0%)',
            'animation-name: rgbshift;color: hsl(0,0%,0%);',
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideIdentifiersStartingWithColorFunctionNameAndExpectedRendering
     */
    public function parsesIdentifiersStartingWithColorFunctionNameAsIdentifiers(
        string $declarations,
        string $expected
    ): void {
        $result = (new Parser('a{' . $declarations . '}'))->parse()->render();

        self::assertSame('a {' . $expected . '}', $result);
    }

    /**
     * @test
     */
    public function parsesColorFunctionWithoutWhitespaceBeforeOpeningParenthesisAsColor(): void
    {
        $result = Value::parsePrimitiveValue(new ParserState('rgb(0, 255, 0)', Settings::create()));

        self::assertInstanceOf(Color::class, $result);
    }

    /**
     * @test
     */
    public function doesNotParseColorFunctionNameFollowedByWhitespaceAsColor(): void
    {
        $result = Value::parsePrimitiveValue(new ParserState('rgb (0, 255, 0)', Settings::create()));

        self::assertSame('rgb', $result);
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideListDelimiter(): array
    {
        return [
            'comma' => [','],
            'space' => [' '],
            'slash' => ['/'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideListDelimiter
     */
    public function parsesValuesSeparatedByDelimiterAsList(string $delimiter): void
    {
        $parserState = new ParserState('a' . $delimiter . 'b', Settings::create());

        $result = Value::parseValue($parserState, self::DEFAULT_DELIMITERS);

        self::assertInstanceOf(RuleValueList::class, $result);
        self::assertSame($delimiter, $result->getListSeparator());
        self::assertSame(['a', 'b'], $result->getListComponents());
    }

    /**
     * @test
     */
    public function parsesCommaSeparatedListInsideSpaceSeparatedList(): void
    {
        $parserState = new ParserState('a b, c', Settings::create());

        $result = Value::parseValue($parserState, self::DEFAULT_DELIMITERS);

        self::assertInstanceOf(RuleValueList::class, $result);
        self::assertSame(' ', $result->getListSeparator());
        $components = $result->getListComponents();
        self::assertCount(2, $components);
        self::assertSame('a', $components[0]);
        self::assertInstanceOf(RuleValueList::class, $components[1]);
        self::assertSame(',', $components[1]->getListSeparator());
        self::assertSame(['b', 'c'], $components[1]->getListComponents());
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideValueTerminator(): array
    {
        return [
            'semicolon' => [';'],
            'closing brace' => ['}'],
            'closing parenthesis' => [')'],
            'exclamation mark' => ['!'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideValueTerminator
     */
    public function parseValueStopsAtTerminator(string $terminator): void
    {
        $value = 'red';
        $parserState = new ParserState($value . $terminator . 'x', Settings::create());

        $result = Value::parseValue($parserState, self::DEFAULT_DELIMITERS);

        self::assertSame($value, $result);
        self::assertTrue($parserState->comes($terminator));
    }

    /**
     * @test
     */
    public function parsesUnicodeRangeWithMoreThanSixDigitsOnlyUpToSixDigits(): void
    {
        $parserState = new ParserState('U+1234567', Settings::create());

        $result = Value::parsePrimitiveValue($parserState);

        self::assertSame('U+123456', $result);
    }

    /**
     * @test
     */
    public function parseValueDoesNotAcceptMicrosoftFilterInStrictMode(): void
    {
        $parserState = new ParserState(
            'progid:DXImageTransform.Microsoft.Alpha(Opacity=50)',
            Settings::create()->beStrict()
        );

        $this->expectException(UnexpectedTokenException::class);

        Value::parseValue($parserState, self::DEFAULT_DELIMITERS);
    }

    /**
     * @test
     */
    public function parseIdentifierOrFunctionParsesIdentifierAsString(): void
    {
        $identifier = 'Block';
        $parserState = new ParserState($identifier, Settings::create());

        $result = Value::parseIdentifierOrFunction($parserState);

        self::assertSame($identifier, $result);
    }

    /**
     * @test
     */
    public function parseIdentifierOrFunctionParsesCalcAsCalcFunction(): void
    {
        $parserState = new ParserState('calc(1px + 2px)', Settings::create());

        $result = Value::parseIdentifierOrFunction($parserState);

        self::assertInstanceOf(CalcFunction::class, $result);
    }

    /**
     * @test
     */
    public function parseIdentifierOrFunctionParsesUppercaseUrlAsUrl(): void
    {
        $uri = 'a.png';
        $parserState = new ParserState('URL(' . $uri . ')', Settings::create());

        $result = Value::parseIdentifierOrFunction($parserState);

        self::assertInstanceOf(URL::class, $result);
        self::assertSame($uri, $result->getURL()->getString());
    }

    /**
     * @test
     */
    public function parseIdentifierOrFunctionKeepsCaseOfFunctionNameByDefault(): void
    {
        $name = 'Foo';
        $parserState = new ParserState($name . '(1)', Settings::create());

        $result = Value::parseIdentifierOrFunction($parserState);

        self::assertInstanceOf(CSSFunction::class, $result);
        self::assertSame($name, $result->getName());
    }

    /**
     * @test
     */
    public function parseIdentifierOrFunctionLowercasesFunctionNameIfIgnoringCase(): void
    {
        $parserState = new ParserState('Foo(1)', Settings::create());

        $result = Value::parseIdentifierOrFunction($parserState, true);

        self::assertInstanceOf(CSSFunction::class, $result);
        self::assertSame('foo', $result->getName());
    }

    /**
     * @test
     */
    public function parseValueThrowsIfThereIsNoValue(): void
    {
        $parserState = new ParserState('a;', Settings::create());
        $parserState->consume(1);

        $this->expectException(UnexpectedTokenException::class);

        Value::parseValue($parserState, self::DEFAULT_DELIMITERS);
    }

    /**
     * @test
     */
    public function parsesUrlFunctionAsUrl(): void
    {
        $uri = 'https://example.com/image.png';
        $parserState = new ParserState('url(' . $uri . ')', Settings::create());

        $result = Value::parseValue($parserState, self::DEFAULT_DELIMITERS);

        self::assertInstanceOf(URL::class, $result);
        self::assertSame($uri, $result->getURL()->getString());
    }

    /**
     * @test
     */
    public function parsesQuotedValueAsString(): void
    {
        $string = 'Open Sans';
        $parserState = new ParserState('"' . $string . '"', Settings::create());

        $result = Value::parseValue($parserState, self::DEFAULT_DELIMITERS);

        self::assertInstanceOf(CSSString::class, $result);
        self::assertSame($string, $result->getString());
    }

    /**
     * @test
     */
    public function parsesBracketedValueAsLineName(): void
    {
        $name = 'full-start';
        $parserState = new ParserState('[' . $name . ']', Settings::create());

        $result = Value::parseValue($parserState, self::DEFAULT_DELIMITERS);

        self::assertInstanceOf(LineName::class, $result);
        self::assertSame([$name], $result->getListComponents());
    }

    /**
     * @test
     */
    public function parsesMicrosoftFilterAsFunctionInLenientMode(): void
    {
        $name = 'progid:DXImageTransform.Microsoft.Alpha';
        $parserState = new ParserState($name . '(Opacity=50)', Settings::create());

        $result = Value::parseValue($parserState, self::DEFAULT_DELIMITERS);

        self::assertInstanceOf(CSSFunction::class, $result);
        self::assertSame($name, $result->getName());
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideColorFunctionNameFollowedByIncompleteEscape(): array
    {
        return [
            'rgb' => ['rgb\\'],
            'hsl' => ['hsl\\'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideColorFunctionNameFollowedByIncompleteEscape
     */
    public function parsePrimitiveValueThrowsForColorFunctionNameFollowedByIncompleteEscape(string $css): void
    {
        $parserState = new ParserState($css, Settings::create());

        $this->expectException(UnexpectedEOFException::class);

        Value::parsePrimitiveValue($parserState);
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideUnicodeRange(): array
    {
        return [
            'single code point' => ['U+26'],
            'range' => ['U+0025-00FF'],
            'range starting at zero' => ['U+0-7F'],
            'wildcards' => ['U+4??'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideUnicodeRange
     */
    public function parsesUnicodeRangeAsString(string $range): void
    {
        $parserState = new ParserState($range, Settings::create());

        $result = Value::parseValue($parserState, self::DEFAULT_DELIMITERS);

        self::assertSame($range, $result);
    }
}
