<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property\Selector;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Property\Selector\Component;
use Sabberworm\CSS\Property\Selector\CompoundSelector;
use Sabberworm\CSS\Renderable;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @covers \Sabberworm\CSS\Property\Selector\CompoundSelector
 */
final class CompoundSelectorTest extends TestCase
{
    /**
     * @test
     */
    public function implementsRenderable(): void
    {
        self::assertInstanceOf(Renderable::class, new CompoundSelector('a:hover'));
    }

    /**
     * @test
     */
    public function implementsSelectorComponent(): void
    {
        self::assertInstanceOf(Component::class, new CompoundSelector('a:hover'));
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: int<0, max>}>
     */
    public static function provideCompoundSelectorAndSpecificity(): array
    {
        return [
            'type' => ['a', 1],
            'class' => ['.highlighted', 10],
            'type with class' => ['li.green', 11],
            'pseudo-class' => [':hover', 10],
            'type with pseudo-class' => ['a:hover', 11],
            'class with pseudo-class' => ['.help:hover', 20],
            'ID' => ['#file', 100],
            'type with ID' => ['h2#my-mug', 101],
            'pseudo-element' => ['::before', 1],
            'type with pseudo-element' => ['li::before', 2],
            '`not`' => [':not(#your-mug)', 100],
            // TODO, broken: The specificity should be the highest of the `:not` arguments, not the sum.
            '`not` with multiple arguments' => [':not(#your-mug, .their-mug)', 110],
            'attribute with `"`' => ['[alt="{}()[]\\"\',"]', 10],
            'attribute with `\'`' => ['[alt=\'{}()[]"\\\',\']', 10],
            // TODO, broken: specificity should be 11, but the calculator doesn't realize the `#` is in a string.
            'attribute with `^=`' => ['a[href^="#"]', 111],
            'attribute with `*=`' => ['a[href*="example"]', 11],
            // TODO, broken: specificity should be 11, but the calculator doesn't realize the `.` is in a string.
            'attribute with `$=`' => ['a[href$=".org"]', 21],
            'attribute with `~=`' => ['span[title~="bonjour"]', 11],
            'attribute with `|=`' => ['[lang|="en"]', 10],
            // TODO, broken: specificity should be 11, but the calculator doesn't realize the `i` is in an attribute.
            'attribute with case insensitive modifier' => ['a[href*="insensitive" i]', 12],
            // TODO, broken: specificity should be 21, but the calculator doesn't realize the `.` is in a string.
            'multiple attributes' => ['a[href^="https://"][href$=".org"]', 31],
            // TODO, broken: specificity should be 11, but the calculator is treating the `n` as a type selector.
            'nth-last-child' => ['li:nth-last-child(2n+3)', 12],
            // TODO, maybe broken: specificity should probably be 2 (1 for `p` and 1 for `span`).
            '`not` with descendent combinator' => [':not(p span)', 1],
            // TODO, maybe broken: specificity should probably be 2 (1 for `p` and 1 for `span`).
            '`not` with child combinator' => [':not(p > span)', 1],
            // TODO, maybe broken: specificity should probably be 2 (1 for `h1` and 1 for `p`).
            '`not` with next-sibling combinator' => [':not(h1 + p)', 1],
            // TODO, maybe broken: specificity should probably be 2 (1 for `h1` and 1 for `p`).
            '`not` with subsequent-sibling combinator' => [':not(h1 ~ p)', 1],
        ];
    }

    /**
     * @return array<non-empty-string, array{0: string}>
     */
    public static function provideInvalidCompoundSelector(): array
    {
        return [
            'empty string' => [''],
            'space' => [' '],
            'tab' => ["\t"],
            'line feed' => ["\n"],
            'carriage return' => ["\r"],
            'percent sign' => ['%'],
            // This is currently broken.
            // 'hash only' => ['#'],
            // This is currently broken.
            // 'dot only' => ['.'],
            'slash' => ['/'],
            'less-than sign' => ['<'],
            'with space before' => [' a:hover'],
        ];
    }

    /**
     * This is valid in a parsing context, but not when passed to `setValue()` or the constructor.
     *
     * @return array<non-empty-string, array{0: string}>
     */
    public static function provideInvalidCompoundSelectorForSetValue(): array
    {
        return [
            'with space after' => ['a:hover '],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $value
     *
     * @dataProvider provideCompoundSelectorAndSpecificity
     */
    public function constructsWithValueProvided(string $value): void
    {
        $subject = new CompoundSelector($value);

        self::assertSame($value, $subject->getArrayRepresentation()['value']);
    }

    /**
     * @test
     *
     * @param non-empty-string $value
     *
     * @dataProvider provideInvalidCompoundSelector
     * @dataProvider provideInvalidCompoundSelectorForSetValue
     */
    public function constructorThrowsExceptionWithInvalidValue(string $value): void
    {
        $this->expectException(\UnexpectedValueException::class);

        new CompoundSelector($value);
    }

    /**
     * @test
     */
    public function constructorThrowsForValueThatIsNotValidUtf8(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The selector is not valid UTF-8.');
        $this->expectExceptionCode(1787283476);

        new CompoundSelector("a\xFF");
    }

    /**
     * @test
     *
     * @param non-empty-string $value
     *
     * @dataProvider provideCompoundSelectorAndSpecificity
     */
    public function setValueSetsValueProvided(string $value): void
    {
        $subject = new CompoundSelector('p.intro');

        $subject->setValue($value);

        self::assertSame($value, $subject->getArrayRepresentation()['value']);
    }

    /**
     * @test
     *
     * @param non-empty-string $value
     *
     * @dataProvider provideInvalidCompoundSelector
     * @dataProvider provideInvalidCompoundSelectorForSetValue
     */
    public function setValueThrowsExceptionWithInvalidValue(string $value): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $subject = new CompoundSelector('p.intro');

        $subject->setValue($value);
    }

    /**
     * @test
     *
     * @param non-empty-string $value
     *
     * @dataProvider provideCompoundSelectorAndSpecificity
     */
    public function getValueReturnsValueProvidedToConstructor(string $value): void
    {
        $subject = new CompoundSelector($value);

        $result = $subject->getValue();

        self::assertSame($value, $result);
    }

    /**
     * @test
     *
     * @param non-empty-string $value
     *
     * @dataProvider provideCompoundSelectorAndSpecificity
     */
    public function getValueReturnsValueProvidedToSetValue(string $value): void
    {
        $subject = new CompoundSelector('p.intro');
        $subject->setValue($value);

        $result = $subject->getValue();

        self::assertSame($value, $result);
    }

    /**
     * @test
     *
     * @param non-empty-string $compoundSelector
     * @param int<0, max> $expectedSpecificity
     *
     * @dataProvider provideCompoundSelectorAndSpecificity
     */
    public function getSpecificityByDefaultReturnsSpecificityOfCompoundSelectorProvidedToConstructor(
        string $compoundSelector,
        int $expectedSpecificity
    ): void {
        $subject = new CompoundSelector($compoundSelector);

        self::assertSame($expectedSpecificity, $subject->getSpecificity());
    }

    /**
     * @test
     *
     * @param non-empty-string $compoundSelector
     * @param int<0, max> $expectedSpecificity
     *
     * @dataProvider provideCompoundSelectorAndSpecificity
     */
    public function getSpecificityReturnsSpecificityOfCompoundSelectorLastProvidedViaSetValue(
        string $compoundSelector,
        int $expectedSpecificity
    ): void {
        $subject = new CompoundSelector('p.intro');

        $subject->setValue($compoundSelector);

        self::assertSame($expectedSpecificity, $subject->getSpecificity());
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $subject = new CompoundSelector('a:hover');

        $result = $subject->getArrayRepresentation();

        self::assertSame('CompoundSelector', $result['class']);
    }

    /**
     * @test
     *
     * @param non-empty-string $value
     *
     * @dataProvider provideCompoundSelectorAndSpecificity
     */
    public function getArrayRepresentationIncludesValue(string $value): void
    {
        $subject = new CompoundSelector($value);

        $result = $subject->getArrayRepresentation();

        self::assertSame($value, $result['value']);
    }
}
