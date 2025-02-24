<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property;

use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\OutputFormat;

/**
 * `CSSNamespace` represents an `@namespace` rule.
 */
class CSSNamespace implements AtRule
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var int
     */
    private $lineNumber;

    /**
     * @var array<array-key, Comment>
     *
     * @internal since 8.8.0
     */
    protected $comments = [];

    /**
     * @param string $url
     * @param string|null $prefix
     * @param int<0, max> $lineNumber
     */
    public function __construct($url, $prefix = null, $lineNumber = 0)
    {
        $this->url = $url;
        $this->prefix = $prefix;
        $this->lineNumber = $lineNumber;
    }

    /**
     * @return int<0, max>
     */
    public function getLineNo(): int
    {
        return $this->lineNumber;
    }

    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $outputFormat): string
    {
        return '@namespace ' . ($this->prefix === null ? '' : $this->prefix . ' ')
            . $this->url->render($outputFormat) . ';';
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $url
     */
    public function setUrl($url): void
    {
        $this->url = $url;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function atRuleName(): string
    {
        return 'namespace';
    }

    /**
     * @return array<int, string>
     */
    public function atRuleArgs(): array
    {
        $result = [$this->url];
        if ($this->prefix) {
            \array_unshift($result, $this->prefix);
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
}
