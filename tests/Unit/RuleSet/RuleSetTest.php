<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\Property\Declaration;
use Sabberworm\CSS\RuleSet\RuleSet;

/**
 * @covers \Sabberworm\CSS\RuleSet\RuleSet
 */
final class RuleSetTest extends TestCase
{
    use DeclarationListTests;

    /**
     * @var RuleSet
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new RuleSet();
    }

    /**
     * @test
     */
    public function implementsCSSElement(): void
    {
        self::assertInstanceOf(CSSElement::class, $this->subject);
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
    public function getLineNumberByDefaultReturnsNull(): void
    {
        $result = $this->subject->getLineNumber();

        self::assertNull($result);
    }

    /**
     * @return array<non-empty-string, array{0: int<1, max>|null}>
     */
    public function provideLineNumber(): array
    {
        return [
            'null' => [null],
            'line 1' => [1],
            'line 42' => [42],
        ];
    }

    /**
     * @test
     *
     * @param int<1, max>|null $lineNumber
     *
     * @dataProvider provideLineNumber
     */
    public function getLineNumberReturnsLineNumberPassedToConstructor(?int $lineNumber): void
    {
        $subject = new RuleSet($lineNumber);

        $result = $subject->getLineNumber();

        self::assertSame($lineNumber, $result);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new RuleSet();

        $result = $subject->getArrayRepresentation();

        self::assertSame('RuleSet', $result['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesDeclarations(): void
    {
        $subject = new RuleSet();
        $subject->addDeclaration(new Declaration('line-height'));
        $subject->addDeclaration(new Declaration('line-height'));
        $subject->addDeclaration(new Declaration('color'));

        $result = $subject->getArrayRepresentation();

        self::assertSame(
            [
                'line-height' => [
                    [
                        'class' => 'Declaration',
                        'propertyName' => 'line-height',
                        'propertyValue' => null,
                        'important' => false,
                    ],
                    [
                        'class' => 'Declaration',
                        'propertyName' => 'line-height',
                        'propertyValue' => null,
                        'important' => false,
                    ],
                ],
                'color' => [
                    [
                        'class' => 'Declaration',
                        'propertyName' => 'color',
                        'propertyValue' => null,
                        'important' => false,
                    ],
                ],
            ],
            $result['declarations']
        );
    }
}
