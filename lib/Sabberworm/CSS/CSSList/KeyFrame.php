<?php

namespace Sabberworm\CSS\CSSList;

use Sabberworm\CSS\Property\AtRule;

class KeyFrame extends CSSList implements AtRule
{
    /**
     * @var string|null
     */
    private $vendorKeyFrame;

    /**
     * @var string|null
     */
    private $animationName;

    public function __construct($iLineNo = 0)
    {
        parent::__construct($iLineNo);
        $this->vendorKeyFrame = null;
        $this->animationName  = null;
    }

    public function setVendorKeyFrame($vendorKeyFrame)
    {
        $this->vendorKeyFrame = $vendorKeyFrame;
    }

    public function getVendorKeyFrame()
    {
        return $this->vendorKeyFrame;
    }

    public function setAnimationName($animationName)
    {
        $this->animationName = $animationName;
    }

    public function getAnimationName()
    {
        return $this->animationName;
    }

    public function __toString()
    {
        return $this->render(new \Sabberworm\CSS\OutputFormat());
    }

    /**
     * @param \Sabberworm\CSS\OutputFormat $oOutputFormat
     *
     * @return string
     */
    public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat)
    {
        $sResult = "@{$this->vendorKeyFrame} {$this->animationName}{$oOutputFormat->spaceBeforeOpeningBrace()}{";
        $sResult .= parent::render($oOutputFormat);
        $sResult .= '}';
        return $sResult;
    }

    public function isRootList()
    {
        return false;
    }

    /**
     * @return string|null
     */
    public function atRuleName()
    {
        return $this->vendorKeyFrame;
    }

    /**
     * @return string|null
     */
    public function atRuleArgs()
    {
        return $this->animationName;
    }
}
