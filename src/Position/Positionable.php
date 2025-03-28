<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Position;

/**
 * Represents a CSS item that may have a position in the source CSS document (line number and possibly column number).
 *
 * A standard implementation of this interface is available in the `Position` trait.
 */
interface Positionable
{
    /**
     * @return int<1, max>|null
     */
    public function getLineNumber(): ?int;

    /**
     * @deprecated in version 9.0.0, will be removed in v10.0. Use `getLineNumber()` instead.
     *
     * @return int<0, max>
     */
    public function getLineNo(): int;

    /**
     * @return int<0, max>|null
     */
    public function getColumnNumber(): ?int;

    /**
     * @deprecated in version 9.0.0, will be removed in v10.0. Use `getColumnNumber()` instead.
     *
     * @return int<0, max>
     */
    public function getColNo(): int;

    /**
     * @param int<0, max>|null $lineNumber
     *        Providing zero for this parameter is deprecated in version 9.0.0, and will not be supported from v10.0.
     *        Use `null` instead when no line number is available.
     * @param int<0, max>|null $columnNumber
     */
    public function setPosition(?int $lineNumber, ?int $columnNumber = null): void;
}
