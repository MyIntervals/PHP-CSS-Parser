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

            'php_unit_construct' => true,
            'php_unit_dedicate_assert' => ['target' => '5.0'],
            'php_unit_expectation' => ['target' => '5.6'],
            'php_unit_fqcn_annotation' => true,
            'php_unit_method_casing' => true,
            'php_unit_mock' => ['target' => '5.5'],
            'php_unit_mock_short_will_return' => true,
            'php_unit_namespaced' => ['target' => '5.7'],
            'php_unit_set_up_tear_down_visibility' => true,
            'php_unit_test_annotation' => ['style' => 'annotation'],
            'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
        ]
    );
