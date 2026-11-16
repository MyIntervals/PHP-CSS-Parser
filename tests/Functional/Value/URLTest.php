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
     * @test
     */
    public function parseParsesUrlWithDoubleQuotes(): void
    {
        $parserState = new ParserState('url("a.png")', Settings::create());

        $result = URL::parse($parserState);

        self::assertSame('a.png', $result->getURL()->getString());
    }

    /**
     * @test
     */
    public function parseParsesUrlWithSingleQuotes(): void
    {
        $parserState = new ParserState('url(\'a.png\')', Settings::create());

        $result = URL::parse($parserState);

        self::assertSame('a.png', $result->getURL()->getString());
    }

    /**
     * @test
     */
    public function parseParsesUrlWithWhitespaceInsideParentheses(): void
    {
        $parserState = new ParserState('url( "a.png" )', Settings::create());

        $result = URL::parse($parserState);

        self::assertSame('a.png', $result->getURL()->getString());
    }

    /**
     * @test
     */
    public function parseParsesUppercaseUrlFunction(): void
    {
        $parserState = new ParserState('URL("a.png")', Settings::create());

        $result = URL::parse($parserState);

        self::assertSame('a.png', $result->getURL()->getString());
    }

    /**
     * @test
     */
    public function parseParsesStringWithoutUrlFunction(): void
    {
        $parserState = new ParserState('"a.png"', Settings::create());

        $result = URL::parse($parserState);

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
