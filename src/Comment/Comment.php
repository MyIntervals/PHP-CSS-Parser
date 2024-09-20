<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Comment;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Renderable;

class Comment implements Renderable
{
    /**
     * @var int
     */
    protected $lineNumber;

    /**
     * @var string
     */
    protected $commentText;

    /**
     * @param string $commentText
     * @param int $lineNumber
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
     * @return int
     */
    public function getLineNo()
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
