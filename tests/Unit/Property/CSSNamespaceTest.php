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
}
