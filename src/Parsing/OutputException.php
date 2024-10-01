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
     * @param int $iLineNo
     */
    public function __construct($sMessage, $iLineNo = 0)
    {
        parent::__construct($sMessage, $iLineNo);
    }
}
