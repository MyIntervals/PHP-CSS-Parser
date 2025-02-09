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
     * @param string $commentText
     * @param int<0, max> $lineNumber
     */
    public function __construct($commentText = '', $lineNumber = 0)
    {
        $this->commentText = $commentText;
        $this->lineNumber = $lineNumber;
    }

    /**
     * @return string
     */
    public function getComment()
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

    /**
     * @param string $commentText
     */
    public function setComment($commentText): void
    {
        $this->commentText = $commentText;
    }

    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $outputFormat): string
    {
        return '/*' . $this->commentText . '*/';
    }
}
