<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Functional\CSSList;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\CSSList\CSSList;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Settings;

/**
 * @covers \Sabberworm\CSS\CSSList\CSSList
 */
final class CSSListTest extends TestCase
{
    /**
     * The content provided must (currently) be in the same format as the expected rendering.
     *
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public function provideValidContentForParsing(): array
    {
        return [
            'at-import rule' => ['@import url("foo.css");'],
            'rule with declaration block' => ['a {color: green;}'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $followingContent
     *
     * @dataProvider provideValidContentForParsing
     */
    public function parseListAtRootLevelSkipsErroneousClosingBraceAndParsesFollowingContent(
        string $followingContent
    ): void {
        $parserState = new ParserState('}' . $followingContent, Settings::create());
        // The subject needs to be a `Document`, as that is currently the test for 'root level'.
        // Otherwise `}` will be treated as 'end of list'.
        $subject = new Document();

        CSSList::parseList($parserState, $subject);

        self::assertSame($followingContent, $subject->render(new OutputFormat()));
    }
}
