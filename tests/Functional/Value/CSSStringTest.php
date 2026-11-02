<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\Value;

use PHPUnit\Framework\TestCase;
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
}
