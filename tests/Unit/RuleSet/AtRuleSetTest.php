<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\Property\Declaration;
use Sabberworm\CSS\RuleSet\AtRuleSet;

/**
 * @covers \Sabberworm\CSS\RuleSet\AtRuleSet
 */
final class AtRuleSetTest extends TestCase
{
    /**
     * @var AtRuleSet
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new AtRuleSet('supports');
    }

    /**
     * @test
     */
    public function implementsCSSListItem(): void
    {
        self::assertInstanceOf(CSSListItem::class, $this->subject);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new AtRuleSet('supports');

        $result = $subject->getArrayRepresentation();

        self::assertSame('AtRuleSet', $result['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesDeclarations(): void
    {
        $subject = new AtRuleSet('supports');
        $subject->addDeclaration(new Declaration('display'));
        $subject->addDeclaration(new Declaration('transform-origin'));

        $result = $subject->getArrayRepresentation();

        self::assertSame(
            [
                'display' => [
                    [
                        'class' => 'Declaration',
                        'propertyName' => 'display',
                        'propertyValue' => null,
                        'important' => false,
                    ],
                ],
                'transform-origin' => [
                    [
                        'class' => 'Declaration',
                        'propertyName' => 'transform-origin',
                        'propertyValue' => null,
                        'important' => false,
                    ],
                ],
            ],
            $result['declarations']
        );
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesAtRuleName(): void
    {
        $atRuleName = 'supports';
        $subject = new AtRuleSet($atRuleName);

        $result = $subject->getArrayRepresentation();

        self::assertSame($atRuleName, $result['atRuleName']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesArguments(): void
    {
        $arguments = 'foo';
        $subject = new AtRuleSet('supports', $arguments);

        $result = $subject->getArrayRepresentation();

        self::assertSame($arguments, $result['arguments']);
    }
}
