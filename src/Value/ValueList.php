<?php

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\OutputFormat;

/**
 * A `ValueList` represents a lists of `Value`s, separated by some separation character
 * (mostly `,`, whitespace, or `/`).
 *
 * There are two types of `ValueList`s: `RuleValueList` and `CSSFunction`
 */
abstract class ValueList extends Value
{
    /**
     * @var array<int, Value|string>
     */
    protected $aComponents;

    /**
     * @var string
     */
    protected $sSeparator;

    /**
     * @param array<int, Value|string>|Value|string $aComponents
     * @param string $sSeparator
     * @param int $iLineNo
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

    /**
     * @param Value|string $mComponent
     *
     * @return void
     */
    public function addListComponent($mComponent)
    {
        $this->aComponents[] = $mComponent;
    }

    /**
     * @return array<int, Value|string>
     */
    public function getListComponents()
    {
        return $this->aComponents;
    }

    /**
     * @param array<int, Value|string> $aComponents
     *
     * @return void
     */
    public function setListComponents(array $aComponents)
    {
        $this->aComponents = $aComponents;
    }

    /**
     * @return string
     */
    public function getListSeparator()
    {
        return $this->sSeparator;
    }

    /**
     * @param string $sSeparator
     *
     * @return void
     */
    public function setListSeparator($sSeparator)
    {
        $this->sSeparator = $sSeparator;
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
        return $oOutputFormat->implode(
            $oOutputFormat->spaceBeforeListArgumentSeparator($this->sSeparator) . $this->sSeparator
            . $oOutputFormat->spaceAfterListArgumentSeparator($this->sSeparator),
            $this->aComponents
        );
    }
}
