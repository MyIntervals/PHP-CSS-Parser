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
     *
     * @return void
     */
    public function getAllValuesWhenNoContentSetReturnsEmptyArray()
    {
        $subject = new ConcreteCSSBlockList();

        self::assertSame([], $subject->getAllValues());
    }

    /**
     * @test
     *
     * @return void
     */
    public function getAllValuesReturnsOneValueDirectlySetAsContent()
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
     *
     * @return void
     */
    public function getAllValuesReturnsMultipleValuesDirectlySetAsContentInOneDeclarationBlock()
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
     *
     * @return void
     */
    public function getAllValuesReturnsMultipleValuesDirectlySetAsContentInMultipleDeclarationBlocks()
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
     *
     * @return void
     */
    public function getAllValuesReturnsValuesWithinAtRuleBlockList()
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
     *
     * @return void
     */
    public function getAllValuesWithElementProvidedReturnsOnlyValuesWithinThatElement()
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
     *
     * @return void
     */
    public function getAllValuesWithSearchStringProvidedReturnsOnlyValuesFromMatchingRules()
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
     *
     * @return void
     */
    public function getAllValuesWithSearchStringProvidedInNewMethodSignatureReturnsOnlyValuesFromMatchingRules()
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

        $result = $subject->getAllValues(null, 'font-');

        self::assertSame([$value1], $result);
    }

    /**
     * @test
     *
     * @return void
     */
    public function getAllValuesByDefaultDoesNotReturnValuesInFunctionArguments()
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
     *
     * @return void
     */
    public function getAllValuesWithSearchInFunctionArgumentsReturnsValuesInFunctionArguments()
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

    /**
     * @test
     *
     * @return void
     */
    public function getAllValuesWithSearchInFunctionArgumentsInNewMethodSignatureReturnsValuesInFunctionArguments()
    {
        $subject = new ConcreteCSSBlockList();

        $value1 = new Size(10, 'px');
        $value2 = new Size(2, '%');

        $declarationBlock = new DeclarationBlock();
        $rule = new Rule('margin');
        $rule->setValue(new CSSFunction('max', [$value1, $value2]));
        $declarationBlock->addRule($rule);
        $subject->setContents([$declarationBlock]);

        $result = $subject->getAllValues(null, null, true);

        self::assertSame([$value1, $value2], $result);
    }
}
