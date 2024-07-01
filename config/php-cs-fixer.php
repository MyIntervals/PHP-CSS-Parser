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
            'trailing_comma_in_multiline' => ['elements' => ['arrays']],

            // casing
            'magic_constant_casing' => true,
            'native_function_casing' => true,

            // cast notation
            'modernize_types_casting' => true,
            'no_short_bool_cast' => true,

            // class notation
            'no_php4_constructor' => true,

            // comment
            'no_empty_comment' => true,

            // control structure
            'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],

            // function notation
            'native_function_invocation' => ['include' => ['@all']],

            // import
            'no_unused_imports' => true,

            // language construct
            'combine_consecutive_issets' => true,
            'combine_consecutive_unsets' => true,
            'dir_constant' => true,
            'is_null' => true,

            // namespace notation
            'no_leading_namespace_whitespace' => true,

            // operator
            'standardize_not_equals' => true,
            'ternary_to_null_coalescing' => true,

            // PHP tag
            'linebreak_after_opening_tag' => true,

            // PHPUnit
            'php_unit_construct' => true,
            'php_unit_dedicate_assert' => ['target' => 'newest'],
            'php_unit_expectation' => ['target' => 'newest'],
            'php_unit_fqcn_annotation' => true,
            'php_unit_mock_short_will_return' => true,
            'php_unit_set_up_tear_down_visibility' => true,
            'php_unit_test_annotation' => ['style' => 'annotation'],
            'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],

            // PHPDoc
            'no_blank_lines_after_phpdoc' => true,
            'no_empty_phpdoc' => true,
            'phpdoc_indent' => true,
            'phpdoc_no_package' => true,
            'phpdoc_trim' => true,
            'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],

            // return notation
            'no_useless_return' => true,

            // semicolon
            'no_empty_statement' => true,
            'no_singleline_whitespace_before_semicolons' => true,
            'semicolon_after_instruction' => true,

            // strict
            'declare_strict_types' => true,
            'strict_param' => true,

            // string notation
            'string_implicit_backslashes' => ['single_quoted' => 'escape'],
        ]
    );
