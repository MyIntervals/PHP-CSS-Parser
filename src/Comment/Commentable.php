<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Comment;

interface Commentable
{
    /**
     * @param array<int<0, max>, Comment> $comments
     */
    public function addComments(array $comments): void;

    /**
     * @return list<Comment>
     */
    public function getComments(): array;

    /**
     * @param list<Comment> $comments
     */
    public function setComments(array $comments): void;
}
