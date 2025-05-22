<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Position;

/**
 * Provides a standard reusable implementation of `Positionable`.
 *
 * @internal
 *
 * @phpstan-require-implements Positionable
 */
trait Position
{
    /**
     * @var int<1, max>|null
     */
    protected $lineNumber;

    /**
     * @var int<0, max>|null
     */
    protected $columnNumber;

    /**
     * @return int<1, max>|null
     */
    public function getLineNumber(): ?int
    {
        return $this->lineNumber;
    }

    /**
     * @return int<0, max>
     */
    public function getLineNo(): int
    {
        return $this->getLineNumber() ?? 0;
    }

    /**
     * @return int<0, max>|null
     */
    public function getColumnNumber(): ?int
    {
        return $this->columnNumber;
    }

    /**
     * @return int<0, max>
     */
    public function getColNo(): int
    {
        return $this->getColumnNumber() ?? 0;
    }

    /**
     * @param int<0, max>|null $lineNumber
     * @param int<0, max>|null $columnNumber
     *
     * @return $this fluent interface
     */
    public function setPosition(?int $lineNumber, ?int $columnNumber = null): Positionable
    {
        // The conditional is for backwards compatibility (backcompat); `0` will not be allowed in future.
        $this->lineNumber = $lineNumber !== 0 ? $lineNumber : null;
        $this->columnNumber = $columnNumber;

        return $this;
    }
}
