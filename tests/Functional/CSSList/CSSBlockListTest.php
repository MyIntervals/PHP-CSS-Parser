<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Settings;

/**
 * @covers \Sabberworm\CSS\CSSList\CSSBlockList
 */
final class CSSBlockListTest extends TestCase
{
    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: list<non-empty-string>}>
     */
    public static function provideSpecificitySearchAndExpectedSelectors(): array
    {
        return [
            'less than or equal' => ['<= 1', ['p']],
            'less than' => ['< 10', ['p']],
            'greater than or equal' => ['>= 10', ['#a', '.b', 'p.c']],
            'greater than' => ['> 10', ['#a', 'p.c']],
            'number only' => ['10', ['.b']],
            'equals' => ['== 11', ['p.c']],
        ];
    }

    /**
     * @test
     *
     * @param list<non-empty-string> $expectedSelectors
     *
     * @dataProvider provideSpecificitySearchAndExpectedSelectors
     */
    public function getAllSelectorsFiltersBySpecificity(string $specificitySearch, array $expectedSelectors): void
    {
        $subject = Document::parse(new ParserState('#a {} .b {} p {} p.c {}', Settings::create()));

        $result = $subject->getSelectorsBySpecificity($specificitySearch);

        $selectors = \array_map(
            static function (Selector $selector): string {
                return $selector->getSelector();
            },
            $result
        );
        self::assertSame($expectedSelectors, $selectors);
    }
}
