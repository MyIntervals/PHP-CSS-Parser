<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property;

use Sabberworm\CSS\Comment\CommentContainer;
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
    use CommentContainer;

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
     * @param int<0, max> $lineNumber
     */
    public function __construct(CSSString $charset, int $lineNumber = 0)
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

    public function getCharset(): string
    {
        return $this->charset->getString();
    }

    /**
     * @return non-empty-string
     */
    public function render(OutputFormat $outputFormat): string
    {
        return "{$outputFormat->getFormatter()->comments($this)}@charset {$this->charset->render($outputFormat)};";
    }

    /**
     * @return non-empty-string
     */
    public function atRuleName(): string
    {
        return 'charset';
    }

    public function atRuleArgs(): CSSString
    {
        return $this->charset;
    }
}
