<?php

require_once(dirname(__FILE__).'/../CSSParser.php');

$oParser = new CSSParser(file_get_contents('php://stdin'));

$oDoc = $oParser->parse();

echo '#### Structure (`var_dump()`)'."\n";
var_dump($oDoc);

echo '#### Output (`__toString()`)'."\n";
print $oDoc->__toString();
echo "\n";

