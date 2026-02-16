<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Property\Declaration;
use Sabberworm\CSS\Property\Selector;
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
    public function rendersDeclarationsInOrderProvided(): void
    {
        $declarationBlock = new DeclarationBlock();
        $declarationBlock->setSelectors([new Selector('.test')]);

        $declaration1 = new Declaration('background-color');
        $declaration1->setValue('transparent');
        $declarationBlock->addDeclaration($declaration1);

        $declaration2 = new Declaration('background');
        $declaration2->setValue('#222');
        $declarationBlock->addDeclaration($declaration2);

        $declaration3 = new Declaration('background-color');
        $declaration3->setValue('#fff');
        $declarationBlock->addDeclaration($declaration3);

        $expectedRendering = 'background-color: transparent;background: #222;background-color: #fff';
        self::assertStringContainsString($expectedRendering, $declarationBlock->render(new OutputFormat()));
    }
}
