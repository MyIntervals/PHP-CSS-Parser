<?php

declare(strict_types=1);

namespace Sabberworm\CSS\CSSList;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\RuleSet\RuleSet;
use Sabberworm\CSS\Value\Value;

/**
 * This class represents the root of a parsed CSS file. It contains all top-level CSS contents: mostly declaration
 * blocks, but also any at-rules encountered (`Import` and `Charset`).
 */
class Document extends CSSBlockList
{
    /**
     * @param int<0, max> $lineNumber
     */
    public function __construct($lineNumber = 0)
    {
        parent::__construct($lineNumber);
    }

    /**
     * @throws SourceException
     *
     * @internal since V8.8.0
     */
    public static function parse(ParserState $parserState): Document
    {
        $oDocument = new Document($parserState->currentLine());
        CSSList::parseList($parserState, $oDocument);
        return $oDocument;
    }

    /**
     * Gets all `DeclarationBlock` objects recursively, no matter how deeply nested the selectors are.
     * Aliased as `getAllSelectors()`.
     *
     * @return array<int, DeclarationBlock>
     */
    public function getAllDeclarationBlocks(): array
    {
        /** @var array<int, DeclarationBlock> $result */
        $result = [];
        $this->allDeclarationBlocks($result);
        return $result;
    }

    /**
     * Returns all `RuleSet` objects recursively found in the tree, no matter how deeply nested the rule sets are.
     *
     * @return array<int, RuleSet>
     */
    public function getAllRuleSets(): array
    {
        /** @var array<int, RuleSet> $result */
        $result = [];
        $this->allRuleSets($result);
        return $result;
    }

    /**
     * Returns all `Value` objects found recursively in `Rule`s in the tree.
     *
     * @param CSSList|RuleSet|string $element
     *        the `CSSList` or `RuleSet` to start the search from (defaults to the whole document).
     *        If a string is given, it is used as rule name filter.
     * @param bool $searchInFunctionArguments whether to also return Value objects used as Function arguments.
     *
     * @return array<int, Value>
     *
     * @see RuleSet->getRules()
     */
    public function getAllValues($element = null, $searchInFunctionArguments = false): array
    {
        $searchString = null;
        if ($element === null) {
            $element = $this;
        } elseif (\is_string($element)) {
            $searchString = $element;
            $element = $this;
        }
        /** @var array<int, Value> $result */
        $result = [];
        $this->allValues($element, $result, $searchString, $searchInFunctionArguments);
        return $result;
    }

    /**
     * Returns all `Selector` objects with the requested specificity found recursively in the tree.
     *
     * Note that this does not yield the full `DeclarationBlock` that the selector belongs to
     * (and, currently, there is no way to get to that).
     *
     * @param string|null $specificitySearch
     *        An optional filter by specificity.
     *        May contain a comparison operator and a number or just a number (defaults to "==").
     *
     * @return array<int, Selector>
     * @example `getSelectorsBySpecificity('>= 100')`
     */
    public function getSelectorsBySpecificity($specificitySearch = null): array
    {
        /** @var array<int, Selector> $result */
        $result = [];
        $this->allSelectors($result, $specificitySearch);
        return $result;
    }

    /**
     * Overrides `render()` to make format argument optional.
     */
    public function render(?OutputFormat $outputFormat = null): string
    {
        if ($outputFormat === null) {
            $outputFormat = new OutputFormat();
        }
        return $outputFormat->comments($this) . $this->renderListContents($outputFormat);
    }

    public function isRootList(): bool
    {
        return true;
    }
}
