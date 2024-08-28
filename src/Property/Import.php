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
    private $oLocation;

    /**
     * @var string
     */
    private $sMediaQuery;

    /**
     * @var int
     */
    protected $iLineNo;

    /**
     * @var array<array-key, Comment>
     */
    protected $aComments;

    /**
     * @param URL $oLocation
     * @param string $sMediaQuery
     * @param int $iLineNo
     */
    public function __construct(URL $oLocation, $sMediaQuery, $iLineNo = 0)
    {
        $this->oLocation = $oLocation;
        $this->sMediaQuery = $sMediaQuery;
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

    /**
     * @param URL $oLocation
     */
    public function setLocation($oLocation): void
    {
        $this->oLocation = $oLocation;
    }

    /**
     * @return URL
     */
    public function getLocation()
    {
        return $this->oLocation;
    }

    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $oOutputFormat): string
    {
        return $oOutputFormat->comments($this) . '@import ' . $this->oLocation->render($oOutputFormat)
            . ($this->sMediaQuery === null ? '' : ' ' . $this->sMediaQuery) . ';';
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
        $aResult = [$this->oLocation];
        if ($this->sMediaQuery) {
            \array_push($aResult, $this->sMediaQuery);
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

    /**
     * @return string
     */
    public function getMediaQuery()
    {
        return $this->sMediaQuery;
    }
}
