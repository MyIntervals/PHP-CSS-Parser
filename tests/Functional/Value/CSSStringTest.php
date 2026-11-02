<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\CSSString;

/**
 * @covers \Sabberworm\CSS\Value\CSSString
 */
final class CSSStringTest extends TestCase
{
    /**
     * @test
     */
    public function parseThrowsForUnquotedStringThatIsNotValidUtf8WithoutMultibyteSupport(): void
    {
        $parserState = new ParserState("\xFF", Settings::create()->withMultibyteSupport(false));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The CSS is not valid UTF-8.');
        $this->expectExceptionCode(1787548272);

        CSSString::parse($parserState);
    }

    /**
     * @test
     */
    public function doesNotEscapeSingleQuotesThatDoNotNeedToBeEscaped(): void
    {
        $input = "data:image/svg+xml,%3Csvg width='24' height='24' viewBox='0 0 24 24' fill='none'"
            . " xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd'"
            . " d='M14.3145 2 3V11.9987H17.5687L18 8.20761H14.3145L14.32 6.31012C14.32 5.32134 14.4207"
            . ' 4.79153 15.9426 4.79153H17.977V1H14.7223C10.8129 1 9.43687 2.83909 9.43687 '
            . " 5.93187V8.20804H7V11.9991H9.43687V23H14.3145Z' fill='black'/%3E%3C/svg%3E%0A";

        $outputFormat = OutputFormat::createPretty();

        self::assertSame("\"{$input}\"", (new CSSString($input))->render($outputFormat));

        $outputFormat->setStringQuotingType("'");

        $expected = str_replace("'", "\\'", $input);

        self::assertSame("'{$expected}'", (new CSSString($input))->render($outputFormat));
    }

    /**
     * @test
     */
    public function doesNotEscapeDoubleQuotesThatDoNotNeedToBeEscaped(): void
    {
        $input = '"Hello World"';

        $outputFormat = OutputFormat::createPretty();

        self::assertSame('"\\"Hello World\\""', (new CSSString($input))->render($outputFormat));

        $outputFormat->setStringQuotingType("'");

        self::assertSame("'{$input}'", (new CSSString($input))->render($outputFormat));
    }
}
