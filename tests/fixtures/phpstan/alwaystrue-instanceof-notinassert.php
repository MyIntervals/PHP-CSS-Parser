<?php

declare(strict_types=1);

$e = new \Exception();
if ($e instanceof \Exception) {
    $theTest = 'passed';
}
