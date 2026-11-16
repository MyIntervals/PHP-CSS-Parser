<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\CSSList;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\Settings;

/**
 * @covers \Sabberworm\CSS\CSSList\CSSList
 */
final class CSSListTest extends TestCase
{
    /**
     * The content provided must (currently) be in the same format as the expected rendering.
     *
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public function provideValidContentForParsing(): array
    {
        return [
            'at-import rule' => ['@import url("foo.css");'],
            'rule with declaration block' => ['a {color: green;}'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $followingContent
     *
     * @dataProvider provideValidContentForParsing
     */
    public function parseListAtRootLevelSkipsErroneousClosingBraceAndParsesFollowingContent(
        string $followingContent
    ): void {
        $parserState = new ParserState('}' . $followingContent, Settings::create());
        // The subject needs to be a `Document`, as that is currently the test for 'root level'.
        // Otherwise `}` will be treated as 'end of list'.
        $subject = new Document();

        CSSList::parseList($parserState, $subject);

        self::assertSame($followingContent, $subject->render(new OutputFormat()));
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: string}>
     */
    public static function provideCssAndExpectedRenderingInStrictMode(): array
    {
        return [
            '@charset followed by a rule' => ['@charset "UTF-8"; a {}', "@charset \"UTF-8\";\na {}"],
            '@import with media query' => ['@import url(a.css) print;', '@import url("a.css") print;'],
            '@import with whitespace before semicolon' => ['@import url(a.css) ;', '@import url("a.css");'],
            '@import at end of input without semicolon' => ['@import url(a.css)', '@import url("a.css");'],
            '@namespace with prefix' => [
                '@namespace svg url(http://www.w3.org/2000/svg);',
                '@namespace svg url("http://www.w3.org/2000/svg");',
            ],
            '@namespace without prefix' => ['@namespace "http://x";', '@namespace "http://x";'],
            'vendor-prefixed @keyframes' => [
                '@-webkit-keyframes spin { from {} }',
                '@-webkit-keyframes spin {from {}}',
            ],
            '@media block' => ['@media screen { a {} }', '@media screen {a {}}'],
            'at-rule statement' => ['@layer base;', '@layer base;'],
            'escaped quote in at-rule arguments' => ['@font-face \\"x {}', '@font-face \\"x {}'],
            'brace inside string in at-rule arguments' => [
                '@supports (content: "{") { a {} }',
                '@supports (content: "{") {a {}}',
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideCssAndExpectedRenderingInStrictMode
     */
    public function parseListInStrictModeParsesValidCss(string $css, string $expectedRendering): void
    {
        $subject = new Document();

        CSSList::parseList(new ParserState($css, Settings::create()->beStrict()), $subject);

        self::assertSame($expectedRendering, $subject->render(OutputFormat::create()));
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: class-string<\Throwable>, 2: non-empty-string}>
     */
    public static function provideInvalidCssForStrictMode(): array
    {
        return [
            'closing brace at root level' => ['} a {}', SourceException::class, 'Unopened {'],
            '@charset inside block' => [
                '@media screen { @charset "x"; }',
                UnexpectedTokenException::class,
                '@charset may only occur in root document',
            ],
            '@charset after a rule' => [
                'a {} @charset "x";',
                UnexpectedTokenException::class,
                '@charset must be the first parseable token in a document',
            ],
            '@namespace with URL of invalid type' => [
                '@namespace foo bar;',
                UnexpectedTokenException::class,
                'Wrong namespace url of invalid type',
            ],
            'unmatched parenthesis in at-rule arguments' => [
                '@media (screen { a {} }',
                SourceException::class,
                'Unmatched brace count in media query',
            ],
            'unclosed block' => ['@media screen { a {}', SourceException::class, 'Unexpected end of document'],
        ];
    }

    /**
     * @test
     *
     * @param class-string<\Throwable> $expectedException
     *
     * @dataProvider provideInvalidCssForStrictMode
     */
    public function parseListInStrictModeThrowsForInvalidCss(
        string $css,
        string $expectedException,
        string $expectedMessage
    ): void {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);

        CSSList::parseList(new ParserState($css, Settings::create()->beStrict()), new Document());
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: string}>
     */
    public static function provideInvalidCssAndExpectedRenderingInLenientMode(): array
    {
        return [
            '@charset inside block' => ['@media screen { @charset "x"; a {} }', '@media screen {a {}}'],
            '@charset after a rule' => ['a {} @charset "x";', 'a {}'],
            'unclosed block' => ['@media screen { a {}', '@media screen {a {}}'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideInvalidCssAndExpectedRenderingInLenientMode
     */
    public function parseListInLenientModeSkipsInvalidContent(string $css, string $expectedRendering): void
    {
        $subject = new Document();

        CSSList::parseList(new ParserState($css, Settings::create()), $subject);

        self::assertSame($expectedRendering, $subject->render(OutputFormat::create()));
    }

    /**
     * @test
     */
    public function renderSeparatesBlocks(): void
    {
        $subject = new Document();
        $first = new DeclarationBlock();
        $first->setSelectors('a');
        $second = new DeclarationBlock();
        $second->setSelectors('b');
        $subject->setContents([$first, $second]);

        $result = $subject->render(OutputFormat::createPretty());

        self::assertSame("\na {}\n\nb {}\n", $result);
    }

    /**
     * @test
     */
    public function renderWithIgnoredExceptionsSkipsItemsThatCannotBeRendered(): void
    {
        $subject = new Document();
        $first = new DeclarationBlock();
        $first->setSelectors('a');
        $second = new DeclarationBlock();
        $second->setSelectors('b');
        $subject->setContents([$first, new DeclarationBlock(), $second]);

        $result = $subject->render(OutputFormat::create()->setIgnoreExceptions(true));

        self::assertSame("a {}\nb {}", $result);
    }
}
