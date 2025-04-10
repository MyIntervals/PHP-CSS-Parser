<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\Tests\Unit\CSSList\Fixtures\ConcreteCSSBlockList;
use Sabberworm\CSS\Value\CSSFunction;
use Sabberworm\CSS\Value\CSSString;
use Sabberworm\CSS\Value\Size;

/**
 * @covers \Sabberworm\CSS\CSSList\CSSBlockList
 */
final class CSSBlockListTest extends TestCase
{
    /**
     * @test
     */
    public function getAllValuesWhenNoContentSetReturnsEmptyArray(): void
    {
        $subject = new ConcreteCSSBlockList();

        self::assertSame([], $subject->getAllValues());
    }

    /**
     * @test
     */
    public function getAllValuesReturnsOneValueDirectlySetAsContent(): void
    {
        $subject = new ConcreteCSSBlockList();

        $value = new CSSString('Superfont');

        $declarationBlock = new DeclarationBlock();
        $rule = new Rule('font-family');
        $rule->setValue($value);
        $declarationBlock->addRule($rule);
        $subject->setContents([$declarationBlock]);

        $result = $subject->getAllValues();

        self::assertSame([$value], $result);
    }

    /**
     * @test
     */
    public function getAllValuesReturnsMultipleValuesDirectlySetAsContentInOneDeclarationBlock(): void
    {
        $subject = new ConcreteCSSBlockList();

        $value1 = new CSSString('Superfont');
        $value2 = new CSSString('aquamarine');

        $declarationBlock = new DeclarationBlock();
        $rule1 = new Rule('font-family');
        $rule1->setValue($value1);
        $declarationBlock->addRule($rule1);
        $rule2 = new Rule('color');
        $rule2->setValue($value2);
        $declarationBlock->addRule($rule2);
        $subject->setContents([$declarationBlock]);

        $result = $subject->getAllValues();

        self::assertSame([$value1, $value2], $result);
    }

    /**
     * @test
     */
    public function getAllValuesReturnsMultipleValuesDirectlySetAsContentInMultipleDeclarationBlocks(): void
    {
        $subject = new ConcreteCSSBlockList();

        $value1 = new CSSString('Superfont');
        $value2 = new CSSString('aquamarine');

        $declarationBlock1 = new DeclarationBlock();
        $rule1 = new Rule('font-family');
        $rule1->setValue($value1);
        $declarationBlock1->addRule($rule1);
        $declarationBlock2 = new DeclarationBlock();
        $rule2 = new Rule('color');
        $rule2->setValue($value2);
        $declarationBlock2->addRule($rule2);
        $subject->setContents([$declarationBlock1, $declarationBlock2]);

        $result = $subject->getAllValues();

        self::assertSame([$value1, $value2], $result);
    }

    /**
     * @test
     */
    public function getAllValuesReturnsValuesWithinAtRuleBlockList(): void
    {
        $subject = new ConcreteCSSBlockList();

        $value = new CSSString('Superfont');

        $declarationBlock = new DeclarationBlock();
        $rule = new Rule('font-family');
        $rule->setValue($value);
        $declarationBlock->addRule($rule);
        $atRuleBlockList = new AtRuleBlockList('media');
        $atRuleBlockList->setContents([$declarationBlock]);
        $subject->setContents([$atRuleBlockList]);

        $result = $subject->getAllValues();

        self::assertSame([$value], $result);
    }

    /**
     * @test
     */
    public function getAllValuesWithElementProvidedReturnsOnlyValuesWithinThatElement(): void
    {
        $subject = new ConcreteCSSBlockList();

        $value1 = new CSSString('Superfont');
        $value2 = new CSSString('aquamarine');

        $declarationBlock1 = new DeclarationBlock();
        $rule1 = new Rule('font-family');
        $rule1->setValue($value1);
        $declarationBlock1->addRule($rule1);
        $declarationBlock2 = new DeclarationBlock();
        $rule2 = new Rule('color');
        $rule2->setValue($value2);
        $declarationBlock2->addRule($rule2);
        $subject->setContents([$declarationBlock1, $declarationBlock2]);

        $result = $subject->getAllValues($declarationBlock1);

        self::assertSame([$value1], $result);
    }

    /**
     * @test
     */
    public function getAllValuesWithSearchStringProvidedReturnsOnlyValuesFromMatchingRules(): void
    {
        $subject = new ConcreteCSSBlockList();

        $value1 = new CSSString('Superfont');
        $value2 = new CSSString('aquamarine');

        $declarationBlock = new DeclarationBlock();
        $rule1 = new Rule('font-family');
        $rule1->setValue($value1);
        $declarationBlock->addRule($rule1);
        $rule2 = new Rule('color');
        $rule2->setValue($value2);
        $declarationBlock->addRule($rule2);
        $subject->setContents([$declarationBlock]);

        $result = $subject->getAllValues('font-');

        self::assertSame([$value1], $result);
    }

    /**
     * @test
     */
    public function getAllValuesByDefaultDoesNotReturnValuesInFunctionArguments(): void
    {
        $subject = new ConcreteCSSBlockList();

        $value1 = new Size(10, 'px');
        $value2 = new Size(2, '%');

        $declarationBlock = new DeclarationBlock();
        $rule = new Rule('margin');
        $rule->setValue(new CSSFunction('max', [$value1, $value2]));
        $declarationBlock->addRule($rule);
        $subject->setContents([$declarationBlock]);

        $result = $subject->getAllValues();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getAllValuesWithSearchInFunctionArgumentsReturnsValuesInFunctionArguments(): void
    {
        $subject = new ConcreteCSSBlockList();

        $value1 = new Size(10, 'px');
        $value2 = new Size(2, '%');

        $declarationBlock = new DeclarationBlock();
        $rule = new Rule('margin');
        $rule->setValue(new CSSFunction('max', [$value1, $value2]));
        $declarationBlock->addRule($rule);
        $subject->setContents([$declarationBlock]);

        $result = $subject->getAllValues(null, true);

        self::assertSame([$value1, $value2], $result);
    }
}
