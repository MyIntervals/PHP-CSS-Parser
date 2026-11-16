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
        $uri = 'https://example.com/';
        $subject = new Import(new URL(new CSSString($uri)), null);

        self::assertSame($uri, $subject->getLocation()->getURL()->getString());
    }

    /**
     * @test
     */
    public function setLocationSetsLocation(): void
    {
        $uri = 'https://example.com/other.css';

        $this->subject->setLocation(new URL(new CSSString($uri)));

        self::assertSame($uri, $this->subject->getLocation()->getURL()->getString());
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
        $uri = 'https://example.com/';
        $subject = new Import(new URL(new CSSString($uri)), null);

        $result = $subject->atRuleArgs();

        self::assertCount(1, $result);
        self::assertInstanceOf(URL::class, $result[0]);
        self::assertSame($uri, $result[0]->getURL()->getString());
    }

    /**
     * @test
     */
    public function atRuleArgsWithEmptyMediaQueryReturnsOnlyLocation(): void
    {
        $uri = 'https://example.com/';
        $subject = new Import(new URL(new CSSString($uri)), '');

        $result = $subject->atRuleArgs();

        self::assertCount(1, $result);
        self::assertInstanceOf(URL::class, $result[0]);
        self::assertSame($uri, $result[0]->getURL()->getString());
    }

    /**
     * @test
     */
    public function atRuleArgsWithMediaQueryReturnsLocationFollowedByMediaQuery(): void
    {
        $uri = 'https://example.com/';
        $mediaQuery = 'print';
        $subject = new Import(new URL(new CSSString($uri)), $mediaQuery);

        $result = $subject->atRuleArgs();

        self::assertCount(2, $result);
        self::assertInstanceOf(URL::class, $result[0]);
        self::assertSame($uri, $result[0]->getURL()->getString());
        self::assertSame($mediaQuery, $result[1]);
    }
}
