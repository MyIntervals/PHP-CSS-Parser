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
', $cssDocument->render(OutputFormat::createPretty()));
        self::assertSame(
            '/** Number 11 **//**' . "\n"
            . ' * Comments' . "\n"
            . ' *//* Hell */@import url("some/url.css") screen;'
            . '/* Number 4 *//* Number 5 */.foo,#bar{'
            . '/* Number 6 */background-color:#000}@media screen{'
            . '/** Number 10 **/#foo.bar{/** Number 10b **/position:absolute}}',
            $cssDocument->render(OutputFormat::createCompact()->setRenderComments(true))
        );
    }

    /**
     * @test
     */
    public function stripCommentsFromOutput(): void
    {
        $css = TestsParserTest::parsedStructureForFile('comments');
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
', $css->render(OutputFormat::createPretty()->setRenderComments(false)));
        self::assertSame(
            '@import url("some/url.css") screen;'
            . '.foo,#bar{background-color:#000}'
            . '@media screen{#foo.bar{position:absolute}}',
            $css->render(OutputFormat::createCompact())
        );
    }
}
