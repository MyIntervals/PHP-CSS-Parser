<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\CSSFunction;
use Sabberworm\CSS\Value\Size;
use Sabberworm\CSS\Value\Value;

/**
 * @covers \Sabberworm\CSS\Value\Value
 */
final class ValueTest extends TestCase
{
    /**
     * the default set of delimiters for parsing most values
     *
     * @see \Sabberworm\CSS\Rule\Rule::listDelimiterForRule
     *
     * @var list<non-empty-string>
     */
    private const DEFAULT_DELIMITERS = [',', ' ', '/'];

    /**
     * @test
     */
    public function parsesFirstArgumentInMaxFunction(): void
    {
        $parsedValue = Value::parseValue(
            new ParserState('max(300px, 400px);', Settings::create()),
            self::DEFAULT_DELIMITERS
        );

        self::assertInstanceOf(CSSFunction::class, $parsedValue);
        $size = $parsedValue->getArguments()[0];
        self::assertInstanceOf(Size::class, $size);
        self::assertSame(300.0, $size->getSize());
        self::assertSame('px', $size->getUnit());
        self::assertFalse($size->isColorComponent());
    }
}
