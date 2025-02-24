<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Parsing;

/**
 * Thrown if the CSS parser encounters a token it did not expect.
 */
class UnexpectedTokenException extends SourceException
{
    /**
     * @var string
     */
    private $expected;

    /**
     * @var string
     */
    private $found;

    /**
     * @var 'literal'|'identifier'|'count'|'expression'|'search'|'custom'
     */
    private $matchType;

    /**
     * @param 'literal'|'identifier'|'count'|'expression'|'search'|'custom' $matchType
     * @param int<0, max> $lineNumber
     */
    public function __construct(string $expected, string $found, string $matchType = 'literal', int $lineNumber = 0)
    {
        $this->expected = $expected;
        $this->found = $found;
        $this->matchType = $matchType;
        $message = "Token “{$expected}” ({$matchType}) not found. Got “{$found}”.";
        if ($this->matchType === 'search') {
            $message = "Search for “{$expected}” returned no results. Context: “{$found}”.";
        } elseif ($this->matchType === 'count') {
            $message = "Next token was expected to have {$expected} chars. Context: “{$found}”.";
        } elseif ($this->matchType === 'identifier') {
            $message = "Identifier expected. Got “{$found}”";
        } elseif ($this->matchType === 'custom') {
            $message = \trim("$expected $found");
        }

        parent::__construct($message, $lineNumber);
    }
}
