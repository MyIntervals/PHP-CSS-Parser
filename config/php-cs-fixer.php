<?php

declare(strict_types=1);

return (new \PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules(
        [
            '@PER-CS2.0' => true,
            '@PER-CS2.0:risky' => true,

            '@PHPUnit50Migration:risky' => true,
            '@PHPUnit52Migration:risky' => true,
            '@PHPUnit54Migration:risky' => true,
            '@PHPUnit55Migration:risky' => true,
            '@PHPUnit56Migration:risky' => true,
            '@PHPUnit57Migration:risky' => true,
            '@PHPUnit60Migration:risky' => true,
            '@PHPUnit75Migration:risky' => true,
            '@PHPUnit84Migration:risky' => true,

            // overwrite the PER2 defaults to restore compatibility with PHP 7.x
            'trailing_comma_in_multiline' => ['elements' => ['arrays', 'match']],

            'php_unit_construct' => true,
            'php_unit_dedicate_assert' => ['target' => 'newest'],
            'php_unit_expectation' => ['target' => 'newest'],
            'php_unit_fqcn_annotation' => true,
            'php_unit_method_casing' => true,
            'php_unit_mock' => ['target' => 'newest'],
            'php_unit_mock_short_will_return' => true,
            'php_unit_namespaced' => ['target' => 'newest'],
            'php_unit_set_up_tear_down_visibility' => true,
            'php_unit_test_annotation' => ['style' => 'annotation'],
            'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
        ]
    );
