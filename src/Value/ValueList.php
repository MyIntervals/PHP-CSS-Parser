<?php

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\OutputFormat;

abstract class ValueList extends Value
{
    protected $aComponents;

    protected $sSeparator;

    /**
     * @param array|mixed $aComponents
     */
    public function __construct($aComponents = [], $sSeparator = ',', $iLineNo = 0)
    {
        parent::__construct($iLineNo);
        if (!is_array($aComponents)) {
            $aComponents = [$aComponents];
        }
        $this->aComponents = $aComponents;
        $this->sSeparator = $sSeparator;
    }

    public function addListComponent($mComponent)
    {
        $this->aComponents[] = $mComponent;
    }

    public function getListComponents()
    {
        return $this->aComponents;
    }

    public function setListComponents(array $aComponents)
    {
        $this->aComponents = $aComponents;
    }

    public function getListSeparator()
    {
        return $this->sSeparator;
    }

    public function setListSeparator($sSeparator)
    {
        $this->sSeparator = $sSeparator;
    }

    public function __toString()
    {
        return $this->render(new OutputFormat());
    }

    /**
     * @param OutputFormat $oOutputFormat
     *
     * @return string
     */
    public function render(OutputFormat $oOutputFormat)
    {
        return $oOutputFormat->implode(
            $oOutputFormat->spaceBeforeListArgumentSeparator($this->sSeparator) . $this->sSeparator
            . $oOutputFormat->spaceAfterListArgumentSeparator($this->sSeparator),
            $this->aComponents
        );
    }
}
