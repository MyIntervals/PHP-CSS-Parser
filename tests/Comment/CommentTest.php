<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Comment;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Tests\ParserTest as TestsParserTest;

/**
 * @coversNothing
 */
final class CommentTest extends TestCase
{
    /**
     * @test
     */
    public function keepCommentsInOutput(): void
    {
        $cssDocument = TestsParserTest::parsedStructureForFile('comments');

        $expected1 = "/** Number 11 **/\n\n"
            . "/**\n"
            . " * Comments\n"
            . " */\n\n"
            . "/* Hell */\n"
            . "@import url(\"some/url.css\") screen;\n\n"
            . "/* Number 4 */\n\n"
            . "/* Number 5 */\n"
            . ".foo, #bar {\n"
            . "\t/* Number 6 */\n"
            . "\tbackground-color: #000;\n"
            . "}\n\n"
            . "@media screen {\n"
            . "\t/** Number 10 **/\n"
            . "\t#foo.bar {\n"
            . "\t\t/** Number 10b **/\n"
            . "\t\tposition: absolute;\n"
            . "\t}\n"
            . "}\n";
        self::assertSame($expected1, $cssDocument->render(OutputFormat::createPretty()));

        $expected2 = "/** Number 11 **//**\n"
            . " * Comments\n"
            . ' *//* Hell */@import url("some/url.css") screen;'
            . '/* Number 4 *//* Number 5 */.foo,#bar{'
            . '/* Number 6 */background-color:#000}@media screen{'
            . '/** Number 10 **/#foo.bar{/** Number 10b **/position:absolute}}';
        self::assertSame($expected2, $cssDocument->render(OutputFormat::createCompact()->setRenderComments(true)));
    }

    /**
     * @test
     */
    public function stripCommentsFromOutput(): void
    {
        $css = TestsParserTest::parsedStructureForFile('comments');

        $expected1 = "\n"
            . "@import url(\"some/url.css\") screen;\n\n"
            . ".foo, #bar {\n" .
            "\tbackground-color: #000;\n"
            . "}\n\n"
            . "@media screen {\n"
            . "\t#foo.bar {\n"
            . "\t\tposition: absolute;\n"
            . "\t}\n"
            . "}\n";
        self::assertSame($expected1, $css->render(OutputFormat::createPretty()->setRenderComments(false)));

        $expected2 = '@import url("some/url.css") screen;'
            . '.foo,#bar{background-color:#000}'
            . '@media screen{#foo.bar{position:absolute}}';
        self::assertSame($expected2, $css->render(OutputFormat::createCompact()));
    }
}
