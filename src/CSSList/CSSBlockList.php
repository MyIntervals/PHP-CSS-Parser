<?php

declare(strict_types=1);

namespace Sabberworm\CSS\CSSList;

use Sabberworm\CSS\CSSElement;
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
     * Gets all `DeclarationBlock` objects recursively, no matter how deeply nested the selectors are.
     *
     * @return list<DeclarationBlock>
     */
    public function getAllDeclarationBlocks(): array
    {
        $result = [];

        foreach ($this->contents as $item) {
            if ($item instanceof DeclarationBlock) {
                $result[] = $item;
            } elseif ($item instanceof CSSBlockList) {
                $result = \array_merge($result, $item->getAllDeclarationBlocks());
            }
        }

        return $result;
    }

    /**
     * Returns all `RuleSet` objects recursively found in the tree, no matter how deeply nested the rule sets are.
     *
     * @return list<RuleSet>
     */
    public function getAllRuleSets(): array
    {
        $result = [];

        foreach ($this->contents as $item) {
            if ($item instanceof RuleSet) {
                $result[] = $item;
            } elseif ($item instanceof CSSBlockList) {
                $result = \array_merge($result, $item->getAllRuleSets());
            }
        }

        return $result;
    }

    /**
     * Returns all `Value` objects found recursively in `Rule`s in the tree.
     *
     * @param CSSElement|string|null $element
     *        This is the `CSSList` or `RuleSet` to start the search from (defaults to the whole document).
     *        If a string is given, it is used as a rule name filter.
     *        Passing a string for this parameter is deprecated in version 8.9.0, and will not work from v9.0;
     *        use the following parameter to pass a rule name filter instead.
     * @param string|bool|null $ruleSearchPatternOrSearchInFunctionArguments
     *        This allows filtering rules by property name
     *        (e.g. if "color" is passed, only `Value`s from `color` properties will be returned,
     *        or if "font-" is provided, `Value`s from all font rules, like `font-size`, and including `font` itself,
     *        will be returned).
     *        If a Boolean is provided, it is treated as the `$searchInFunctionArguments` argument.
     *        Passing a Boolean for this parameter is deprecated in version 8.9.0, and will not work from v9.0;
     *        use the `$searchInFunctionArguments` parameter instead.
     * @param bool $searchInFunctionArguments whether to also return Value objects used as Function arguments.
     *
     * @return array<int, Value>
     *
     * @see RuleSet->getRules()
     */
    public function getAllValues(
        $element = null,
        $ruleSearchPatternOrSearchInFunctionArguments = null,
        bool $searchInFunctionArguments = false
    ): array {
        if (\is_bool($ruleSearchPatternOrSearchInFunctionArguments)) {
            $searchInFunctionArguments = $ruleSearchPatternOrSearchInFunctionArguments;
            $searchString = null;
        } else {
            $searchString = $ruleSearchPatternOrSearchInFunctionArguments;
        }

        if ($element === null) {
            $element = $this;
        } elseif (\is_string($element)) {
            $searchString = $element;
            $element = $this;
        }

        $result = [];
        $this->allValues($element, $result, $searchString, $searchInFunctionArguments);
        return $result;
    }

    /**
     * @param CSSElement|string $element
     * @param list<Value> $result
     */
    protected function allValues(
        $element,
        array &$result,
        ?string $searchString = null,
        bool $searchInFunctionArguments = false
    ): void {
        if ($element instanceof CSSBlockList) {
            foreach ($element->getContents() as $content) {
                $this->allValues($content, $result, $searchString, $searchInFunctionArguments);
            }
        } elseif ($element instanceof RuleSet) {
            foreach ($element->getRules($searchString) as $rule) {
                $this->allValues($rule, $result, $searchString, $searchInFunctionArguments);
            }
        } elseif ($element instanceof Rule) {
            $this->allValues($element->getValue(), $result, $searchString, $searchInFunctionArguments);
        } elseif ($element instanceof ValueList) {
            if ($searchInFunctionArguments || !($element instanceof CSSFunction)) {
                foreach ($element->getListComponents() as $component) {
                    $this->allValues($component, $result, $searchString, $searchInFunctionArguments);
                }
            }
        } elseif ($element instanceof Value) {
            $result[] = $element;
        }
    }

    /**
     * @param list<Selector> $result
     */
    protected function allSelectors(array &$result, ?string $specificitySearch = null): void
    {
        foreach ($this->getAllDeclarationBlocks() as $declarationBlock) {
            foreach ($declarationBlock->getSelectors() as $selector) {
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
