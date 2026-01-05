<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\ShortClassNameProvider;

/**
 * A `ValueList` represents a lists of `Value`s, separated by some separation character
 * (mostly `,`, whitespace, or `/`).
 *
 * There are two types of `ValueList`s: `RuleValueList` and `CSSFunction`
 */
abstract class ValueList extends Value
{
    use ShortClassNameProvider;

    /**
     * @var array<Value|string>
     *
     * @internal since 8.8.0
     */
    protected $components;

    /**
     * @var non-empty-string
     *
     * @internal since 8.8.0
     */
    protected $separator;

    /**
     * @param array<Value|string>|Value|string $components
     * @param non-empty-string $separator
     * @param int<1, max>|null $lineNumber
     */
    public function __construct($components = [], $separator = ',', ?int $lineNumber = null)
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
     * @return array<Value|string>
     */
    public function getListComponents(): array
    {
        return $this->components;
    }

    /**
     * @param array<Value|string> $components
     */
    public function setListComponents(array $components): void
    {
        $this->components = $components;
    }

    /**
     * @return non-empty-string
     */
    public function getListSeparator(): string
    {
        return $this->separator;
    }

    /**
     * @param non-empty-string $separator
     */
    public function setListSeparator(string $separator): void
    {
        $this->separator = $separator;
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

    /**
     * @return array<string, bool|int|float|string|array<mixed>|null>
     *
     * @internal
     */
    public function getArrayRepresentation(): array
    {
        return [
            'class' => $this->getShortClassName(),
            'components' => \array_map(
                /**
                 * @parm Value|string $component
                 */
                function ($component): array {
                    if (\is_string($component)) {
                        return ['class' => 'string', 'value' => $component];
                    }
                    return $component->getArrayRepresentation();
                },
                $this->components
            ),
            'separator' => $this->separator,
        ];
    }
}
