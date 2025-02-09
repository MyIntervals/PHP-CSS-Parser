<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Parsing;

/**
 * Thrown if the CSS parser attempts to print something invalid.
 */
final class OutputException extends SourceException
{
    /**
     * @param string $sMessage
     * @param int<0, max> $lineNumber
     */
    public function __construct($sMessage, $lineNumber = 0)
    {
        parent::__construct($sMessage, $lineNumber);
    }
}
