<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\Property\CSSNamespace;
use Sabberworm\CSS\Value\CSSString;
use Sabberworm\CSS\Value\URL;

/**
 * @covers \Sabberworm\CSS\Property\CSSNamespace
 */
final class CSSNamespaceTest extends TestCase
{
    /**
     * @var CSSNamespace
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new CSSNamespace(new CSSString('http://www.w3.org/2000/svg'));
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
        $subject = new CSSNamespace(new CSSString('http://www.w3.org/2000/svg'));

        $result = $subject->getArrayRepresentation();

        self::assertSame('CSSNamespace', $result['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesUrlProvidedAsCssString(): void
    {
        $uri = 'http://www.w3.org/2000/svg';
        $subject = new CSSNamespace(new CSSString($uri));

        $result = $subject->getArrayRepresentation();

        self::assertSame(
            [
                'class' => 'CSSString',
                'contents' => $uri,
            ],
            $result['uri']
        );
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesUrlProvidedAsUrl(): void
    {
        $uri = 'http://www.w3.org/2000/svg';
        $subject = new CSSNamespace(new URL(new CSSString($uri)));

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
    public function getArrayRepresentationIncludesPrefixForNullPrefix(): void
    {
        $subject = new CSSNamespace(new CSSString('http://www.w3.org/2000/svg'), null);

        $result = $subject->getArrayRepresentation();

        self::assertNull($result['prefix']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesPrefixForStringPrefix(): void
    {
        $prefix = 'hello';

        $subject = new CSSNamespace(new CSSString('http://www.w3.org/2000/svg'), $prefix);

        $result = $subject->getArrayRepresentation();

        self::assertSame($prefix, $result['prefix']);
    }

    /**
     * @test
     */
    public function getUrlReturnsUrlProvidedToConstructor(): void
    {
        $url = new CSSString('http://www.w3.org/2000/svg');
        $subject = new CSSNamespace($url);

        self::assertSame($url, $subject->getUrl());
    }

    /**
     * @test
     */
    public function setUrlSetsUrl(): void
    {
        $url = new URL(new CSSString('http://www.w3.org/1999/xhtml'));

        $this->subject->setUrl($url);

        self::assertSame($url, $this->subject->getUrl());
    }

    /**
     * @test
     */
    public function getPrefixReturnsPrefixProvidedToConstructor(): void
    {
        $prefix = 'svg';
        $subject = new CSSNamespace(new CSSString('http://www.w3.org/2000/svg'), $prefix);

        self::assertSame($prefix, $subject->getPrefix());
    }

    /**
     * @test
     */
    public function setPrefixSetsPrefix(): void
    {
        $prefix = 'svg';

        $this->subject->setPrefix($prefix);

        self::assertSame($prefix, $this->subject->getPrefix());
    }

    /**
     * @test
     */
    public function atRuleNameReturnsNamespace(): void
    {
        self::assertSame('namespace', $this->subject->atRuleName());
    }

    /**
     * @test
     */
    public function atRuleArgsWithoutPrefixReturnsOnlyUrl(): void
    {
        $url = new CSSString('http://www.w3.org/2000/svg');
        $subject = new CSSNamespace($url);

        self::assertSame([$url], $subject->atRuleArgs());
    }

    /**
     * @test
     */
    public function atRuleArgsWithEmptyPrefixReturnsOnlyUrl(): void
    {
        $url = new CSSString('http://www.w3.org/2000/svg');
        $subject = new CSSNamespace($url, '');

        self::assertSame([$url], $subject->atRuleArgs());
    }

    /**
     * @test
     */
    public function atRuleArgsWithPrefixReturnsPrefixFollowedByUrl(): void
    {
        $url = new CSSString('http://www.w3.org/2000/svg');
        $prefix = 'svg';
        $subject = new CSSNamespace($url, $prefix);

        self::assertSame([$prefix, $url], $subject->atRuleArgs());
    }
}
