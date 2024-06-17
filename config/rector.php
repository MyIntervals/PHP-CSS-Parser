<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withPaths(
        [
            __DIR__ . '/../src',
            __DIR__ . '/../tests',
        ]
    )
    ->withPhpSets(php70: true)
    ->withRules(
        [
            // AddVoidReturnTypeWhereNoReturnRector::class,
        ]
    );
