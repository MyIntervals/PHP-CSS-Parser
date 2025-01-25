<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Comment;

interface Commentable
{
    /**
     * @param array<array-key, Comment> $aComments
     */
    public function addComments(array $aComments): void;

    /**
     * @return array<array-key, Comment>
     */
    public function getComments();

    /**
     * @param array<array-key, Comment> $aComments
     */
    public function setComments(array $aComments): void;
}
