<?php

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\OutputFormat;

class CSSFunction extends ValueList
{
    protected $sName;

    /**
     * @param RuleValueList|array $aArguments
     */
    public function __construct($sName, $aArguments, $sSeparator = ',', $iLineNo = 0)
    {
        if ($aArguments instanceof RuleValueList) {
            $sSeparator = $aArguments->getListSeparator();
            $aArguments = $aArguments->getListComponents();
        }
        $this->sName = $sName;
        $this->iLineNo = $iLineNo;
        parent::__construct($aArguments, $sSeparator, $iLineNo);
    }

    public function getName()
    {
        return $this->sName;
    }

    public function setName($sName)
    {
        $this->sName = $sName;
    }

    public function getArguments()
    {
        return $this->aComponents;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render(new OutputFormat());
    }

    /**
     * @return string
     */
    public function render(OutputFormat $oOutputFormat)
    {
        $aArguments = parent::render($oOutputFormat);
        return "{$this->sName}({$aArguments})";
    }
}
