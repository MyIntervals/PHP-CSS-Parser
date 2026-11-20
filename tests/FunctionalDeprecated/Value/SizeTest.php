<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\FunctionalDeprecated\Value;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\Size;

/**
 * @covers \Sabberworm\CSS\Value\Size
 */
final class SizeTest extends TestCase
{
    /**
     * This exercises the deprecated `Size::getSize()` which has been replaced with `getNumber()` which is exercised in
     * the non-deprecated equivalent `TestCase`.
     *
     * @test
     */
    public function parsesNegativeDecimal(): void
    {
        $parserState = new ParserState('-1.5px', Settings::create());

        $result = Size::parse($parserState);

        self::assertSame(-1.5, $result->getSize());
        self::assertSame('px', $result->getUnit());
    }

    /**
     * This exercises the deprecated `Size::getSize()` which has been replaced with `getNumber()` which is exercised in
     * the non-deprecated equivalent `TestCase`.
     *
     * @test
     */
    public function parsesDecimalWithoutLeadingZero(): void
    {
        $parserState = new ParserState('.5em', Settings::create());

        $result = Size::parse($parserState);

        self::assertSame(0.5, $result->getSize());
        self::assertSame('em', $result->getUnit());
    }

    /**
     * This exercises the deprecated `Size::getSize()` which has been replaced with `getNumber()` which is exercised in
     * the non-deprecated equivalent `TestCase`.
     *
     * @test
     */
    public function parsesScientificNotationWithoutUnit(): void
    {
        $parserState = new ParserState('1e3', Settings::create());

        $result = Size::parse($parserState);

        self::assertSame(1000.0, $result->getSize());
        self::assertNull($result->getUnit());
    }

    /**
     * This exercises the deprecated `Size::getSize()` which has been replaced with `getNumber()` which is exercised in
     * the non-deprecated equivalent `TestCase`.
     *
     * @test
     */
    public function parsesScientificNotationWithNegativeExponentAndUnit(): void
    {
        $parserState = new ParserState('2E-2s', Settings::create());

        $result = Size::parse($parserState);

        self::assertSame(0.02, $result->getSize());
        self::assertSame('s', $result->getUnit());
    }

    /**
     * This exercises the deprecated `Size::getSize()` which has been replaced with `getNumber()` which is exercised in
     * the non-deprecated equivalent `TestCase`.
     *
     * @test
     */
    public function parsesUnitStartingWithE(): void
    {
        $parserState = new ParserState('1em', Settings::create());

        $result = Size::parse($parserState);

        self::assertSame(1.0, $result->getSize());
        self::assertSame('em', $result->getUnit());
    }

    /**
     * This exercises the deprecated `Size::getSize()` which has been replaced with `getNumber()` which is exercised in
     * the non-deprecated equivalent `TestCase`.
     *
     * @test
     */
    public function parsesUppercaseUnitToRecommendedCasing(): void
    {
        $parserState = new ParserState('3EM', Settings::create());

        $result = Size::parse($parserState);

        self::assertSame(3.0, $result->getSize());
        self::assertSame('em', $result->getUnit());
    }
}
