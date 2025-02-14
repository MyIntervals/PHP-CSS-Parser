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
    private $sExpected;

    /**
     * @var string
     */
    private $sFound;

    /**
     * @var 'literal'|'identifier'|'count'|'expression'|'search'|'custom'
     */
    private $sMatchType;

    /**
     * @param 'literal'|'identifier'|'count'|'expression'|'search'|'custom' $sMatchType
     * @param int<0, max> $lineNumber
     */
    public function __construct(string $sExpected, string $sFound, string $sMatchType = 'literal', int $lineNumber = 0)
    {
        $this->sExpected = $sExpected;
        $this->sFound = $sFound;
        $this->sMatchType = $sMatchType;
        $sMessage = "Token “{$sExpected}” ({$sMatchType}) not found. Got “{$sFound}”.";
        if ($this->sMatchType === 'search') {
            $sMessage = "Search for “{$sExpected}” returned no results. Context: “{$sFound}”.";
        } elseif ($this->sMatchType === 'count') {
            $sMessage = "Next token was expected to have {$sExpected} chars. Context: “{$sFound}”.";
        } elseif ($this->sMatchType === 'identifier') {
            $sMessage = "Identifier expected. Got “{$sFound}”";
        } elseif ($this->sMatchType === 'custom') {
            $sMessage = \trim("$sExpected $sFound");
        }

        parent::__construct($sMessage, $lineNumber);
    }
}
