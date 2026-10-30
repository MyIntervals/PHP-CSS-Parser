<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\CSSFunction;

/**
 * @covers \Sabberworm\CSS\Value\CSSFunction
 * @covers \Sabberworm\CSS\Value\Value
 * @covers \Sabberworm\CSS\Value\ValueList
 */
final class CSSFunctionTest extends TestCase
{
    /**
     * @test
     */
    public function parsesEmptyVarFallback(): void
    {
        $parserState = new ParserState('var(--w,)', Settings::create());

        $result = CSSFunction::parse($parserState);

        self::assertSame(
            [
                'class' => 'CSSFunction',
                'name' => 'var',
                'components' => [
                    [
                        'class' => 'string',
                        'value' => '--w',
                    ],
                    [
                        'class' => 'string',
                        'value' => '',
                    ],
                ],
                'separator' => ',',
            ],
            $result->getArrayRepresentation()
        );
    }
}
