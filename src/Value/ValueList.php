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
    protected $components;

    /**
     * @var string
     *
     * @internal since 8.8.0
     */
    protected $sSeparator;

    /**
     * @param array<array-key, Value|string>|Value|string $components
     * @param string $sSeparator
     * @param int<0, max> $lineNumber
     */
    public function __construct($components = [], $sSeparator = ',', $lineNumber = 0)
    {
        parent::__construct($lineNumber);
        if (!\is_array($components)) {
            $components = [$components];
        }
        $this->components = $components;
        $this->sSeparator = $sSeparator;
    }

    /**
     * @param Value|string $component
     */
    public function addListComponent($component): void
    {
        $this->components[] = $component;
    }

    /**
     * @return array<array-key, Value|string>
     */
    public function getListComponents()
    {
        return $this->components;
    }

    /**
     * @param array<array-key, Value|string> $components
     */
    public function setListComponents(array $components): void
    {
        $this->components = $components;
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

    /**
     * @deprecated in V8.8.0, will be removed in V9.0.0. Use `render` instead.
     */
    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $outputFormat): string
    {
        return $outputFormat->implode(
            $outputFormat->spaceBeforeListArgumentSeparator($this->sSeparator) . $this->sSeparator
            . $outputFormat->spaceAfterListArgumentSeparator($this->sSeparator),
            $this->components
        );
    }
}
