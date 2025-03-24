<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\Settings;

/**
 * @covers \Sabberworm\CSS\RuleSet\DeclarationBlock
 */
final class DeclarationBlockTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function provideInvalidDeclarationBlock(): array
    {
        return [
            'no selector' => ['{ color: red; }'],
            'invalid selector' => ['/ { color: red; }'],
            'no opening brace' => ['body color: red; }'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideInvalidDeclarationBlock
     */
    public function parseReturnsNullForInvalidDeclarationBlock(string $invalidDeclarationBlock): void
    {
        $parserState = new ParserState($invalidDeclarationBlock, Settings::create());

        $result = DeclarationBlock::parse($parserState);

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function rendersRulesInOrderProvided(): void
    {
        $declarationBlock = new DeclarationBlock();
        $declarationBlock->setSelectors([new Selector('.test')]);

        $rule1 = new Rule('background-color');
        $rule1->setValue('transparent');
        $declarationBlock->addRule($rule1);

        $rule2 = new Rule('background');
        $rule2->setValue('#222');
        $declarationBlock->addRule($rule2);

        $rule3 = new Rule('background-color');
        $rule3->setValue('#fff');
        $declarationBlock->addRule($rule3);

        $expectedRendering = 'background-color: transparent;background: #222;background-color: #fff';
        self::assertStringContainsString($expectedRendering, $declarationBlock->render(new OutputFormat()));
    }
}
