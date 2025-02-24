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
    public function __construct(string $message, int $lineNumber = 0)
    {
        $this->lineNumber = $lineNumber;
        if ($lineNumber !== 0) {
            $message .= " [line no: $lineNumber]";
        }
        parent::__construct($message);
    }

    /**
     * @return int<0, max>
     */
    public function getLineNo(): int
    {
        return $this->lineNumber;
    }
}
