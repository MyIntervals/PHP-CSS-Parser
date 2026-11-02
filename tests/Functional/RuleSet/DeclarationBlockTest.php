<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Property\Declaration;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\Settings;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @covers \Sabberworm\CSS\RuleSet\DeclarationBlock
 */
final class DeclarationBlockTest extends TestCase
{
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
     * @return array<string, array{0: string}>
     */
    public static function provideInvalidDeclarationBlock(): array
    {
        return [
            'no selector' => ['{ color: red; }'],
            'invalid selector' => ['/ { color: red; }'],
            'no opening brace' => ['body color: red; }'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideInvalidDeclarationBlock
     */
    public function parseReturnsNullForInvalidDeclarationBlock(string $invalidDeclarationBlock): void
    {
        $parserState = new ParserState($invalidDeclarationBlock, Settings::create());

        $result = DeclarationBlock::parse($parserState);

        self::assertNull($result);
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

        DeclarationBlock::parse($parserState);
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
    public function rendersDeclarationsInOrderProvided(): void
    {
        $declarationBlock = new DeclarationBlock();
        $declarationBlock->setSelectors([new Selector('.test')]);

        $declaration1 = new Declaration('background-color');
        $declaration1->setValue('transparent');
        $declarationBlock->addDeclaration($declaration1);

        $declaration2 = new Declaration('background');
        $declaration2->setValue('#222');
        $declarationBlock->addDeclaration($declaration2);

        $declaration3 = new Declaration('background-color');
        $declaration3->setValue('#fff');
        $declarationBlock->addDeclaration($declaration3);

        $expectedRendering = 'background-color: transparent;background: #222;background-color: #fff';
        self::assertStringContainsString($expectedRendering, $declarationBlock->render(new OutputFormat()));
    }
}
