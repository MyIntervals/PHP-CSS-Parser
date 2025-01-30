<?php

declare(strict_types=1);

namespace Sabberworm\CSS\CSSList;

use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\RuleSet\RuleSet;
use Sabberworm\CSS\Value\CSSFunction;
use Sabberworm\CSS\Value\Value;
use Sabberworm\CSS\Value\ValueList;

/**
 * A `CSSBlockList` is a `CSSList` whose `DeclarationBlock`s are guaranteed to contain valid declaration blocks or
 * at-rules.
 *
 * Most `CSSList`s conform to this category but some at-rules (such as `@keyframes`) do not.
 */
abstract class CSSBlockList extends CSSList
{
    /**
     * @param int $lineNumber
     */
    public function __construct($lineNumber = 0)
    {
        parent::__construct($lineNumber);
    }

    /**
     * @param array<int, DeclarationBlock> $result
     */
    protected function allDeclarationBlocks(array &$result): void
    {
        foreach ($this->contents as $item) {
            if ($item instanceof DeclarationBlock) {
                $result[] = $item;
            } elseif ($item instanceof CSSBlockList) {
                $item->allDeclarationBlocks($result);
            }
        }
    }

    /**
     * @param array<int, RuleSet> $result
     */
    protected function allRuleSets(array &$result): void
    {
        foreach ($this->contents as $item) {
            if ($item instanceof RuleSet) {
                $result[] = $item;
            } elseif ($item instanceof CSSBlockList) {
                $item->allRuleSets($result);
            }
        }
    }

    /**
     * @param CSSList|Rule|RuleSet|Value $element
     * @param array<int, Value> $result
     * @param string|null $searchString
     * @param bool $searchInFunctionArguments
     */
    protected function allValues(
        $element,
        array &$result,
        $searchString = null,
        $searchInFunctionArguments = false
    ): void {
        if ($element instanceof CSSBlockList) {
            foreach ($element->getContents() as $oContent) {
                $this->allValues($oContent, $result, $searchString, $searchInFunctionArguments);
            }
        } elseif ($element instanceof RuleSet) {
            foreach ($element->getRules($searchString) as $rule) {
                $this->allValues($rule, $result, $searchString, $searchInFunctionArguments);
            }
        } elseif ($element instanceof Rule) {
            $this->allValues($element->getValue(), $result, $searchString, $searchInFunctionArguments);
        } elseif ($element instanceof ValueList) {
            if ($searchInFunctionArguments || !($element instanceof CSSFunction)) {
                foreach ($element->getListComponents() as $mComponent) {
                    $this->allValues($mComponent, $result, $searchString, $searchInFunctionArguments);
                }
            }
        } else {
            // Non-List `Value` or `CSSString` (CSS identifier)
            $result[] = $element;
        }
    }

    /**
     * @param array<int, Selector> $result
     * @param string|null $specificitySearch
     */
    protected function allSelectors(array &$result, $specificitySearch = null): void
    {
        /** @var array<int, DeclarationBlock> $declarationBlocks */
        $declarationBlocks = [];
        $this->allDeclarationBlocks($declarationBlocks);
        foreach ($declarationBlocks as $oBlock) {
            foreach ($oBlock->getSelectors() as $selector) {
                if ($specificitySearch === null) {
                    $result[] = $selector;
                } else {
                    $comparator = '===';
                    $expressionParts = \explode(' ', $specificitySearch);
                    $targetSpecificity = $expressionParts[0];
                    if (\count($expressionParts) > 1) {
                        $comparator = $expressionParts[0];
                        $targetSpecificity = $expressionParts[1];
                    }
                    $targetSpecificity = (int) $targetSpecificity;
                    $selectorSpecificity = $selector->getSpecificity();
                    $comparatorMatched = false;
                    switch ($comparator) {
                        case '<=':
                            $comparatorMatched = $selectorSpecificity <= $targetSpecificity;
                            break;
                        case '<':
                            $comparatorMatched = $selectorSpecificity < $targetSpecificity;
                            break;
                        case '>=':
                            $comparatorMatched = $selectorSpecificity >= $targetSpecificity;
                            break;
                        case '>':
                            $comparatorMatched = $selectorSpecificity > $targetSpecificity;
                            break;
                        default:
                            $comparatorMatched = $selectorSpecificity === $targetSpecificity;
                            break;
                    }
                    if ($comparatorMatched) {
                        $result[] = $selector;
                    }
                }
            }
        }
    }
}
