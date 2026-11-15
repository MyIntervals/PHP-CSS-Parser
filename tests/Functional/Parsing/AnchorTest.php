<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\Parsing;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Parsing\Anchor;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Settings;

/**
 * @covers \Sabberworm\CSS\Parsing\Anchor
 */
final class AnchorTest extends TestCase
{
    /**
     * @test
     */
    public function backtrackResetsParserStateToAnchoredPosition(): void
    {
        $parserState = new ParserState('abc', Settings::create());
        $subject = new Anchor(0, $parserState);
        $parserState->consume(2);

        $subject->backtrack();

        self::assertSame('a', $parserState->peek());
    }
}
