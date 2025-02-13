<?php

declare(strict_types=1);

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
     * @var array<array-key, Value|string>
     *
     * @internal since 8.8.0
     */
    protected $aComponents;

    /**
     * @var string
     *
     * @internal since 8.8.0
     */
    protected $sSeparator;

    /**
     * @param array<array-key, Value|string>|Value|string $aComponents
     * @param string $sSeparator
     * @param int<0, max> $lineNumber
     */
    public function __construct($aComponents = [], $sSeparator = ',', $lineNumber = 0)
    {
        parent::__construct($lineNumber);
        if (!\is_array($aComponents)) {
            $aComponents = [$aComponents];
        }
        $this->aComponents = $aComponents;
        $this->sSeparator = $sSeparator;
    }

    /**
     * @param Value|string $component
     */
    public function addListComponent($component): void
    {
        $this->aComponents[] = $component;
    }

    /**
     * @return array<array-key, Value|string>
     */
    public function getListComponents()
    {
        return $this->aComponents;
    }

    /**
     * @param array<array-key, Value|string> $aComponents
     */
    public function setListComponents(array $aComponents): void
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
     */
    public function setListSeparator($sSeparator): void
    {
        $this->sSeparator = $sSeparator;
    }

    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $outputFormat): string
    {
        return $outputFormat->implode(
            $outputFormat->spaceBeforeListArgumentSeparator($this->sSeparator) . $this->sSeparator
            . $outputFormat->spaceAfterListArgumentSeparator($this->sSeparator),
            $this->aComponents
        );
    }
}
