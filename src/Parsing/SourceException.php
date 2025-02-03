<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Parsing;

class SourceException extends \Exception
{
    /**
     * @var int
     */
    private $lineNumber;

    /**
     * @param string $sMessage
     * @param int $lineNumber
     */
    public function __construct($sMessage, $lineNumber = 0)
    {
        $this->lineNumber = $lineNumber;
        if ($lineNumber !== 0) {
            $sMessage .= " [line no: $lineNumber]";
        }
        parent::__construct($sMessage);
    }

    /**
     * @return int
     */
    public function getLineNo()
    {
        return $this->lineNumber;
    }
}
