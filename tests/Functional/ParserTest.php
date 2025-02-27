<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\Parser;

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
}
