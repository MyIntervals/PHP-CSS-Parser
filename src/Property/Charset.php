<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property;

use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Value\CSSString;

/**
 * Class representing an `@charset` rule.
 *
 * The following restrictions apply:
 * - May not be found in any CSSList other than the Document.
 * - May only appear at the very top of a Document’s contents.
 * - Must not appear more than once.
 */
class Charset implements AtRule
{
    /**
     * @var CSSString
     */
    private $charset;

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
     * @param int<0, max> $lineNumber
     */
    public function __construct(CSSString $charset, $lineNumber = 0)
    {
        $this->charset = $charset;
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
     * @param string|CSSString $charset
     */
    public function setCharset($charset): void
    {
        $charset = $charset instanceof CSSString ? $charset : new CSSString($charset);
        $this->charset = $charset;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset->getString();
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
        return "{$outputFormat->comments($this)}@charset {$this->charset->render($outputFormat)};";
    }

    public function atRuleName(): string
    {
        return 'charset';
    }

    /**
     * @return string
     */
    public function atRuleArgs()
    {
        return $this->charset;
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
