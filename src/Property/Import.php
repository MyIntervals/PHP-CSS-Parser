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
     * @var int
     */
    protected $lineNumber;

    /**
     * @var array<array-key, Comment>
     */
    protected $comments;

    /**
     * @param string $mediaQuery
     * @param int $lineNumber
     */
    public function __construct(URL $location, $mediaQuery, $lineNumber = 0)
    {
        $this->location = $location;
        $this->mediaQuery = $mediaQuery;
        $this->lineNumber = $lineNumber;
        $this->comments = [];
    }

    /**
     * @return int
     */
    public function getLineNo()
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

    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $oOutputFormat): string
    {
        return $oOutputFormat->comments($this) . '@import ' . $this->location->render($oOutputFormat)
            . ($this->mediaQuery === null ? '' : ' ' . $this->mediaQuery) . ';';
    }

    public function atRuleName(): string
    {
        return 'import';
    }

    /**
     * @return array<int, URL|string>
     */
    public function atRuleArgs(): array
    {
        $aResult = [$this->location];
        if ($this->mediaQuery) {
            \array_push($aResult, $this->mediaQuery);
        }
        return $aResult;
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
    public function getComments()
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
