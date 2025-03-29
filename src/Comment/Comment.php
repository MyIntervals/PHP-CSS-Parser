<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Comment;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\Position\Position;
use Sabberworm\CSS\Position\Positionable;

class Comment implements Positionable, Renderable
{
    use Position;

    /**
     * @var string
     *
     * @internal since 8.8.0
     */
    protected $commentText;

    /**
     * @param int<0, max> $lineNumber
     */
    public function __construct(string $commentText = '', int $lineNumber = 0)
    {
        $this->commentText = $commentText;
        $this->setPosition($lineNumber);
    }

    public function getComment(): string
    {
        return $this->commentText;
    }

    public function setComment(string $commentText): void
    {
        $this->commentText = $commentText;
    }

    /**
     * @return non-empty-string
     */
    public function render(OutputFormat $outputFormat): string
    {
        return '/*' . $this->commentText . '*/';
    }
}
