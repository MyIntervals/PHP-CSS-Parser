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
    private $mUrl;

    /**
     * @var string
     */
    private $sPrefix;

    /**
     * @var int
     */
    private $iLineNo;

    /**
     * @var array<array-key, Comment>
     */
    protected $aComments;

    /**
     * @param string $mUrl
     * @param string|null $sPrefix
     * @param int $iLineNo
     */
    public function __construct($mUrl, $sPrefix = null, $iLineNo = 0)
    {
        $this->mUrl = $mUrl;
        $this->sPrefix = $sPrefix;
        $this->iLineNo = $iLineNo;
        $this->aComments = [];
    }

    /**
     * @return int
     */
    public function getLineNo()
    {
        return $this->iLineNo;
    }

    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $oOutputFormat): string
    {
        return '@namespace ' . ($this->sPrefix === null ? '' : $this->sPrefix . ' ')
            . $this->mUrl->render($oOutputFormat) . ';';
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->mUrl;
    }

    /**
     * @return string|null
     */
    public function getPrefix()
    {
        return $this->sPrefix;
    }

    /**
     * @param string $mUrl
     */
    public function setUrl($mUrl): void
    {
        $this->mUrl = $mUrl;
    }

    /**
     * @param string $sPrefix
     */
    public function setPrefix($sPrefix): void
    {
        $this->sPrefix = $sPrefix;
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
        $aResult = [$this->mUrl];
        if ($this->sPrefix) {
            \array_unshift($aResult, $this->sPrefix);
        }
        return $aResult;
    }

    /**
     * @param array<array-key, Comment> $aComments
     */
    public function addComments(array $aComments): void
    {
        $this->aComments = \array_merge($this->aComments, $aComments);
    }

    /**
     * @return array<array-key, Comment>
     */
    public function getComments()
    {
        return $this->aComments;
    }

    /**
     * @param array<array-key, Comment> $aComments
     */
    public function setComments(array $aComments): void
    {
        $this->aComments = $aComments;
    }
}
