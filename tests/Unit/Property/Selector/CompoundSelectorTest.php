<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property\Selector;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Property\Selector\Component;
use Sabberworm\CSS\Property\Selector\CompoundSelector;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\Settings;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @covers \Sabberworm\CSS\Property\Selector\CompoundSelector
 */
final class CompoundSelectorTest extends TestCase
{
    /**
     * @test
     */
    public function implementsRenderable(): void
    {
        self::assertInstanceOf(Renderable::class, new CompoundSelector('a:hover'));
    }

    /**
     * @test
     */
    public function implementsSelectorComponent(): void
    {
        self::assertInstanceOf(Component::class, new CompoundSelector('a:hover'));
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: int<0, max>}>
     */
    public static function provideCompoundSelectorAndSpecificity(): array
    {
        return [
            'type' => ['a', 1],
            'class' => ['.highlighted', 10],
            'type with class' => ['li.green', 11],
            'pseudo-class' => [':hover', 10],
            'type with pseudo-class' => ['a:hover', 11],
            'class with pseudo-class' => ['.help:hover', 20],
            'ID' => ['#file', 100],
            'type with ID' => ['h2#my-mug', 101],
            'pseudo-element' => ['::before', 1],
            'type with pseudo-element' => ['li::before', 2],
            '`not`' => [':not(#your-mug)', 100],
            // TODO, broken: The specificity should be the highest of the `:not` arguments, not the sum.
            '`not` with multiple arguments' => [':not(#your-mug, .their-mug)', 110],
            'attribute with `"`' => ['[alt="{}()[]\\"\',"]', 10],
            'attribute with `\'`' => ['[alt=\'{}()[]"\\\',\']', 10],
            // TODO, broken: specificity should be 11, but the calculator doesn't realize the `#` is in a string.
            'attribute with `^=`' => ['a[href^="#"]', 111],
            'attribute with `*=`' => ['a[href*="example"]', 11],
            // TODO, broken: specificity should be 11, but the calculator doesn't realize the `.` is in a string.
            'attribute with `$=`' => ['a[href$=".org"]', 21],
            'attribute with `~=`' => ['span[title~="bonjour"]', 11],
            'attribute with `|=`' => ['[lang|="en"]', 10],
            // TODO, broken: specificity should be 11, but the calculator doesn't realize the `i` is in an attribute.
            'attribute with case insensitive modifier' => ['a[href*="insensitive" i]', 12],
            // TODO, broken: specificity should be 21, but the calculator doesn't realize the `.` is in a string.
            'multiple attributes' => ['a[href^="https://"][href$=".org"]', 31],
            // TODO, broken: specificity should be 11, but the calculator is treating the `n` as a type selector.
            'nth-last-child' => ['li:nth-last-child(2n+3)', 12],
            // TODO, maybe broken: specificity should probably be 2 (1 for `p` and 1 for `span`).
            '`not` with descendent combinator' => [':not(p span)', 1],
            // TODO, maybe broken: specificity should probably be 2 (1 for `p` and 1 for `span`).
            '`not` with child combinator' => [':not(p > span)', 1],
            // TODO, maybe broken: specificity should probably be 2 (1 for `h1` and 1 for `p`).
            '`not` with next-sibling combinator' => [':not(h1 + p)', 1],
            // TODO, maybe broken: specificity should probably be 2 (1 for `h1` and 1 for `p`).
            '`not` with subsequent-sibling combinator' => [':not(h1 ~ p)', 1],
        ];
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideCompoundSelectorWithEscapedQuotes(): array
    {
        return [
            'escaped double quote in double-quoted attribute' => ['a[href="test\\"value"]'],
            'escaped single quote in single-quoted attribute' => ["a[href='test\\'value']"],
            'multiple escaped double quotes in double-quoted attribute' => ['a[title="say \\"hello\\" world"]'],
            'multiple escaped single quotes in single-quoted attribute' => ["a[title='say \\'hello\\' world']"],
            'escaped quote at start of attribute value' => ['a[data-test="\\"start"]'],
            'escaped quote at end of attribute value' => ['a[data-test="end\\""]'],
            'escaped backslash followed by quote' => ['a[data-test="test\\\\"]'],
            'escaped backslash before escaped quote' => ['a[data-test="test\\\\\\"value"]'],
            'triple backslash before quote' => ['a[data-test="test\\\\\\""]'],
            'escaped single quotes in selector itself, with other escaped characters'
                => [".before\\:content-\\[\\'\\'\\]:before"],
            'escaped double quotes in selector itself, with other escaped characters'
                => ['.before\\:content-\\[\\"\\"\\]:before'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $compoundSelector
     *
     * @dataProvider provideCompoundSelectorAndSpecificity
     * @dataProvider provideCompoundSelectorWithEscapedQuotes
     */
    public function parsesValidCompoundSelector(string $compoundSelector): void
    {
        $result = CompoundSelector::parse(new ParserState($compoundSelector, Settings::create()));

        self::assertInstanceOf(CompoundSelector::class, $result);
        self::assertSame($compoundSelector, $result->getValue());
    }

    /**
     * @test
     */
    public function parsingAttributeWithEscapedQuoteDoesNotPrematurelyCloseString(): void
    {
        $compoundSelector = 'input[placeholder="Enter \\"quoted\\" text here"]';

        $result = CompoundSelector::parse(new ParserState($compoundSelector, Settings::create()));

        self::assertInstanceOf(CompoundSelector::class, $result);
        self::assertSame($compoundSelector, $result->getValue());
    }

    /**
     * @test
     */
    public function parseDistinguishesEscapedFromUnescapedQuotes(): void
    {
        // One backslash = escaped quote (should not close string)
        $compoundSelector = 'a[data-value="test\\"more"]';

        $result = CompoundSelector::parse(new ParserState($compoundSelector, Settings::create()));

        self::assertSame($compoundSelector, $result->getValue());
    }

    /**
     * @test
     */
    public function parseHandlesEvenNumberOfBackslashesBeforeQuote(): void
    {
        // Two backslashes = escaped backslash + unescaped quote (should close string)
        $compoundSelector = 'a[data-value="test\\\\"]';

        $result = CompoundSelector::parse(new ParserState($compoundSelector, Settings::create()));

        self::assertSame($compoundSelector, $result->getValue());
    }

    /**
     * @return array<non-empty-string, array{0: string}>
     */
    public static function provideInvalidCompoundSelector(): array
    {
        return [
            'empty string' => [''],
            'space' => [' '],
            'tab' => ["\t"],
            'line feed' => ["\n"],
            'carriage return' => ["\r"],
            'percent sign' => ['%'],
            // This is currently broken.
            // 'hash only' => ['#'],
            // This is currently broken.
            // 'dot only' => ['.'],
            'slash' => ['/'],
            'less-than sign' => ['<'],
            'with space before' => [' a:hover'],
        ];
    }

    /**
     * The validation checks on `setValue()` are not able to pick up these, because it does not perform any parsing.
     *
     * @return array<non-empty-string, array{0: string}>
     */
    public static function provideInvalidCompoundSelectorForParse(): array
    {
        return [
            'a `:not` missing the closing brace' => [':not(a'],
            'a `:not` missing the opening brace' => [':nota)'],
            'attribute value missing closing single quote' => ["a[href='#top]"],
            'attribute value missing closing double quote' => ['a[href="#top]'],
            'attribute value with mismatched quotes, single quote opening' => ['a[href=\'#top"]'],
            'attribute value with mismatched quotes, double quote opening' => ['a[href="#top\']'],
            'attribute value with extra `[`' => ['a[[href="#top"]'],
            'attribute value with extra `]`' => ['a[href="#top"]]'],
        ];
    }

    /**
     * This is valid in a parsing context, but not when passed to `setValue()` or the constructor.
     *
     * @return array<non-empty-string, array{0: string}>
     */
    public static function provideInvalidCompoundSelectorForSetValue(): array
    {
        return [
            'with space after' => ['a:hover '],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $compoundSelector
     *
     * @dataProvider provideInvalidCompoundSelector
     * @dataProvider provideInvalidCompoundSelectorForParse
     */
    public function parseThrowsExceptionWithInvalidCompoundSelector(string $compoundSelector): void
    {
        $this->expectException(UnexpectedTokenException::class);

        CompoundSelector::parse(new ParserState($compoundSelector, Settings::create()));
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideStopCharacters(): array
    {
        return [
            ',' => [','],
            '{' => ['{'],
            '}' => ['}'],
            'space' => [' '],
            'tab' => ["\t"],
            'line feed' => ["\n"],
            'carriage return' => ["\r"],
            'child combinator' => ['>'],
            'next-sibling combinator' => ['+'],
            'subsequent-sibling combinator' => ['~'],
        ];
    }

    /**
     * @return DataProvider<non-empty-string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public function provideStopCharactersAndValidCompoundSelector(): DataProvider
    {
        return DataProvider::cross(self::provideStopCharacters(), self::provideCompoundSelectorAndSpecificity());
    }

    /**
     * @test
     *
     * @param non-empty-string $stopCharacter
     * @param non-empty-string $compoundSelector
     *
     * @dataProvider provideStopCharactersAndValidCompoundSelector
     */
    public function parseDoesNotConsumeStopCharacter(string $stopCharacter, string $compoundSelector): void
    {
        $subject = new ParserState($compoundSelector . $stopCharacter, Settings::create());

        CompoundSelector::parse($subject);

        self::assertSame($stopCharacter, $subject->peek());
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function provideCompoundSelectorWithAndWithoutComment(): array
    {
        return [
            'comment before' => ['/*comment*/body.page', 'body.page'],
            'comment after' => ['body.page/*comment*/', 'body.page'],
            'comment within' => ['p./*comment*/teapot', 'p.teapot'],
            'comment within function' => ['p:not(#your-mug,/*comment*/.their-mug)', 'p:not(#your-mug,.their-mug)'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $compoundSelectorWith
     * @param non-empty-string $compoundSelectorWithout
     *
     * @dataProvider provideCompoundSelectorWithAndWithoutComment
     */
    public function parsesCompoundSelectorWithComment(
        string $compoundSelectorWith,
        string $compoundSelectorWithout
    ): void {
        $result = CompoundSelector::parse(new ParserState($compoundSelectorWith, Settings::create()));

        self::assertInstanceOf(CompoundSelector::class, $result);
        self::assertSame($compoundSelectorWithout, $result->getValue());
    }

    /**
     * @test
     *
     * @param non-empty-string $compoundSelector
     *
     * @dataProvider provideCompoundSelectorWithAndWithoutComment
     */
    public function parseExtractsCommentFromCompoundSelector(string $compoundSelector): void
    {
        $result = [];
        CompoundSelector::parse(new ParserState($compoundSelector, Settings::create()), $result);

        self::assertInstanceOf(Comment::class, $result[0]);
        self::assertSame('comment', $result[0]->getComment());
    }

    /**
     * @test
     */
    public function parsesCompoundSelectorWithTwoComments(): void
    {
        $result = CompoundSelector::parse(new ParserState('/*comment1*/a:hover/*comment2*/', Settings::create()));

        self::assertInstanceOf(CompoundSelector::class, $result);
        self::assertSame('a:hover', $result->getValue());
    }

    /**
     * @test
     */
    public function parseExtractsTwoCommentsFromCompoundSelector(): void
    {
        $result = [];
        CompoundSelector::parse(new ParserState('/*comment1*/a:hover/*comment2*/', Settings::create()), $result);

        self::assertInstanceOf(Comment::class, $result[0]);
        self::assertSame('comment1', $result[0]->getComment());
        self::assertInstanceOf(Comment::class, $result[1]);
        self::assertSame('comment2', $result[1]->getComment());
    }

    /**
     * @test
     *
     * @param non-empty-string $value
     *
     * @dataProvider provideCompoundSelectorAndSpecificity
     */
    public function constructsWithValueProvided(string $value): void
    {
        $subject = new CompoundSelector($value);

        self::assertSame($value, $subject->getArrayRepresentation()['value']);
    }

    /**
     * @test
     *
     * @param non-empty-string $value
     *
     * @dataProvider provideInvalidCompoundSelector
     * @dataProvider provideInvalidCompoundSelectorForSetValue
     */
    public function constructorThrowsExceptionWithInvalidValue(string $value): void
    {
        $this->expectException(\UnexpectedValueException::class);

        new CompoundSelector($value);
    }

    /**
     * @test
     *
     * @param non-empty-string $value
     *
     * @dataProvider provideCompoundSelectorAndSpecificity
     */
    public function setValueSetsValueProvided(string $value): void
    {
        $subject = new CompoundSelector('p.intro');

        $subject->setValue($value);

        self::assertSame($value, $subject->getArrayRepresentation()['value']);
    }

    /**
     * @test
     *
     * @param non-empty-string $value
     *
     * @dataProvider provideInvalidCompoundSelector
     * @dataProvider provideInvalidCompoundSelectorForSetValue
     */
    public function setValueThrowsExceptionWithInvalidValue(string $value): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $subject = new CompoundSelector('p.intro');

        $subject->setValue($value);
    }

    /**
     * @test
     *
     * @param non-empty-string $value
     *
     * @dataProvider provideCompoundSelectorAndSpecificity
     */
    public function getValueReturnsValueProvidedToConstructor(string $value): void
    {
        $subject = new CompoundSelector($value);

        $result = $subject->getValue();

        self::assertSame($value, $result);
    }

    /**
     * @test
     *
     * @param non-empty-string $value
     *
     * @dataProvider provideCompoundSelectorAndSpecificity
     */
    public function getValueReturnsValueProvidedToSetValue(string $value): void
    {
        $subject = new CompoundSelector('p.intro');
        $subject->setValue($value);

        $result = $subject->getValue();

        self::assertSame($value, $result);
    }

    /**
     * @test
     *
     * @param non-empty-string $compoundSelector
     * @param int<0, max> $expectedSpecificity
     *
     * @dataProvider provideCompoundSelectorAndSpecificity
     */
    public function getSpecificityByDefaultReturnsSpecificityOfCompoundSelectorProvidedToConstructor(
        string $compoundSelector,
        int $expectedSpecificity
    ): void {
        $subject = new CompoundSelector($compoundSelector);

        self::assertSame($expectedSpecificity, $subject->getSpecificity());
    }

    /**
     * @test
     *
     * @param non-empty-string $compoundSelector
     * @param int<0, max> $expectedSpecificity
     *
     * @dataProvider provideCompoundSelectorAndSpecificity
     */
    public function getSpecificityReturnsSpecificityOfCompoundSelectorLastProvidedViaSetValue(
        string $compoundSelector,
        int $expectedSpecificity
    ): void {
        $subject = new CompoundSelector('p.intro');

        $subject->setValue($compoundSelector);

        self::assertSame($expectedSpecificity, $subject->getSpecificity());
    }

    /**
     * @test
     *
     * @param non-empty-string $value
     *
     * @dataProvider provideCompoundSelectorAndSpecificity
     */
    public function renderReturnsValueProvided(string $value): void
    {
        $subject = new CompoundSelector($value);

        self::assertSame($value, $subject->render(OutputFormat::create()));
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new CompoundSelector('a:hover');

        $result = $subject->getArrayRepresentation();

        self::assertSame('CompoundSelector', $result['class']);
    }

    /**
     * @test
     *
     * @param non-empty-string $value
     *
     * @dataProvider provideCompoundSelectorAndSpecificity
     */
    public function getArrayRepresentationIncludesValue(string $value): void
    {
        $subject = new CompoundSelector($value);

        $result = $subject->getArrayRepresentation();

        self::assertSame($value, $result['value']);
    }
}
