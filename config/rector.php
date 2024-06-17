<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withPaths(
        [
            __DIR__ . '/../src',
            __DIR__ . '/../tests',
        ]
    )
    ->withSets([SetList::PHP_71])
    ->withRules(
        [
            // AddVoidReturnTypeWhereNoReturnRector::class,
        ]
    );
