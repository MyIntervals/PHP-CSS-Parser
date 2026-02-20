<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Position\Positionable;
use Sabberworm\CSS\Property\Declaration;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\RuleSet\RuleSet;
use Sabberworm\CSS\Settings;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @covers \Sabberworm\CSS\RuleSet\DeclarationBlock
 */
final class DeclarationBlockTest extends TestCase
{
    use DeclarationListTests;

    /**
     * @var DeclarationBlock
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new DeclarationBlock();
    }

    /**
     * @test
     */
    public function implementsCSSElement(): void
    {
        self::assertInstanceOf(CSSElement::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsCSSListItem(): void
    {
        self::assertInstanceOf(CSSListItem::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsPositionable(): void
    {
        self::assertInstanceOf(Positionable::class, $this->subject);
    }

    /**
     * @test
     */
    public function getLineNumberByDefaultReturnsNull(): void
    {
        $result = $this->subject->getLineNumber();

        self::assertNull($result);
    }

    /**
     * @return array<non-empty-string, array{0: int<1, max>|null}>
     */
    public function provideLineNumber(): array
    {
        return [
            'null' => [null],
            'line 1' => [1],
            'line 42' => [42],
        ];
    }

    /**
     * @test
     *
     * @param int<1, max>|null $lineNumber
     *
     * @dataProvider provideLineNumber
     */
    public function getLineNumberReturnsLineNumberPassedToConstructor(?int $lineNumber): void
    {
        $subject = new DeclarationBlock($lineNumber);

        $result = $subject->getLineNumber();

        self::assertSame($lineNumber, $result);
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideSelector(): array
    {
        return [
            'type' => ['body'],
            'class' => ['.teapot'],
            'type & class' => ['img.teapot'],
            'id' => ['#my-mug'],
            'type & id' => ['h2#my-mug'],
            'pseudo-class' => [':hover'],
            'type & pseudo-class' => ['a:hover'],
            '`not`' => [':not(#your-mug)'],
            '`not` with multiple arguments' => [':not(#your-mug, .their-mug)'],
            'pseudo-element' => ['::before'],
            'attribute with `"`' => ['[alt="{}()[]\\"\',"]'],
            'attribute with `\'`' => ['[alt=\'{}()[]"\\\',\']'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $selector
     *
     * @dataProvider provideSelector
     */
    public function parsesSingleSelector(string $selector): void
    {
        $subject = DeclarationBlock::parse(new ParserState($selector . ' {}', Settings::create()));

        self::assertInstanceOf(DeclarationBlock::class, $subject);
        self::assertSame([$selector], self::getSelectorsAsStrings($subject));
    }

    /**
     * @return DataProvider<non-empty-string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function provideTwoSelectors(): DataProvider
    {
        return DataProvider::cross(self::provideSelector(), self::provideSelector());
    }

    /**
     * @test
     *
     * @param non-empty-string $firstSelector
     * @param non-empty-string $secondSelector
     *
     * @dataProvider provideTwoSelectors
     */
    public function parsesTwoCommaSeparatedSelectors(string $firstSelector, string $secondSelector): void
    {
        $joinedSelectors = $firstSelector . ', ' . $secondSelector;

        $subject = DeclarationBlock::parse(new ParserState($joinedSelectors . ' {}', Settings::create()));

        self::assertInstanceOf(DeclarationBlock::class, $subject);
        self::assertSame([$firstSelector, $secondSelector], self::getSelectorsAsStrings($subject));
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function provideSelectorWithAndWithoutComment(): array
    {
        return [
            'comment before' => ['/*comment*/body', 'body'],
            'comment after' => ['body/*comment*/', 'body'],
            'comment within' => ['./*comment*/teapot', '.teapot'],
            'comment within function' => [':not(#your-mug,/*comment*/.their-mug)', ':not(#your-mug,.their-mug)'],
        ];
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
        $subject = DeclarationBlock::parse(new ParserState($selectorWith . ' {}', Settings::create()));

        self::assertInstanceOf(DeclarationBlock::class, $subject);
        self::assertSame([$selectorWithout], self::getSelectorsAsStrings($subject));
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
        $subject = DeclarationBlock::parse(new ParserState($selector . ' {}', Settings::create()));

        self::assertInstanceOf(DeclarationBlock::class, $subject);
        self::assertSame(['comment'], self::getCommentsAsStrings($subject));
    }

    /**
     * @test
     */
    public function parsesSelectorWithTwoComments(): void
    {
        $subject = DeclarationBlock::parse(new ParserState('/*comment1*/a/*comment2*/ {}', Settings::create()));

        self::assertInstanceOf(DeclarationBlock::class, $subject);
        self::assertSame(['a'], self::getSelectorsAsStrings($subject));
    }

    /**
     * @test
     */
    public function parseExtractsTwoCommentsFromSelector(): void
    {
        $subject = DeclarationBlock::parse(new ParserState('/*comment1*/a/*comment2*/ {}', Settings::create()));

        self::assertInstanceOf(DeclarationBlock::class, $subject);
        self::assertSame(['comment1', 'comment2'], self::getCommentsAsStrings($subject));
    }

    /**
     * @return array<non-empty-string, array{0: string, 1: non-empty-string}>
     */
    public static function provideInvalidSelectorAndExpectedExceptionMessage(): array
    {
        return [
            'no selector' => ['', 'Token “selector” (literal) not found. Got “{”. [line no: 1]'],
            'lone `(`' => ['(', 'Token “)” (literal) not found. Got “{”. [line no: 1]'],
            'lone `)`' => [')', 'Token “anything but” (literal) not found. Got “)”. [line no: 1]'],
            'lone `,`' => [',', 'Token “selector” (literal) not found. Got “,”. [line no: 1]'],
            'unclosed `(`' => [':not(#your-mug', 'Token “)” (literal) not found. Got “{”. [line no: 1]'],
            'extra `)`' => [':not(#your-mug))', 'Token “anything but” (literal) not found. Got “)”. [line no: 1]'],
            '`,` missing left operand' => [', a', 'Token “selector” (literal) not found. Got “,”. [line no: 1]'],
            '`,` missing right operand' => ['a,', 'Token “selector” (literal) not found. Got “{”. [line no: 1]'],
        ];
    }

    /**
     * @return array<non-empty-string, array{0: string}>
     */
    public static function provideInvalidSelector(): array
    {
        // Re-use the set of invalid selectors, but remove the expected exception message for tests that don't need it.
        return \array_map(
            /**
             * @param array{0: string, 1: non-empty-string}
             *
             * @return array<{0: string}>
             */
            static function (array $testData): array {
                return [$testData[0]];
            },
            self::provideInvalidSelectorAndExpectedExceptionMessage()
        );
    }

    /**
     * @test
     *
     * @dataProvider provideInvalidSelector
     */
    public function parseSkipsBlockWithInvalidSelector(string $selector): void
    {
        static $nextCss = ' .next {}';
        $css = $selector . ' {}' . $nextCss;
        $parserState = new ParserState($css, Settings::create());

        $subject = DeclarationBlock::parse($parserState);

        self::assertNull($subject);
        self::assertTrue($parserState->comes($nextCss));
    }

    /**
     * @test
     *
     * @param non-empty-string $expectedExceptionMessage
     *
     * @dataProvider provideInvalidSelectorAndExpectedExceptionMessage
     */
    public function parseInStrictModeThrowsExceptionWithInvalidSelector(
        string $selector,
        string $expectedExceptionMessage
    ): void {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $parserState = new ParserState($selector . ' {}', Settings::create()->beStrict());

        $subject = DeclarationBlock::parse($parserState);
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideClosingBrace(): array
    {
        return [
            'as is' => ['}'],
            'with space before' => [' }'],
            'with newline before' => ["\n}"],
        ];
    }

    /**
     * @return DataProvider<non-empty-string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function provideInvalidSelectorAndClosingBrace(): DataProvider
    {
        return DataProvider::cross(self::provideInvalidSelector(), self::provideClosingBrace());
    }

    /**
     * TODO: It's probably not the responsibility of `DeclarationBlock` to deal with this.
     *
     * @test
     *
     * @param non-empty-string $selector
     * @param non-empty-string $closingBrace
     *
     * @dataProvider provideInvalidSelectorAndClosingBrace
     */
    public function parseConsumesClosingBraceAfterInvalidSelector(string $selector, string $closingBrace): void
    {
        $parserState = new ParserState($selector . $closingBrace, Settings::create());

        DeclarationBlock::parse($parserState);

        self::assertTrue($parserState->isEnd());
    }

    /**
     * @return array<non-empty-string, array{0: string}>
     */
    public static function provideOptionalWhitespace(): array
    {
        return [
            'none' => [''],
            'space' => [' '],
            'newline' => ["\n"],
        ];
    }

    /**
     * @return DataProvider<non-empty-string, array{0: non-empty-string, 1: string}>
     */
    public static function provideInvalidSelectorAndOptionalWhitespace(): DataProvider
    {
        return DataProvider::cross(self::provideInvalidSelector(), self::provideOptionalWhitespace());
    }

    /**
     * TODO: It's probably not the responsibility of `DeclarationBlock` to deal with this.
     *
     * @test
     *
     * @param non-empty-string $selector
     *
     * @dataProvider provideInvalidSelectorAndOptionalWhitespace
     */
    public function parseConsumesToEofIfNoClosingBraceAfterInvalidSelector(
        string $selector,
        string $optionalWhitespace
    ): void {
        $parserState = new ParserState($selector . $optionalWhitespace, Settings::create());

        DeclarationBlock::parse($parserState);

        self::assertTrue($parserState->isEnd());
    }

    /**
     * @test
     */
    public function getRuleSetOnVirginReturnsARuleSet(): void
    {
        $result = $this->subject->getRuleSet();

        self::assertInstanceOf(RuleSet::class, $result);
    }

    /**
     * @test
     */
    public function getRuleSetAfterRulesSetReturnsARuleSet(): void
    {
        $this->subject->setDeclarations([new Declaration('color')]);

        $result = $this->subject->getRuleSet();

        self::assertInstanceOf(RuleSet::class, $result);
    }

    /**
     * @test
     */
    public function getRuleSetOnVirginReturnsObjectWithoutDeclarations(): void
    {
        $result = $this->subject->getRuleSet();

        self::assertSame([], $result->getDeclarations());
    }

    /**
     * @test
     *
     * @param list<string> $propertyNamesToSet
     *
     * @dataProvider providePropertyNames
     */
    public function getRuleSetReturnsObjectWithDeclarationsSet(array $propertyNamesToSet): void
    {
        $declarations = self::createDeclarationsFromPropertyNames($propertyNamesToSet);
        $this->subject->setDeclarations($declarations);

        $result = $this->subject->getRuleSet();

        self::assertSame($declarations, $result->getDeclarations());
    }

    /**
     * @test
     */
    public function getRuleSetByDefaultReturnsObjectWithNullLineNumber(): void
    {
        $result = $this->subject->getRuleSet();

        self::assertNull($result->getLineNumber());
    }

    /**
     * @test
     *
     * @param int<1, max>|null $lineNumber
     *
     * @dataProvider provideLineNumber
     */
    public function getRuleSetReturnsObjectWithLineNumberPassedToConstructor(?int $lineNumber): void
    {
        $subject = new DeclarationBlock($lineNumber);

        $result = $subject->getRuleSet();

        self::assertSame($lineNumber, $result->getLineNumber());
    }

    /**
     * @test
     *
     * Any type of array may be passed to the method, but the resultant property should be a `list`.
     */
    public function setSelectorsIgnoresKeys(): void
    {
        $subject = new DeclarationBlock();
        $subject->setSelectors(['Bob' => 'html', 'Mary' => 'body']);

        $result = $subject->getSelectors();

        self::assertSame([0, 1], \array_keys($result));
    }

    /**
     * @test
     *
     * @param non-empty-string $selector
     *
     * @dataProvider provideSelector
     */
    public function setSelectorsSetsSingleSelectorProvidedAsString(string $selector): void
    {
        $subject = new DeclarationBlock();

        $subject->setSelectors($selector);

        $result = $subject->getSelectors();
        self::assertSame([$selector], self::getSelectorsAsStrings($subject));
    }

    /**
     * @test
     *
     * @param non-empty-string $firstSelector
     * @param non-empty-string $secondSelector
     *
     * @dataProvider provideTwoSelectors
     */
    public function setSelectorsSetsTwoCommaSeparatedSelectorsProvidedAsString(
        string $firstSelector,
        string $secondSelector
    ): void {
        $joinedSelectors = $firstSelector . ', ' . $secondSelector;
        $subject = new DeclarationBlock();

        $subject->setSelectors($joinedSelectors);

        $result = $subject->getSelectors();
        self::assertSame([$firstSelector, $secondSelector], self::getSelectorsAsStrings($subject));
    }

    /**
     * Provides selectors that would be parsed without error in the context of full CSS, but are nonetheless invalid.
     *
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideInvalidStandaloneSelector(): array
    {
        return [
            'rogue `{`' => ['a { b'],
            'rogue `}`' => ['a } b'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $selector
     *
     * @dataProvider provideInvalidSelector
     * @dataProvider provideInvalidStandaloneSelector
     */
    public function setSelectorsThrowsExceptionWithInvalidSelector(string $selector): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessageMatches('/^Selector\\(s\\) string is not valid./');

        $subject = new DeclarationBlock();

        $subject->setSelectors($selector);
    }

    /**
     * @test
     */
    public function getArrayRepresentationThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->getArrayRepresentation();
    }

    /**
     * @return list<string>
     */
    private static function getSelectorsAsStrings(DeclarationBlock $declarationBlock): array
    {
        return \array_map(
            static function (Selector $selectorObject): string {
                return $selectorObject->getSelector();
            },
            $declarationBlock->getSelectors()
        );
    }

    /**
     * @return list<string>
     */
    private static function getCommentsAsStrings(DeclarationBlock $declarationBlock): array
    {
        return \array_map(
            static function (Comment $comment): string {
                return $comment->getComment();
            },
            $declarationBlock->getComments()
        );
    }
}
