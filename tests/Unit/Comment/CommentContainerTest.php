<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Comment;

use PHPUnit\Framework\Constraint\LogicalAnd;
use PHPUnit\Framework\Constraint\TraversableContains;
use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\Tests\Unit\Comment\Fixtures\ConcreteCommentContainer;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @covers \Sabberworm\CSS\Comment\CommentContainer
 */
final class CommentContainerTest extends TestCase
{
    /**
     * @var ConcreteCommentContainer
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ConcreteCommentContainer();
    }

    /**
     * @test
     */
    public function getCommentsInitiallyReturnsEmptyArray(): void
    {
        self::assertSame([], $this->subject->getComments());
    }

    /**
     * @return array<non-empty-string, array{0: list<Comment>}>
     */
    public function provideCommentArray(): array
    {
        return [
            'no comment' => [[]],
            'one comment' => [[new Comment('Is this really a spoon?')]],
            'two comments' => [
                [
                    new Comment('I’m a teapot.'),
                    new Comment('I’m a cafetière.'),
                ],
            ],
        ];
    }

    /**
     * @test
     *
     * @param list<Comment> $comments
     *
     * @dataProvider provideCommentArray
     */
    public function addCommentsOnVirginContainerAddsCommentsProvided(array $comments): void
    {
        $this->subject->addComments($comments);

        self::assertSame($comments, $this->subject->getComments());
    }

    /**
     * @test
     *
     * @param list<Comment> $comments
     *
     * @dataProvider provideCommentArray
     */
    public function addCommentsWithEmptyArrayKeepsOriginalCommentsUnchanged(array $comments): void
    {
        $this->subject->setComments($comments);

        $this->subject->addComments([]);

        self::assertSame($comments, $this->subject->getComments());
    }

    /**
     * @return array<non-empty-string, array{0: list<Comment>}>
     */
    public function provideAlternativeCommentArray(): array
    {
        return [
            'no comment' => [[]],
            'one comment' => [[new Comment('Can I eat it with my hands?')]],
            'two comments' => [
                [
                    new Comment('I’m a beer barrel.'),
                    new Comment('I’m a vineyard.'),
                ],
            ],
        ];
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-list<Comment>}>
     */
    public function provideAlternativeNonemptyCommentArray(): array
    {
        $data = $this->provideAlternativeCommentArray();

        unset($data['no comment']);

        return $data;
    }

    /**
     * This provider crosses two comment arrays (0, 1 or 2 comments) with different comments,
     * so that all combinations can be tested.
     *
     * @return DataProvider<non-empty-string, array{0: list<Comment>, 1: list<Comment>}>
     */
    public function provideTwoDistinctCommentArrays(): DataProvider
    {
        return DataProvider::cross($this->provideCommentArray(), $this->provideAlternativeCommentArray());
    }

    /**
     * @return DataProvider<non-empty-string, array{0: list<Comment>, 1: non-empty-list<Comment>}>
     */
    public function provideTwoDistinctCommentArraysWithSecondNonempty(): DataProvider
    {
        return DataProvider::cross($this->provideCommentArray(), $this->provideAlternativeNonemptyCommentArray());
    }

    private static function createContainsConstraint(Comment $comment): TraversableContains
    {
        return new TraversableContains($comment);
    }

    /**
     * @param non-empty-list<Comment> $comments
     *
     * @return non-empty-list<TraversableContains>
     */
    private static function createContainsConstraints(array $comments): array
    {
        return \array_map([self::class, 'createContainsConstraint'], $comments);
    }

    /**
     * @test
     *
     * @param list<Comment> $commentsToAdd
     * @param non-empty-list<Comment> $originalComments
     *
     * @dataProvider provideTwoDistinctCommentArraysWithSecondNonempty
     */
    public function addCommentsKeepsOriginalComments(array $commentsToAdd, array $originalComments): void
    {
        $this->subject->setComments($originalComments);

        $this->subject->addComments($commentsToAdd);

        self::assertThat(
            $this->subject->getComments(),
            LogicalAnd::fromConstraints(...self::createContainsConstraints($originalComments))
        );
    }

    /**
     * @test
     *
     * @param list<Comment> $originalComments
     * @param non-empty-list<Comment> $commentsToAdd
     *
     * @dataProvider provideTwoDistinctCommentArraysWithSecondNonempty
     */
    public function addCommentsAfterCommentsSetAddsCommentsProvided(array $originalComments, array $commentsToAdd): void
    {
        $this->subject->setComments($originalComments);

        $this->subject->addComments($commentsToAdd);

        self::assertThat(
            $this->subject->getComments(),
            LogicalAnd::fromConstraints(...self::createContainsConstraints($commentsToAdd))
        );
    }

    /**
     * @test
     *
     * @param non-empty-list<Comment> $comments
     *
     * @dataProvider provideAlternativeNonemptyCommentArray
     */
    public function addCommentsAppends(array $comments): void
    {
        $firstComment = new Comment('I must be first!');
        $this->subject->setComments([$firstComment]);

        $this->subject->addComments($comments);

        $result = $this->subject->getComments();
        self::assertNotEmpty($result);
        self::assertSame($firstComment, $result[0]);
    }

    /**
     * @test
     *
     * @param list<Comment> $comments
     *
     * @dataProvider provideCommentArray
     */
    public function setCommentsOnVirginContainerSetsCommentsProvided(array $comments): void
    {
        $this->subject->setComments($comments);

        self::assertSame($comments, $this->subject->getComments());
    }

    /**
     * @test
     *
     * @param list<Comment> $originalComments
     * @param list<Comment> $commentsToSet
     *
     * @dataProvider provideTwoDistinctCommentArrays
     */
    public function setCommentsReplacesWithCommentsProvided(array $originalComments, array $commentsToSet): void
    {
        $this->subject->setComments($originalComments);

        $this->subject->setComments($commentsToSet);

        self::assertSame($commentsToSet, $this->subject->getComments());
    }
}
