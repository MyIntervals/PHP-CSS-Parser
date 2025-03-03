<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Settings;

/**
 * @covers \Sabberworm\CSS\RuleSet\DeclarationBlock
 */
final class DeclarationBlockTest extends TestCase
{
    /**
     * @test
     */
    public function rendersRulesInOrderProvided(): void
    {
        $css = '
            .test {
                background-color:transparent;
                background:#222;
                background-color:#fff;
            }';
        $expectedRendering = 'background-color: transparent;background: #222;background-color: #fff;';

        $declarationBlock = DeclarationBlock::parse(new ParserState($css, Settings::create()));

        self::assertStringContainsString($expectedRendering, $declarationBlock->render(new OutputFormat()));
    }
}
