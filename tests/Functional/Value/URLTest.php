<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\CSSString;
use Sabberworm\CSS\Value\URL;

/**
 * @covers \Sabberworm\CSS\Value\URL
 */
final class URLTest extends TestCase
{
    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideUrlSyntax(): array
    {
        return [
            'url() with double quotes' => ['url("a.png")'],
            'url() with single quotes' => ["url('a.png')"],
            'url() with whitespace inside parentheses' => ['url( "a.png" )'],
            'uppercase URL()' => ['URL("a.png")'],
            'string without url()' => ['"a.png"'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideUrlSyntax
     */
    public function parseParsesUrl(string $css): void
    {
        $result = URL::parse(new ParserState($css, Settings::create()));

        self::assertSame('a.png', $result->getURL()->getString());
    }

    /**
     * @test
     */
    public function renderWrapsUrlInUrlFunction(): void
    {
        $subject = new URL(new CSSString('a.png'));

        self::assertSame('url("a.png")', $subject->render(OutputFormat::create()));
    }
}
