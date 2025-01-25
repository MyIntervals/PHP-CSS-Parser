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
    private $oCharset;

    /**
     * @var int
     */
    protected $iLineNo;

    /**
     * @var array<array-key, Comment>
     */
    protected $comments;

    /**
     * @param CSSString $oCharset
     * @param int $iLineNo
     */
    public function __construct(CSSString $oCharset, $iLineNo = 0)
    {
        $this->oCharset = $oCharset;
        $this->iLineNo = $iLineNo;
        $this->comments = [];
    }

    /**
     * @return int
     */
    public function getLineNo()
    {
        return $this->iLineNo;
    }

    /**
     * @param string|CSSString $oCharset
     *
     * @return void
     */
    public function setCharset($sCharset): void
    {
        $sCharset = $sCharset instanceof CSSString ? $sCharset : new CSSString($sCharset);
        $this->oCharset = $sCharset;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->oCharset->getString();
    }

    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $oOutputFormat): string
    {
        return "{$oOutputFormat->comments($this)}@charset {$this->oCharset->render($oOutputFormat)};";
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
        return $this->oCharset;
    }

    /**
     * @param array<array-key, Comment> $comments
     *
     * @return void
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
     *
     * @return void
     */
    public function setComments(array $comments): void
    {
        $this->comments = $comments;
    }
}
