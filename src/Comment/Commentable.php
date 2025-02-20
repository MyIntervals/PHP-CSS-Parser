<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Comment;

interface Commentable
{
    /**
     * @param array<array-key, Comment> $comments
     */
    public function addComments(array $comments): void;

    /**
     * @return array<array-key, Comment>
     */
    public function getComments(): array;

    /**
     * @param array<array-key, Comment> $comments
     */
    public function setComments(array $comments): void;
}
