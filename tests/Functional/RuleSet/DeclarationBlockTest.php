<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\DeclarationBlock;

/**
 * @covers \Sabberworm\CSS\RuleSet\DeclarationBlock
 */
final class DeclarationBlockTest extends TestCase
{
    /**
     * @test
     */
    public function rendersRulesInOrderProvided()
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
        self::assertContains($expectedRendering, $declarationBlock->render(new OutputFormat()));
    }
}
