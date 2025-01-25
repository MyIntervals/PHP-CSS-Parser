<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Comment;

interface Commentable
{
    /**
     * @param array<array-key, Comment> $comments
     *
     * @return void
     */
    public function addComments(array $comments);

    /**
     * @return array<array-key, Comment>
     */
    public function getComments();

    /**
     * @param array<array-key, Comment> $comments
     *
     * @return void
     */
    public function setComments(array $comments);
}
