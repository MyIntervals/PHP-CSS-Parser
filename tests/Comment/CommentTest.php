<?php

namespace Sabberworm\CSS\Tests\Comment;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\Tests\ParserTest as TestsParserTest;

/**
 * @covers \Sabberworm\CSS\Comment\Comment
 * @covers \Sabberworm\CSS\Comment\Commentable
 * @covers \Sabberworm\CSS\OutputFormat
 * @covers \Sabberworm\CSS\OutputFormatter
 */
final class CommentTest extends TestCase
{
    /**
     * @test
     */
    public function implementsRenderable()
    {
        $subject = new Comment();

        self::assertInstanceOf(Renderable::class, $subject);
    }

    /**
     * @test
     */
    public function getCommentOnEmptyInstanceReturnsReturnsEmptyString()
    {
        $subject = new Comment();

        self::assertSame('', $subject->getComment());
    }

    /**
     * @test
     */
    public function getCommentInitiallyReturnsCommentPassedToConstructor()
    {
        $comment = 'There is no spoon.';
        $subject = new Comment($comment);

        self::assertSame($comment, $subject->getComment());
    }

    /**
     * @test
     */
    public function setCommentSetsComments()
    {
        $comment = 'There is no spoon.';
        $subject = new Comment();

        $subject->setComment($comment);

        self::assertSame($comment, $subject->getComment());
    }

    /**
     * @test
     */
    public function getLineNoOnEmptyInstanceReturnsReturnsZero()
    {
        $subject = new Comment();

        self::assertSame(0, $subject->getLineNo());
    }

    /**
     * @test
     */
    public function getLineNoInitiallyReturnsLineNumberPassedToConstructor()
    {
        $lineNumber = 42;
        $subject = new Comment('', $lineNumber);

        self::assertSame($lineNumber, $subject->getLineNo());
    }

    /**
     * @test
     */
    public function toStringRendersCommentEnclosedInCommentDelimiters()
    {
        $comment = 'There is no spoon.';
        $subject = new Comment();

        $subject->setComment($comment);

        self::assertSame('/*' . $comment . '*/', (string)$subject);
    }

    /**
     * @test
     */
    public function renderRendersCommentEnclosedInCommentDelimiters()
    {
        $comment = 'There is no spoon.';
        $subject = new Comment();

        $subject->setComment($comment);

        self::assertSame('/*' . $comment . '*/', $subject->render(new OutputFormat()));
    }

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
