<?php

namespace Sabberworm\CSS\Parsing;

/**
* Thrown if the CSS parsers attempts to print something invalid
*/
class OutputException extends \Exception {
    private $iLineNum;
    public function __construct($sMessage, $iLineNum = 0)
    {
        $this->$iLineNum = $iLineNum;
        if (!empty($iLineNum)) {
            $sMessage .= " [line no: $iLineNum]";
        }
        parent::__construct($sMessage);
    }
}