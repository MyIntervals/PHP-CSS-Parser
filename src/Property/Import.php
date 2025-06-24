<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property;

use Sabberworm\CSS\Comment\CommentContainer;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Position\Position;
use Sabberworm\CSS\Position\Positionable;
use Sabberworm\CSS\Value\URL;

/**
 * Class representing an `@import` rule.
 */
class Import implements AtRule, Positionable
{
    use CommentContainer;
    use Position;

    /**
     * @var URL
     */
    private $location;

    /**
     * @var string|null
     */
    private $mediaQuery;

    /**
     * @param int<1, max>|null $lineNumber
     */
    public function __construct(URL $location, ?string $mediaQuery, ?int $lineNumber = null)
    {
        $this->location = $location;
        $this->mediaQuery = $mediaQuery;
        $this->setPosition($lineNumber);
    }

    public function setLocation(URL $location): void
    {
        $this->location = $location;
    }

    public function getLocation(): URL
    {
        return $this->location;
    }

    /**
     * @return non-empty-string
     */
    public function render(OutputFormat $outputFormat): string
    {
        return $outputFormat->getFormatter()->comments($this) . '@import ' . $this->location->render($outputFormat)
            . ($this->mediaQuery === null ? '' : ' ' . $this->mediaQuery) . ';';
    }

    /**
     * @return non-empty-string
     */
    public function atRuleName(): string
    {
        return 'import';
    }

    /**
     * @return array{0: URL, 1?: non-empty-string}
     */
    public function atRuleArgs(): array
    {
        $result = [$this->location];
        if (\is_string($this->mediaQuery) && $this->mediaQuery !== '') {
            $result[] = $this->mediaQuery;
        }

        return $result;
    }

    public function getMediaQuery(): ?string
    {
        return $this->mediaQuery;
    }
}
