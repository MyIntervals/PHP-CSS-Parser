<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\Property\Charset;
use Sabberworm\CSS\Value\CSSString;

/**
 * @covers \Sabberworm\CSS\Property\Charset
 */
final class CharsetTest extends TestCase
{
    /**
     * @var Charset
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new Charset(new CSSString('UTF-8'));
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
        $result = $this->subject->getArrayRepresentation();

        self::assertSame('Charset', $result['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesCharset(): void
    {
        $charset = 'iso-8859-15';
        $subject = new Charset(new CSSString($charset));

        $result = $subject->getArrayRepresentation();

        self::assertSame(
            [
                'class' => 'CSSString',
                'contents' => $charset,
            ],
            $result['charset']
        );
    }
}
