<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Settings;

/**
 * @covers \Sabberworm\CSS\Settings
 */
final class SettingsTest extends TestCase
{
    /**
     * @var Settings
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = Settings::create();
    }

    /**
     * @test
     */
    public function createReturnsInstance(): void
    {
        $settings = Settings::create();

        self::assertInstanceOf(Settings::class, $settings);
    }

    /**
     * @test
     */
    public function createReturnsANewInstanceForEachCall(): void
    {
        $settings1 = Settings::create();
        $settings2 = Settings::create();

        self::assertNotSame($settings1, $settings2);
    }

    /**
     * @test
     */
    public function multibyteSupportByDefaultStateOfMbStringExtension(): void
    {
        self::assertSame(\extension_loaded('mbstring'), $this->subject->hasMultibyteSupport());
    }

    /**
     * @test
     */
    public function withMultibyteSupportProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->withMultibyteSupport());
    }

    /**
     * @return array<string, array{0: bool}>
     */
    public static function booleanDataProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false],
        ];
    }

    /**
     * @test
     * @dataProvider booleanDataProvider
     */
    public function withMultibyteSupportSetsMultibyteSupport(bool $value): void
    {
        $this->subject->withMultibyteSupport($value);

        self::assertSame($value, $this->subject->hasMultibyteSupport());
    }

    /**
     * @test
     */
    public function defaultCharsetByDefaultIsUtf8(): void
    {
        self::assertSame('utf-8', $this->subject->getDefaultCharset());
    }

    /**
     * @test
     */
    public function withDefaultCharsetProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->withDefaultCharset('UTF-8'));
    }

    /**
     * @test
     */
    public function withDefaultCharsetSetsDefaultCharset(): void
    {
        $charset = 'ISO-8859-1';
        $this->subject->withDefaultCharset($charset);

        self::assertSame($charset, $this->subject->getDefaultCharset());
    }

    /**
     * @test
     */
    public function lenientParsingByDefaultIsTrue(): void
    {
        self::assertTrue($this->subject->usesLenientParsing());
    }

    /**
     * @test
     */
    public function withLenientParsingProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->withLenientParsing());
    }

    /**
     * @test
     * @dataProvider booleanDataProvider
     */
    public function withLenientParsingSetsLenientParsing(bool $value): void
    {
        $this->subject->withLenientParsing($value);

        self::assertSame($value, $this->subject->usesLenientParsing());
    }

    /**
     * @test
     */
    public function beStrictProvidesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->beStrict());
    }

    /**
     * @test
     */
    public function beStrictSetsLenientParsingToFalse(): void
    {
        $this->subject->beStrict();

        self::assertFalse($this->subject->usesLenientParsing());
    }
}
