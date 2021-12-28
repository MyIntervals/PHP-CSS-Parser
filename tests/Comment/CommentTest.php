<?php

namespace Sabberworm\CSS\Tests\Comment;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Tests\ParserTest as TestsParserTest;

/**
 * @covers \Sabberworm\CSS\Comment\Comment
 * @covers \Sabberworm\CSS\Comment\Commentable
 * @covers \Sabberworm\CSS\OutputFormat
 * @covers \Sabberworm\CSS\OutputFormatter
 */
class ParserTest extends TestCase
{
    /**
     * @test
     */
    public function keepCommentsInOutput()
    {
        $oCss = TestsParserTest::parsedStructureForFile('comments');
        self::assertSame('/** Number 11 **/

/**
 * Comments
 */

/* Hell */
@import url("some/url.css") screen;

/* Number 4 */

/* Number 5 */
.foo, #bar {
	/* Number 6 */
	background-color: #000;
}

@media screen {
	/** Number 10 **/
	#foo.bar {
		/** Number 10b **/
		position: absolute;
	}
}
', $oCss->render(OutputFormat::createPretty()));
        self::assertSame(
            '/** Number 11 **//**' . "\n"
                . ' * Comments' . "\n"
                . ' *//* Hell */@import url("some/url.css") screen;'
                . '/* Number 4 *//* Number 5 */.foo,#bar{'
                . '/* Number 6 */background-color:#000;}@media screen{'
                . '/** Number 10 **/#foo.bar{/** Number 10b **/position:absolute;}}',
            $oCss->render(OutputFormat::createCompact()->setRenderComments(true))
        );
    }

    /**
     * @test
     */
    public function stripCommentsFromOutput()
    {
        $oCss = TestsParserTest::parsedStructureForFile('comments');
        self::assertSame('
@import url("some/url.css") screen;

.foo, #bar {
	background-color: #000;
}

@media screen {
	#foo.bar {
		position: absolute;
	}
}
', $oCss->render(OutputFormat::createPretty()->setRenderComments(false)));
        self::assertSame(
            '@import url("some/url.css") screen;'
                . '.foo,#bar{background-color:#000;}'
                . '@media screen{#foo.bar{position:absolute;}}',
            $oCss->render(OutputFormat::createCompact())
        );
    }
}
