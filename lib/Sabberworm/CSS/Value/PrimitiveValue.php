<?php

namespace Sabberworm\CSS\Value;

abstract class PrimitiveValue extends Value {
    public function __construct($iLineNum = 0) {
        parent::__construct($iLineNum);
    }

}