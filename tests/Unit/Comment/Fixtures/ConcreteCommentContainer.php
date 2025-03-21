<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Comment\Fixtures;

use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\Comment\CommentContainer;

final class ConcreteCommentContainer implements Commentable
{
    use CommentContainer;
}
