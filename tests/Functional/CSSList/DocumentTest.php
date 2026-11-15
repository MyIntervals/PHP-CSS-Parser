<?php

declare(strict_types=1);

namespace Functional\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Property\Charset;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\CSSString;

/**
 * @covers \Sabberworm\CSS\CSSList\Document
 */
final class DocumentTest extends TestCase
{
    /**
     * @test
     */
    public function renderWithoutOutputFormatCanRenderEmptyDocument(): void
    {
        $subject = new Document();

        self::assertSame('', $subject->render());
    }

    /**
     * @test
     */
    public function renderWithVirginOutputFormatCanRenderEmptyDocument(): void
    {
        $subject = new Document();

        self::assertSame('', $subject->render(new OutputFormat()));
    }

    /**
     * @test
     */
    public function renderWithDefaultOutputFormatCanRenderEmptyDocument(): void
    {
        $subject = new Document();

        self::assertSame('', $subject->render(OutputFormat::create()));
    }

    /**
     * @test
     */
    public function renderWithCompactOutputFormatCanRenderEmptyDocument(): void
    {
        $subject = new Document();

        self::assertSame('', $subject->render(OutputFormat::createCompact()));
    }

    /**
     * @test
     */
    public function renderWithPrettyOutputFormatCanRenderEmptyDocument(): void
    {
        $subject = new Document();

        self::assertSame('', $subject->render(OutputFormat::createPretty()));
    }

    /**
     * Builds a subject with one `@charset` rule and one `@media` rule.
     */
    private function buildSubjectWithAtRules(): Document
    {
        $subject = new Document();
        $charset = new Charset(new CSSString('UTF-8'));
        $subject->append($charset);
        $mediaQuery = new AtRuleBlockList('media', 'screen');
        $subject->append($mediaQuery);

        return $subject;
    }

    /**
     * @test
     */
    public function renderWithoutOutputFormatCanRenderAtRules(): void
    {
        $subject = $this->buildSubjectWithAtRules();

        $expected = '@charset "UTF-8";' . "\n" . '@media screen {}';
        self::assertSame($expected, $subject->render());
    }

    /**
     * @test
     */
    public function renderWithVirginOutputFormatCanRenderAtRules(): void
    {
        $subject = $this->buildSubjectWithAtRules();

        $expected = '@charset "UTF-8";' . "\n" . '@media screen {}';
        self::assertSame($expected, $subject->render(new OutputFormat()));
    }

    /**
     * @test
     */
    public function renderWithDefaultOutputFormatCanRenderAtRules(): void
    {
        $subject = $this->buildSubjectWithAtRules();

        $expected = '@charset "UTF-8";' . "\n" . '@media screen {}';
        self::assertSame($expected, $subject->render(OutputFormat::create()));
    }

    /**
     * @test
     */
    public function renderWithCompactOutputFormatCanRenderAtRules(): void
    {
        $subject = $this->buildSubjectWithAtRules();

        $expected = '@charset "UTF-8";@media screen{}';
        self::assertSame($expected, $subject->render(OutputFormat::createCompact()));
    }

    /**
     * @test
     */
    public function renderWithPrettyOutputFormatCanRenderAtRules(): void
    {
        $subject = $this->buildSubjectWithAtRules();

        $expected = "\n" . '@charset "UTF-8";' . "\n\n" . '@media screen {}' . "\n";
        self::assertSame($expected, $subject->render(OutputFormat::createPretty()));
    }

    /**
     * @test
     */
    public function parseParsesAllBlocks(): void
    {
        $result = Document::parse(new ParserState('#a {} .b {} p {}', Settings::create()));

        self::assertCount(3, $result->getContents());
    }

    /**
     * @test
     */
    public function getSelectorsBySpecificityWithoutFilterReturnsAllSelectors(): void
    {
        $subject = Document::parse(new ParserState('#a {} .b {} p {}', Settings::create()));

        self::assertCount(3, $subject->getSelectorsBySpecificity());
    }

    /**
     * @test
     */
    public function getSelectorsBySpecificityReturnsOnlyMatchingSelectors(): void
    {
        $subject = Document::parse(new ParserState('#a {} .b {} p {}', Settings::create()));

        $result = $subject->getSelectorsBySpecificity('>= 10');

        $selectors = \array_map(
            static function (Selector $selector): string {
                return $selector->getSelector();
            },
            $result
        );
        self::assertSame(['#a', '.b'], $selectors);
    }
}
