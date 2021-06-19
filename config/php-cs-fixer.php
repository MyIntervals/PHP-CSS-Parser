<?php

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}

return (new \PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules(
        [
            '@PSR12' => true,
            // Disable constant visibility from the PSR12 rule set as this would break compatibility with PHP < 7.1.
            'visibility_required' => ['elements' => ['property', 'method']],
        ]
    );
