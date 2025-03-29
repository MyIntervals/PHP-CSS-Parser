<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Parsing;

use Sabberworm\CSS\Position\Position;
use Sabberworm\CSS\Position\Positionable;

class SourceException extends \Exception implements Positionable
{
    use Position;

    /**
     * @param int<0, max> $lineNumber
     */
    public function __construct(string $message, int $lineNumber = 0)
    {
        $this->setPosition($lineNumber);
        if ($lineNumber !== 0) {
            $message .= " [line no: $lineNumber]";
        }
        parent::__construct($message);
    }
}
