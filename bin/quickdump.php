#!/usr/bin/env php
<?php

declare(strict_types=1);

use Sabberworm\CSS\Parser;

/**
 * This script is used for generating the examples in the README.
 */

require_once(__DIR__ . '/../vendor/autoload.php');

$source = \file_get_contents('php://stdin');
if (!\is_string($source)) {
    throw new \RuntimeException('Failed to read from stdin.');
}
$parser = new Parser($source);

$document = $parser->parse();
echo "\n" . '#### Input' . "\n\n```css\n";
print $source;

echo "\n```\n\n" . '#### Structure (`var_dump()`)' . "\n\n```php\n";
\var_dump($document);

echo "\n```\n\n" . '#### Output (`render()`)' . "\n\n```css\n";
print $document->render();

echo "\n```\n";
