<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\Selector;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Property\Selector\Combinator;
use Sabberworm\CSS\Property\Selector\CompoundSelector;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Tests\Support\Traits\SelectorDataProviders;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @covers \Sabberworm\CSS\Property\Selector
 */
final class SelectorTest extends TestCase
{
    use SelectorDataProviders;

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideSelectorWithEscapedStructuralCharacters(): array
    {
        return [
            'nested escaped square brackets' => ['.\\[\\&_\\[data\\]\\]'],
            'tailwind arbitrary variant class'
            => ['.\\[\\&_\\[data-piko-package\\=review-card\\]\\]\\:px-\\[25px\\]'],
            'escaped square brackets inside an attribute value' => ['[title=\\[x\\]]'],
            'escaped closing square bracket inside an attribute value' => ['[title=\\]]'],
            'even number of backslashes before an attribute' => ['a\\\\[href]'],
            'escaped parentheses in a class' => ['.w-\\[calc\\(100\\%-1rem\\)\\]'],
            'escaped spaces inside a class' => ['.hover\\:bg-\\[rgb\\(0\\ 0\\ 0\\)\\]'],
            'escaped tab inside a class' => [".foo\\\tbar"],
            'escaped child combinator in a class' => ['.foo\\>bar'],
            'escaped opening parenthesis' => ['.foo\\('],
            'escaped closing parenthesis' => ['.foo\\)'],
            'escaped opening brace' => ['.foo\\{bar'],
            'escaped comma' => ['.foo\\,bar'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $selector
     *
     * @dataProvider provideSelectorAndSpecificity
     * @dataProvider provideSelectorWithEscapedQuotes
     * @dataProvider provideSelectorWithEscapedStructuralCharacters
     */
    public function parsesValidSelector(string $selector): void
    {
        $result = Selector::parse(new ParserState($selector, Settings::create()));

        self::assertInstanceOf(Selector::class, $result);
        self::assertSame($selector, $result->getSelector());
    }

    /**
     * @test
     */
    public function parsingAttributeWithEscapedQuoteDoesNotPrematurelyCloseString(): void
    {
        $selector = 'input[placeholder="Enter \\"quoted\\" text here"]';

        $result = Selector::parse(new ParserState($selector, Settings::create()));

        self::assertInstanceOf(Selector::class, $result);
        self::assertSame($selector, $result->getSelector());
    }

    /**
     * @test
     */
    public function parseDistinguishesEscapedFromUnescapedQuotes(): void
    {
        // One backslash = escaped quote (should not close string)
        $selector = 'a[data-value="test\\"more"]';

        $result = Selector::parse(new ParserState($selector, Settings::create()));

        self::assertSame($selector, $result->getSelector());
    }

    /**
     * @test
     */
    public function parseHandlesEvenNumberOfBackslashesBeforeQuote(): void
    {
        // Two backslashes = escaped backslash + unescaped quote (should close string)
        $selector = 'a[data-value="test\\\\"]';

        $result = Selector::parse(new ParserState($selector, Settings::create()));

        self::assertSame($selector, $result->getSelector());
    }

    /**
     * @return array<non-empty-string, array{0: string}>
     */
    public static function provideInvalidSelectors(): array
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
        ];
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideInvalidSelectorsForParse(): array
    {
        return [
            'a `:not` missing the closing brace' => [':not(a'],
            'a `:not` missing the opening brace' => [':not a)'],
            'attribute value missing closing single quote' => ['a[href=\'#top]'],
            'attribute value missing closing double quote' => ['a[href="#top]'],
            'attribute value with mismatched quotes, single quote opening' => ['a[href=\'#top"]'],
            'attribute value with mismatched quotes, double quote opening' => ['a[href="#top\']'],
            'real square bracket after an escaped backslash' => ['a\\\\[[href]'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideInvalidSelectors
     * @dataProvider provideInvalidSelectorsForParse
     */
    public function parseThrowsExceptionWithInvalidSelector(string $selector): void
    {
        $this->expectException(UnexpectedTokenException::class);

        Selector::parse(new ParserState($selector, Settings::create()));
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
        ];
    }

    /**
     * @return DataProvider<non-empty-string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public function provideStopCharactersAndValidSelectors(): DataProvider
    {
        return DataProvider::cross(self::provideStopCharacters(), self::provideSelectorAndSpecificity());
    }

    /**
     * @test
     *
     * @param non-empty-string $stopCharacter
     * @param non-empty-string $selector
     *
     * @dataProvider provideStopCharactersAndValidSelectors
     */
    public function parseDoesNotConsumeStopCharacter(string $stopCharacter, string $selector): void
    {
        $subject = new ParserState($selector . $stopCharacter, Settings::create());

        Selector::parse($subject);

        self::assertSame($stopCharacter, $subject->peek());
    }

    /**
     * @test
     */
    public function parseSplitsClassWithEscapedBracketsFromFollowingAttributeSelector(): void
    {
        $className = '.\\[\\&_\\[data-piko-package\\=review-card\\]\\]\\:px-\\[25px\\]';
        $selector = $className . ' [data-piko-package=review-card]';

        $result = Selector::parse(new ParserState($selector, Settings::create()));

        $components = $result->getComponents();
        self::assertCount(3, $components);
        self::assertInstanceOf(CompoundSelector::class, $components[0]);
        self::assertSame($className, $components[0]->getValue());
        self::assertInstanceOf(Combinator::class, $components[1]);
        self::assertSame(' ', $components[1]->getValue());
        self::assertInstanceOf(CompoundSelector::class, $components[2]);
        self::assertSame('[data-piko-package=review-card]', $components[2]->getValue());
    }

    /**
     * @test
     */
    public function parseTreatsEscapedCombinatorAndSpaceAsPartOfTheCompoundSelector(): void
    {
        $result = Selector::parse(new ParserState('.foo\\>bar\\ baz qux', Settings::create()));

        $components = $result->getComponents();
        self::assertCount(3, $components);
        self::assertInstanceOf(CompoundSelector::class, $components[0]);
        self::assertSame('.foo\\>bar\\ baz', $components[0]->getValue());
        self::assertInstanceOf(Combinator::class, $components[1]);
        self::assertSame(' ', $components[1]->getValue());
        self::assertInstanceOf(CompoundSelector::class, $components[2]);
        self::assertSame('qux', $components[2]->getValue());
    }

    /**
     * @test
     *
     * @param non-empty-string $selectorWith
     * @param non-empty-string $selectorWithout
     *
     * @dataProvider provideSelectorWithAndWithoutComment
     */
    public function parsesSelectorWithComment(string $selectorWith, string $selectorWithout): void
    {
        $result = Selector::parse(new ParserState($selectorWith, Settings::create()));

        self::assertInstanceOf(Selector::class, $result);
        self::assertSame($selectorWithout, $result->getSelector());
    }

    /**
     * @test
     *
     * @param non-empty-string $selector
     *
     * @dataProvider provideSelectorWithAndWithoutComment
     */
    public function parseExtractsCommentFromSelector(string $selector): void
    {
        $result = [];
        Selector::parse(new ParserState($selector, Settings::create()), $result);

        self::assertInstanceOf(Comment::class, $result[0]);
        self::assertSame('comment', $result[0]->getComment());
    }

    /**
     * @test
     */
    public function parsesSelectorWithTwoComments(): void
    {
        $result = Selector::parse(new ParserState('/*comment1*/a/*comment2*/', Settings::create()));

        self::assertInstanceOf(Selector::class, $result);
        self::assertSame('a', $result->getSelector());
    }

    /**
     * @test
     */
    public function parseExtractsTwoCommentsFromSelector(): void
    {
        $result = [];
        Selector::parse(new ParserState('/*comment1*/a/*comment2*/', Settings::create()), $result);

        self::assertInstanceOf(Comment::class, $result[0]);
        self::assertSame('comment1', $result[0]->getComment());
        self::assertInstanceOf(Comment::class, $result[1]);
        self::assertSame('comment2', $result[1]->getComment());
    }

    /**
     * @test
     */
    public function renderWithVirginOutputFormatRendersSelectorPassedToConstructor(): void
    {
        $pattern = 'a';
        $subject = new Selector($pattern);

        self::assertSame($pattern, $subject->render(new OutputFormat()));
    }

    /**
     * @test
     */
    public function renderWithDefaultOutputFormatRendersSelectorPassedToConstructor(): void
    {
        $pattern = 'a';
        $subject = new Selector($pattern);

        self::assertSame($pattern, $subject->render(OutputFormat::create()));
    }

    /**
     * @test
     */
    public function renderWithCompactOutputFormatRendersSelectorPassedToConstructor(): void
    {
        $pattern = 'a';
        $subject = new Selector($pattern);

        self::assertSame($pattern, $subject->render(OutputFormat::createCompact()));
    }

    /**
     * @test
     */
    public function renderWithPrettyOutputFormatRendersSelectorPassedToConstructor(): void
    {
        $pattern = 'a';
        $subject = new Selector($pattern);

        self::assertSame($pattern, $subject->render(OutputFormat::createPretty()));
    }
}
