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
     * @var string|null
     */
    private $mediaQuery;

    /**
     * @var int<0, max>
     *
     * @internal since 8.8.0
     */
    protected $lineNumber;

    /**
     * @var list<Comment>
     *
     * @internal since 8.8.0
     */
    protected $comments = [];

    /**
     * @param int<0, max> $lineNumber
     */
    public function __construct(URL $location, ?string $mediaQuery, int $lineNumber = 0)
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

    /**
     * @param list<Comment> $comments
     */
    public function addComments(array $comments): void
    {
        $this->comments = \array_merge($this->comments, $comments);
    }

    /**
     * @return list<Comment>
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    /**
     * @param list<Comment> $comments
     */
    public function setComments(array $comments): void
    {
        $this->comments = $comments;
    }

    public function getMediaQuery(): ?string
    {
        return $this->mediaQuery;
    }
}
