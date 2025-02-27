<?php

declare(strict_types=1);

namespace Sabberworm\CSS\RuleSet;

use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\Rule\Rule;

/**
 * This class is a container for individual 'Rule's.
 *
 * The most common form of a rule set is one constrained by a selector, i.e., a `DeclarationBlock`.
 * However, unknown `AtRule`s (like `@font-face`) are rule sets as well.
 *
 * If you want to manipulate a `RuleSet`, use the methods `addRule(Rule $rule)`, `getRules()` and `removeRule($rule)`
 * (which accepts either a `Rule` or a rule name; optionally suffixed by a dash to remove all related rules).
 */
abstract class RuleSet implements Renderable, Commentable
{
    /**
     * @var array<string, Rule>
     */
    private $rules = [];

    /**
     * @var int<0, max>
     *
     * @internal since 8.8.0
     */
    protected $lineNumber;

    /**
     * @var array<array-key, Comment>
     *
     * @internal since 8.8.0
     */
    protected $comments = [];

    /**
     * @param int<0, max> $lineNumber
     */
    public function __construct($lineNumber = 0)
    {
        $this->lineNumber = $lineNumber;
    }

    /**
     * @throws UnexpectedTokenException
     * @throws UnexpectedEOFException
     *
     * @internal since V8.8.0
     */
    public static function parseRuleSet(ParserState $parserState, RuleSet $ruleSet): void
    {
        while ($parserState->comes(';')) {
            $parserState->consume(';');
        }
        while (!$parserState->comes('}')) {
            $rule = null;
            if ($parserState->getSettings()->usesLenientParsing()) {
                try {
                    $rule = Rule::parse($parserState);
                } catch (UnexpectedTokenException $e) {
                    try {
                        $sConsume = $parserState->consumeUntil(["\n", ';', '}'], true);
                        // We need to “unfind” the matches to the end of the ruleSet as this will be matched later
                        if ($parserState->streql(\substr($sConsume, -1), '}')) {
                            $parserState->backtrack(1);
                        } else {
                            while ($parserState->comes(';')) {
                                $parserState->consume(';');
                            }
                        }
                    } catch (UnexpectedTokenException $e) {
                        // We’ve reached the end of the document. Just close the RuleSet.
                        return;
                    }
                }
            } else {
                $rule = Rule::parse($parserState);
            }
            if ($rule instanceof Rule) {
                $ruleSet->addRule($rule);
            }
        }
        $parserState->consume('}');
    }

    /**
     * @return int<0, max>
     */
    public function getLineNo(): int
    {
        return $this->lineNumber;
    }

    public function addRule(Rule $rule, ?Rule $oSibling = null): void
    {
        $sRule = $rule->getRule();
        if (!isset($this->rules[$sRule])) {
            $this->rules[$sRule] = [];
        }

        $position = \count($this->rules[$sRule]);

        if ($oSibling !== null) {
            $iSiblingPos = \array_search($oSibling, $this->rules[$sRule], true);
            if ($iSiblingPos !== false) {
                $position = $iSiblingPos;
                $rule->setPosition($oSibling->getLineNo(), $oSibling->getColNo() - 1);
            }
        }
        if ($rule->getLineNo() === 0 && $rule->getColNo() === 0) {
            //this node is added manually, give it the next best line
            $rules = $this->getRules();
            $pos = \count($rules);
            if ($pos > 0) {
                $last = $rules[$pos - 1];
                $rule->setPosition($last->getLineNo() + 1, 0);
            }
        }

        \array_splice($this->rules[$sRule], $position, 0, [$rule]);
    }

    /**
     * Returns all rules matching the given rule name
     *
     * @example $ruleSet->getRules('font') // returns array(0 => $rule, …) or array().
     *
     * @example $ruleSet->getRules('font-')
     *          //returns an array of all rules either beginning with font- or matching font.
     *
     * @param Rule|string|null $mRule
     *        Pattern to search for. If null, returns all rules.
     *        If the pattern ends with a dash, all rules starting with the pattern are returned
     *        as well as one matching the pattern with the dash excluded.
     *        Passing a Rule behaves like calling `getRules($mRule->getRule())`.
     *
     * @return array<int, Rule>
     */
    public function getRules($mRule = null)
    {
        if ($mRule instanceof Rule) {
            $mRule = $mRule->getRule();
        }
        /** @var array<int, Rule> $result */
        $result = [];
        foreach ($this->rules as $sName => $rules) {
            // Either no search rule is given or the search rule matches the found rule exactly
            // or the search rule ends in “-” and the found rule starts with the search rule.
            if (
                !$mRule || $sName === $mRule
                || (
                    \strrpos($mRule, '-') === \strlen($mRule) - \strlen('-')
                    && (\strpos($sName, $mRule) === 0 || $sName === \substr($mRule, 0, -1))
                )
            ) {
                $result = \array_merge($result, $rules);
            }
        }
        \usort($result, static function (Rule $first, Rule $second): int {
            if ($first->getLineNo() === $second->getLineNo()) {
                return $first->getColNo() - $second->getColNo();
            }
            return $first->getLineNo() - $second->getLineNo();
        });
        return $result;
    }

    /**
     * Overrides all the rules of this set.
     *
     * @param array<array-key, Rule> $rules The rules to override with.
     */
    public function setRules(array $rules): void
    {
        $this->rules = [];
        foreach ($rules as $rule) {
            $this->addRule($rule);
        }
    }

    /**
     * Returns all rules matching the given pattern and returns them in an associative array with the rule’s name
     * as keys. This method exists mainly for backwards-compatibility and is really only partially useful.
     *
     * Note: This method loses some information: Calling this (with an argument of `background-`) on a declaration block
     * like `{ background-color: green; background-color; rgba(0, 127, 0, 0.7); }` will only yield an associative array
     * containing the rgba-valued rule while `getRules()` would yield an indexed array containing both.
     *
     * @param Rule|string|null $mRule $mRule
     *        Pattern to search for. If null, returns all rules. If the pattern ends with a dash,
     *        all rules starting with the pattern are returned as well as one matching the pattern with the dash
     *        excluded. Passing a Rule behaves like calling `getRules($mRule->getRule())`.
     *
     * @return array<string, Rule>
     */
    public function getRulesAssoc($mRule = null)
    {
        /** @var array<string, Rule> $result */
        $result = [];
        foreach ($this->getRules($mRule) as $rule) {
            $result[$rule->getRule()] = $rule;
        }
        return $result;
    }

    /**
     * Removes a rule from this RuleSet. This accepts all the possible values that `getRules()` accepts.
     *
     * If given a Rule, it will only remove this particular rule (by identity).
     * If given a name, it will remove all rules by that name.
     *
     * Note: this is different from pre-v.2.0 behaviour of PHP-CSS-Parser, where passing a Rule instance would
     * remove all rules with the same name. To get the old behaviour, use `removeRule($rule->getRule())`.
     *
     * @param Rule|string|null $mRule
     *        pattern to remove. If $mRule is null, all rules are removed. If the pattern ends in a dash,
     *        all rules starting with the pattern are removed as well as one matching the pattern with the dash
     *        excluded. Passing a Rule behaves matches by identity.
     */
    public function removeRule($mRule): void
    {
        if ($mRule instanceof Rule) {
            $sRule = $mRule->getRule();
            if (!isset($this->rules[$sRule])) {
                return;
            }
            foreach ($this->rules[$sRule] as $key => $rule) {
                if ($rule === $mRule) {
                    unset($this->rules[$sRule][$key]);
                }
            }
        } else {
            foreach ($this->rules as $sName => $rules) {
                // Either no search rule is given or the search rule matches the found rule exactly
                // or the search rule ends in “-” and the found rule starts with the search rule or equals it
                // (without the trailing dash).
                if (
                    !$mRule || $sName === $mRule
                    || (\strrpos($mRule, '-') === \strlen($mRule) - \strlen('-')
                        && (\strpos($sName, $mRule) === 0 || $sName === \substr($mRule, 0, -1)))
                ) {
                    unset($this->rules[$sName]);
                }
            }
        }
    }

    /**
     * @deprecated in V8.8.0, will be removed in V9.0.0. Use `render` instead.
     */
    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    /**
     * @return string
     */
    protected function renderRules(OutputFormat $outputFormat)
    {
        $result = '';
        $isFirst = true;
        $oNextLevel = $outputFormat->nextLevel();
        foreach ($this->rules as $rules) {
            foreach ($rules as $rule) {
                $sRendered = $oNextLevel->safely(static function () use ($rule, $oNextLevel): string {
                    return $rule->render($oNextLevel);
                });
                if ($sRendered === null) {
                    continue;
                }
                if ($isFirst) {
                    $isFirst = false;
                    $result .= $oNextLevel->spaceBeforeRules();
                } else {
                    $result .= $oNextLevel->spaceBetweenRules();
                }
                $result .= $sRendered;
            }
        }

        if (!$isFirst) {
            // Had some output
            $result .= $outputFormat->spaceAfterRules();
        }

        return $outputFormat->removeLastSemicolon($result);
    }

    /**
     * @param array<string, Comment> $comments
     */
    public function addComments(array $comments): void
    {
        $this->comments = \array_merge($this->comments, $comments);
    }

    /**
     * @return array<string, Comment>
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    /**
     * @param array<string, Comment> $comments
     */
    public function setComments(array $comments): void
    {
        $this->comments = $comments;
    }
}
