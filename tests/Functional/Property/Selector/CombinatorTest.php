<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\Property\Selector;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Property\Selector\Combinator;
use Sabberworm\CSS\Settings;

/**
 * @covers \Sabberworm\CSS\Property\Selector\Combinator
 *
 * @phpstan-import-type ValidCombinatorValue from Combinator
 */
final class CombinatorTest extends TestCase
{
    /**
     * @return array<non-empty-string, array{0: ValidCombinatorValue, 1: non-empty-string}>
     */
    public static function provideValidValueAndDefaultRendering(): array
    {
        return [
            'descendent' => [' ', ' '],
            'child' => ['>', ' > '],
            'next sibling' => ['+', ' + '],
            'subsequent sibling' => ['~', ' ~ '],
        ];
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideInvalidValue(): array
    {
        return [
            'other symbol' => ['@'],
            'uppercase letter' => ['Z'],
            'lowercase letter' => ['j'],
            'number' => ['1'],
            'sequence' => ['abc?123'],
        ];
    }

    /**
     * @test
     *
     * @param ValidCombinatorValue $combinator
     *
     * @dataProvider provideValidValueAndDefaultRendering
     */
    public function parsesValidCombinator(string $combinator): void
    {
        $result = Combinator::parse(new ParserState($combinator, Settings::create()));

        self::assertSame($combinator, $result->getArrayRepresentation()['value']);
    }

    /**
     * @test
     *
     * @param non-empty-string $selectorComponent
     *
     * @dataProvider provideInvalidValue
     */
    public function parseThrowsExceptionWithInvalidCombinator(string $selectorComponent): void
    {
        $this->expectException(UnexpectedTokenException::class);

        Combinator::parse(new ParserState($selectorComponent, Settings::create()));
    }

    /**
     * @test
     *
     * @param ValidCombinatorValue $combinator
     *
     * @dataProvider provideValidValueAndDefaultRendering
     */
    public function parsesCombinatorWithCommentBefore(string $combinator): void
    {
        $result = Combinator::parse(new ParserState('/*comment*/' . $combinator, Settings::create()));

        self::assertSame($combinator, $result->getArrayRepresentation()['value']);
    }

    /**
     * @test
     *
     * @param ValidCombinatorValue $combinator
     *
     * @dataProvider provideValidValueAndDefaultRendering
     */
    public function parsesCombinatorWithCommentAfter(string $combinator): void
    {
        $result = Combinator::parse(new ParserState($combinator . '/*comment*/', Settings::create()));

        self::assertSame($combinator, $result->getArrayRepresentation()['value']);
    }

    /**
     * @test
     *
     * @param ValidCombinatorValue $combinator
     *
     * @dataProvider provideValidValueAndDefaultRendering
     */
    public function parseExtractsCommentBefore(string $combinator): void
    {
        $result = [];
        Combinator::parse(new ParserState('/*comment*/' . $combinator, Settings::create()), $result);

        self::assertSame('comment', $result[0]->getArrayRepresentation()['contents']);
    }

    /**
     * @test
     *
     * @param ValidCombinatorValue $combinator
     *
     * @dataProvider provideValidValueAndDefaultRendering
     */
    public function parseExtractsCommentAfter(string $combinator): void
    {
        $result = [];
        Combinator::parse(new ParserState($combinator . '/*comment*/', Settings::create()), $result);

        self::assertSame('comment', $result[0]->getArrayRepresentation()['contents']);
    }
}
