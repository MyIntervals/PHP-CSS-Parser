<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\LineName;

/**
 * @covers \Sabberworm\CSS\Value\LineName
 */
final class LineNameTest extends TestCase
{
    /**
     * @test
     */
    public function parseParsesSpaceSeparatedNames(): void
    {
        $result = LineName::parse(new ParserState('[ first  second ]', Settings::create()));

        self::assertSame(['first', 'second'], $result->getListComponents());
    }

    /**
     * @test
     */
    public function parseInLenientModeAcceptsEmptyBrackets(): void
    {
        $result = LineName::parse(new ParserState('[]', Settings::create()));

        self::assertSame([], $result->getListComponents());
    }

    /**
     * @test
     */
    public function parseInLenientModeThrowsForInvalidIdentifier(): void
    {
        $this->expectException(UnexpectedTokenException::class);

        LineName::parse(new ParserState('[a {]', Settings::create()));
    }

    /**
     * @test
     */
    public function parseInStrictModeThrowsForEmptyBrackets(): void
    {
        $this->expectException(UnexpectedTokenException::class);

        LineName::parse(new ParserState('[]', Settings::create()->beStrict()));
    }

    /**
     * @test
     */
    public function renderWrapsNamesInBrackets(): void
    {
        $subject = new LineName(['first', 'second']);

        self::assertSame('[first second]', $subject->render(OutputFormat::create()));
    }
}
