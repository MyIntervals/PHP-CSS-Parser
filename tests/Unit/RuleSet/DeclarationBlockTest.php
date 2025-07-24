<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\RuleSet;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Position\Positionable;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\Settings;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @covers \Sabberworm\CSS\RuleSet\DeclarationBlock
 */
final class DeclarationBlockTest extends TestCase
{
    /**
     * @var DeclarationBlock
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new DeclarationBlock();
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
    public function implementsPositionable(): void
    {
        self::assertInstanceOf(Positionable::class, $this->subject);
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
     * @return array<non-empty-string, array{0: int<1, max>}>
     */
    public function provideLineNumber(): array
    {
        return [
            'line 1' => [1],
            'line 42' => [42],
        ];
    }

    /**
     * @test
     *
     * @param int<1, max> $lineNumber
     *
     * @dataProvider provideLineNumber
     */
    public function getLineNumberReturnsLineNumberPassedToConstructor(int $lineNumber): void
    {
        $subject = new DeclarationBlock($lineNumber);

        $result = $subject->getLineNumber();

        self::assertSame($lineNumber, $result);
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideSelector(): array
    {
        return [
            'type' => ['body'],
            'class' => ['.teapot'],
            'type & class' => ['img.teapot'],
            'id' => ['#my-mug'],
            'type & id' => ['h2#my-mug'],
            'pseudo-class' => [':hover'],
            'type & pseudo-class' => ['a:hover'],
            '`not`' => [':not(#your-mug)'],
            '`not` with multiple arguments' => [':not(#your-mug, .their-mug)'],
            'pseudo-element' => ['::before'],
            'attribute with `"`' => ['[alt="{}()[]\\"\',"]'],
            'attribute with `\'`' => ['[alt=\'{}()[]"\\\',\']'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $selector
     *
     * @dataProvider provideSelector
     */
    public function parsesSingleSelector(string $selector): void
    {
        $subject = DeclarationBlock::parse(new ParserState($selector . ' {}', Settings::create()));

        self::assertInstanceOf(DeclarationBlock::class, $subject);
        self::assertSame([$selector], self::getSelectorsAsStrings($subject));
    }

    /**
     * @return DataProvider<non-empty-string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function provideTwoSelectors(): DataProvider
    {
        return DataProvider::cross(self::provideSelector(), self::provideSelector());
    }

    /**
     * @test
     *
     * @param non-empty-string $firstSelector
     * @param non-empty-string $secondSelector
     *
     * @dataProvider provideTwoSelectors
     */
    public function parsesTwoCommaSeparatedSelectors(string $firstSelector, string $secondSelector): void
    {
        $joinedSelectors = $firstSelector . ', ' . $secondSelector;

        $subject = DeclarationBlock::parse(new ParserState($joinedSelectors . ' {}', Settings::create()));

        self::assertInstanceOf(DeclarationBlock::class, $subject);
        self::assertSame([$firstSelector, $secondSelector], self::getSelectorsAsStrings($subject));
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideInvalidSelector(): array
    {
        // TODO: the `parse` method consumes the first character without inspection,
        // so the 'lone' test strings are prefixed with a space.
        return [
            'lone `(`' => [' ('],
            'lone `)`' => [' )'],
            'unclosed `(`' => [':not(#your-mug'],
            'extra `)`' => [':not(#your-mug))'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $selector
     *
     * @dataProvider provideInvalidSelector
     */
    public function parseSkipsBlockWithInvalidSelector(string $selector): void
    {
        static $nextCss = ' .next {}';
        $css = $selector . ' {}' . $nextCss;
        $parserState = new ParserState($css, Settings::create());

        $subject = DeclarationBlock::parse($parserState);

        self::assertNull($subject);
        self::assertTrue($parserState->comes($nextCss));
    }

    /**
     * @return array<string>
     */
    private static function getSelectorsAsStrings(DeclarationBlock $declarationBlock): array
    {
        return \array_map(
            static function (Selector $selectorObject): string {
                return $selectorObject->getSelector();
            },
            $declarationBlock->getSelectors()
        );
    }
}
