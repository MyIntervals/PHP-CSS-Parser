<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Support\Traits;

use PHPUnit\Framework\TestCase;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * Adds common data providers to a test case which needs some standard sets of selectors to test with.
 *
 * @mixin TestCase
 */
trait SelectorDataProviders
{
    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: int<0, max>}>
     */
    public static function provideSelectorAndSpecificity(): array
    {
        return [
            'type' => ['a', 1],
            'class' => ['.highlighted', 10],
            'type with class' => ['li.green', 11],
            'pseudo-class' => [':hover', 10],
            'type with pseudo-class' => ['a:hover', 11],
            'class with pseudo-class' => ['.help:hover', 20],
            'ID' => ['#file', 100],
            'ID and descendent class' => ['#test .help', 110],
            'type with ID' => ['h2#my-mug', 101],
            'pseudo-element' => ['::before', 1],
            'type with pseudo-element' => ['li::before', 2],
            'type and descendent type with pseudo-element' => ['ol li::before', 3],
            '`not`' => [':not(#your-mug)', 100],
            // TODO, broken: The specificity should be the highest of the `:not` arguments, not the sum.
            '`not` with multiple arguments' => [':not(#your-mug, .their-mug)', 110],
            'attribute with `"`' => ['[alt="{}()[]\\"\',"]', 10],
            'attribute with `\'`' => ['[alt=\'{}()[]"\\\',\']', 10],
        ];
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideSelector(): array
    {
        // Strip the specificity from the data array
        return \array_map(
            static function (array $data): array {
                return [$data[0]];
            },
            self::provideSelectorAndSpecificity()
        );
    }

    /**
     * @return DataProvider<non-empty-string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function provideTwoSelectors(): DataProvider
    {
        return DataProvider::cross(self::provideSelector(), self::provideSelector());
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function provideSelectorWithEscapedQuotes(): array
    {
        return [
            'escaped double quote in double-quoted attribute' => ['a[href="test\\"value"]'],
            'escaped single quote in single-quoted attribute' => ['a[href=\'test\\\'value\']'],
            'multiple escaped double quotes in double-quoted attribute' => ['a[title="say \\"hello\\" world"]'],
            'multiple escaped single quotes in single-quoted attribute' => ['a[title=\'say \\\'hello\\\' world\']'],
            'escaped quote at start of attribute value' => ['a[data-test="\\"start"]'],
            'escaped quote at end of attribute value' => ['a[data-test="end\\""]'],
            'escaped backslash followed by quote' => ['a[data-test="test\\\\"]'],
            'escaped backslash before escaped quote' => ['a[data-test="test\\\\\\"value"]'],
            'triple backslash before quote' => ['a[data-test="test\\\\\\""]'],
            'escaped single quotes in selector itself, with other escaped characters'
            => ['.before\\:content-\\[\\\'\\\'\\]:before'],
            'escaped double quotes in selector itself, with other escaped characters'
            => ['.before\\:content-\\[\\"\\"\\]:before'],
        ];
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function provideSelectorWithAndWithoutComment(): array
    {
        return [
            'comment before' => ['/*comment*/body', 'body'],
            'comment after' => ['body/*comment*/', 'body'],
            'comment within' => ['./*comment*/teapot', '.teapot'],
            'comment within function' => [':not(#your-mug,/*comment*/.their-mug)', ':not(#your-mug,.their-mug)'],
        ];
    }
}
