<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property;

use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Value\CSSString;
use Sabberworm\CSS\Value\URL;

/**
 * `CSSNamespace` represents an `@namespace` rule.
 */
class CSSNamespace implements AtRule
{
    /**
     * @var CSSString|URL
     */
    private $url;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var int<0, max> $lineNumber
     */
    private $lineNumber;

    /**
     * @var list<Comment>
     *
     * @internal since 8.8.0
     */
    protected $comments = [];

    /**
     * @param CSSString|URL $url
     * @param int<0, max> $lineNumber
     */
    public function __construct($url, ?string $prefix = null, int $lineNumber = 0)
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

    /**
     * @deprecated in V8.8.0, will be removed in V9.0.0. Use `render` instead.
     */
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
     * @return CSSString|URL
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
     * @param CSSString|URL $url
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
     * @return non-empty-string
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
}
