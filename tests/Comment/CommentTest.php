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
    public function implementsRenderable(): void
    {
        $subject = new Comment();

        self::assertInstanceOf(Renderable::class, $subject);
    }

    /**
     * @test
     */
    public function getCommentOnEmptyInstanceReturnsReturnsEmptyString(): void
    {
        $subject = new Comment();

        self::assertSame('', $subject->getComment());
    }

    /**
     * @test
     */
    public function getCommentInitiallyReturnsCommentPassedToConstructor(): void
    {
        $comment = 'There is no spoon.';
        $subject = new Comment($comment);

        self::assertSame($comment, $subject->getComment());
    }

    /**
     * @test
     */
    public function setCommentSetsComments(): void
    {
        $comment = 'There is no spoon.';
        $subject = new Comment();

        $subject->setComment($comment);

        self::assertSame($comment, $subject->getComment());
    }

    /**
     * @test
     */
    public function getLineNoOnEmptyInstanceReturnsReturnsZero(): void
    {
        $subject = new Comment();

        self::assertSame(0, $subject->getLineNo());
    }

    /**
     * @test
     */
    public function getLineNoInitiallyReturnsLineNumberPassedToConstructor(): void
    {
        $lineNumber = 42;
        $subject = new Comment('', $lineNumber);

        self::assertSame($lineNumber, $subject->getLineNo());
    }

    /**
     * @test
     */
    public function toStringRendersCommentEnclosedInCommentDelimiters(): void
    {
        $comment = 'There is no spoon.';
        $subject = new Comment();

        $subject->setComment($comment);

        self::assertSame('/*' . $comment . '*/', (string) $subject);
    }

    /**
     * @test
     */
    public function renderRendersCommentEnclosedInCommentDelimiters(): void
    {
        $comment = 'There is no spoon.';
        $subject = new Comment();

        $subject->setComment($comment);

        self::assertSame('/*' . $comment . '*/', $subject->render(new OutputFormat()));
    }

    /**
     * @test
     */
    public function keepCommentsInOutput(): void
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
            . '.foo,#bar{background-color:#000;}'
            . '@media screen{#foo.bar{position:absolute;}}',
            $css->render(OutputFormat::createCompact())
        );
    }
}
