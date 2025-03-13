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
    protected $separator;

    /**
     * @param array<array-key, Value|string>|Value|string $components
     * @param int<0, max> $lineNumber
     */
    public function __construct($components = [], string $separator = ',', int $lineNumber = 0)
    {
        parent::__construct($lineNumber);
        if (!\is_array($components)) {
            $components = [$components];
        }
        $this->components = $components;
        $this->separator = $separator;
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
        return $this->separator;
    }

    /**
     * @param string $separator
     */
    public function setListSeparator($separator): void
    {
        $this->separator = $separator;
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
        $formatter = $outputFormat->getFormatter();

        return $formatter->implode(
            $formatter->spaceBeforeListArgumentSeparator($this->separator) . $this->separator
            . $formatter->spaceAfterListArgumentSeparator($this->separator),
            $this->components
        );
    }
}
