<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Property;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Position\Positionable;
use Sabberworm\CSS\Property\AtRule;
use Sabberworm\CSS\Property\AtRuleStatement;
use Sabberworm\CSS\Renderable;

/**
 * @covers \Sabberworm\CSS\Property\AtRuleStatement
 */
final class AtRuleStatementTest extends TestCase
{
    /**
     * @var AtRuleStatement
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new AtRuleStatement('layer');
    }

    /**
     * @test
     */
    public function implementsAtRule(): void
    {
        self::assertInstanceOf(AtRule::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsRenderable(): void
    {
        self::assertInstanceOf(Renderable::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsCommentable(): void
    {
        self::assertInstanceOf(Commentable::class, $this->subject);
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
    public function atRuleNameReturnsTypeProvidedToConstructor(): void
    {
        $type = 'layer';

        $subject = new AtRuleStatement($type);

        self::assertSame($type, $subject->atRuleName());
    }

    /**
     * @test
     */
    public function atRuleArgsByDefaultReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->atRuleArgs());
    }

    /**
     * @test
     */
    public function atRuleArgsReturnsArgumentsProvidedToConstructor(): void
    {
        $arguments = 'reset';

        $subject = new AtRuleStatement('layer', $arguments);

        self::assertSame($arguments, $subject->atRuleArgs());
    }

    /**
     * @test
     */
    public function getLineNumberByDefaultReturnsNull(): void
    {
        self::assertNull($this->subject->getLineNumber());
    }

    /**
     * @test
     */
    public function getLineNumberReturnsLineNumberProvidedToConstructor(): void
    {
        $lineNumber = 42;
        $subject = new AtRuleStatement('layer', '', $lineNumber);

        self::assertSame($lineNumber, $subject->getLineNumber());
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesClassName(): void
    {
        $result = $this->subject->getArrayRepresentation();

        self::assertSame('AtRuleStatement', $result['class']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesAtRuleName(): void
    {
        $atRuleName = 'layer';
        $subject = new AtRuleStatement($atRuleName);

        $result = $subject->getArrayRepresentation();

        self::assertSame($atRuleName, $result['atRuleName']);
    }

    /**
     * @test
     */
    public function getArrayRepresentationIncludesArguments(): void
    {
        $arguments = 'reset';
        $subject = new AtRuleStatement('layer', $arguments);

        $result = $subject->getArrayRepresentation();

        self::assertSame($arguments, $result['arguments']);
    }

    /**
     * @test
     */
    public function renderWithArgumentsReturnsAtRuleFollowedBySemicolon(): void
    {
        $subject = new AtRuleStatement('layer', 'reset');

        self::assertSame('@layer reset;', $subject->render(OutputFormat::create()));
    }

    /**
     * @test
     */
    public function renderWithoutArgumentsReturnsAtRuleFollowedBySemicolon(): void
    {
        self::assertSame('@layer;', $this->subject->render(OutputFormat::create()));
    }

    /**
     * @return array<non-empty-string, array{0: OutputFormat}>
     */
    public static function provideOutputFormats(): array
    {
        return [
            'default' => [OutputFormat::create()],
            'compact' => [OutputFormat::createCompact()],
            'pretty' => [OutputFormat::createPretty()],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideOutputFormats
     */
    public function renderProducesTheSameStringForAllOutputFormats(OutputFormat $outputFormat): void
    {
        $subject = new AtRuleStatement('layer', 'reset');

        self::assertSame('@layer reset;', $subject->render($outputFormat));
    }
}
