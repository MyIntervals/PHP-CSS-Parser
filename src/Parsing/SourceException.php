<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Parsing;

class SourceException extends \Exception
{
    /**
     * @var int<0, max>
     */
    private $lineNumber;

    /**
     * @param int<0, max> $lineNumber
     */
    public function __construct(string $sMessage, int $lineNumber = 0)
    {
        $this->lineNumber = $lineNumber;
        if ($lineNumber !== 0) {
            $sMessage .= " [line no: $lineNumber]";
        }
        parent::__construct($sMessage);
    }

    /**
     * @return int<0, max>
     */
    public function getLineNo(): int
    {
        return $this->lineNumber;
    }
}
