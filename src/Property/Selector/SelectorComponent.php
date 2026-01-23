<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property\Selector;

interface SelectorComponent
{
    /**
     * @return non-empty-string
     */
    public function getStringValue(): string;

    /**
     * @param non-empty-string $value
     */
    public function setStringValue(string $value): void;

    /**
     * @return int<0, max>
     */
    public function getSpecificity(): int;
}
