<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Comment;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Renderable;

class Comment implements Renderable
{
    /**
     * @var int<0, max>
     *
     * @internal since 8.8.0
     */
    protected $lineNumber;

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
        $this->lineNumber = $lineNumber;
    }

    public function getComment(): string
    {
        return $this->commentText;
    }

    /**
     * @return int<0, max>
     */
    public function getLineNo(): int
    {
        return $this->lineNumber;
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
