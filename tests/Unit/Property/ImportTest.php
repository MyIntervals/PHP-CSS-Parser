<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\Property\Import;
use Sabberworm\CSS\Value\CSSString;
use Sabberworm\CSS\Value\URL;

/**
 * @covers \Sabberworm\CSS\Property\Import
 */
final class ImportTest extends TestCase
{
    /**
     * @var Import
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new Import(new URL(new CSSString('https://example.org/')), null);
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
        $subject = new Import(new URL(new CSSString('https://example.org/')), null);

        $result = $subject->getArrayRepresentation();

        self::assertSame('Import', $result['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesUri(): void
    {
        $uri = 'https://example.com';
        $url = new URL(new CSSString($uri));

        $subject = new Import($url, null);

        $result = $subject->getArrayRepresentation();

        self::assertSame(
            [
                'class' => 'URL',
                'uri' => [
                    'class' => 'CSSString',
                    'contents' => $uri,
                ],
            ],
            $result['uri']
        );
    }

    /**
     * @test
     */
    public function getLocationReturnsLocationProvidedToConstructor(): void
    {
        $location = new URL(new CSSString('https://example.com/'));
        $subject = new Import($location, null);

        self::assertSame($location, $subject->getLocation());
    }

    /**
     * @test
     */
    public function setLocationSetsLocation(): void
    {
        $location = new URL(new CSSString('https://example.com/other.css'));

        $this->subject->setLocation($location);

        self::assertSame($location, $this->subject->getLocation());
    }

    /**
     * @test
     */
    public function getMediaQueryReturnsMediaQueryProvidedToConstructor(): void
    {
        $mediaQuery = 'print';
        $subject = new Import(new URL(new CSSString('https://example.com/')), $mediaQuery);

        self::assertSame($mediaQuery, $subject->getMediaQuery());
    }

    /**
     * @test
     */
    public function atRuleNameReturnsImport(): void
    {
        self::assertSame('import', $this->subject->atRuleName());
    }

    /**
     * @test
     */
    public function atRuleArgsWithoutMediaQueryReturnsOnlyLocation(): void
    {
        $location = new URL(new CSSString('https://example.com/'));
        $subject = new Import($location, null);

        self::assertSame([$location], $subject->atRuleArgs());
    }

    /**
     * @test
     */
    public function atRuleArgsWithEmptyMediaQueryReturnsOnlyLocation(): void
    {
        $location = new URL(new CSSString('https://example.com/'));
        $subject = new Import($location, '');

        self::assertSame([$location], $subject->atRuleArgs());
    }

    /**
     * @test
     */
    public function atRuleArgsWithMediaQueryReturnsLocationFollowedByMediaQuery(): void
    {
        $location = new URL(new CSSString('https://example.com/'));
        $mediaQuery = 'print';
        $subject = new Import($location, $mediaQuery);

        self::assertSame([$location, $mediaQuery], $subject->atRuleArgs());
    }
}
