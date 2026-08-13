<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths(
        [
            __DIR__ . '/../../bin',
            __DIR__ . '/../../src',
            __DIR__ . '/../../tests',
        ]
    )
    ->withPhpSets()
    ->withComposerBased()
    ->withSets([
        // SetList::CODE_QUALITY,
        // SetList::CODING_STYLE,
        // SetList::DEAD_CODE,
        // SetList::EARLY_RETURN,
        // SetList::INSTANCEOF,
        // SetList::NAMING,
        // SetList::PRIVATIZATION,
        SetList::TYPE_DECLARATION,
    ])
    ->withImportNames(true, true, false);
