<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Settings;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @covers \Sabberworm\CSS\Parser
 */
final class ParserTest extends TestCase
{
    /**
     * @test
     */
    public function parseWithEmptyStringReturnsDocument(): void
    {
        $parser = new Parser('');

        $result = $parser->parse();

        self::assertInstanceOf(Document::class, $result);
    }

    /**
     * @test
     */
    public function parseWithOneRuleSetReturnsDocument(): void
    {
        $parser = new Parser('.thing { }');

        $result = $parser->parse();

        self::assertInstanceOf(Document::class, $result);
    }

    /**
     * @return array<non-empty-string, array{0: string}>
     */
    public static function provideEmptyCss(): array
    {
        return [
            'empty string' => [''],
            'space' => [' '],
            'newline' => ["\n"],
            'carriage return' => ["\r"],
            'tab' => ["\t"],
            'Windows line ending' => ["\r\n"],
            'comment' => ['/* I get put in a separate property */'],
        ];
    }

    /**
     * @return array<non-empty-string, array{0: bool}>
     */
    public static function provideLenientParsingFlag(): array
    {
        return [
            'strict parsing' => [false],
            'lenient parsing' => [true],
        ];
    }

    /**
     * @return DataProvider<non-empty-string, array{0: string, 1: bool}>
     */
    public static function provideEmptyCssAndLenientParsingFlag(): DataProvider
    {
        return DataProvider::cross(static::provideEmptyCss(), static::provideLenientParsingFlag());
    }

    /**
     * @test
     *
     * @dataProvider provideEmptyCssAndLenientParsingFlag
     */
    public function parsesEmptyCss(string $css, bool $parseLeniently): void
    {
        $parser = new Parser($css, Settings::create()->withLenientParsing($parseLeniently));

        $result = $parser->parse();

        // Note: Comments for the document are accessed separately via `getComments()`.
        self::assertSame([], $result->getContents());
    }
}
