<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property;

use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Value\URL;

/**
 * Class representing an `@import` rule.
 */
class Import implements AtRule
{
    /**
     * @var URL
     */
    private $location;

    /**
     * @var string
     */
    private $mediaQuery;

    /**
     * @var int<0, max>
     *
     * @internal since 8.8.0
     */
    protected $lineNumber;

    /**
     * @var array<array-key, Comment>
     *
     * @internal since 8.8.0
     */
    protected $comments = [];

    /**
     * @param string $mediaQuery
     * @param int<0, max> $lineNumber
     */
    public function __construct(URL $location, $mediaQuery, int $lineNumber = 0)
    {
        $this->location = $location;
        $this->mediaQuery = $mediaQuery;
        $this->lineNumber = $lineNumber;
    }

    /**
     * @return int<0, max>
     */
    public function getLineNo(): int
    {
        return $this->lineNumber;
    }

    /**
     * @param URL $location
     */
    public function setLocation($location): void
    {
        $this->location = $location;
    }

    /**
     * @return URL
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @deprecated in V8.8.0, will be removed in V9.0.0. Use `render` instead.
     */
    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

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
     * @return array<int, URL|string>
     */
    public function atRuleArgs(): array
    {
        $result = [$this->location];
        if ($this->mediaQuery) {
            \array_push($result, $this->mediaQuery);
        }
        return $result;
    }

    /**
     * @param array<array-key, Comment> $comments
     */
    public function addComments(array $comments): void
    {
        $this->comments = \array_merge($this->comments, $comments);
    }

    /**
     * @return array<array-key, Comment>
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    /**
     * @param array<array-key, Comment> $comments
     */
    public function setComments(array $comments): void
    {
        $this->comments = $comments;
    }

    /**
     * @return string
     */
    public function getMediaQuery()
    {
        return $this->mediaQuery;
    }
}
