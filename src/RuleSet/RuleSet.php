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
     * the rules in this rule set, using the property name as the key,
     * with potentially multiple rules per property name.
     *
     * @var array<string, array<int<0, max>, Rule>>
     */
    private $rules = [];

    /**
     * @var int<0, max>
     *
     * @internal since 8.8.0
     */
    protected $lineNumber;

    /**
     * @var list<Comment>
     *
     * @internal since 8.8.0
     */
    protected $comments = [];

    /**
     * @param int<0, max> $lineNumber
     */
    public function __construct(int $lineNumber = 0)
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
        while (true) {
            $commentsBeforeRule = $parserState->consumeWhiteSpace();
            if ($parserState->comes('}')) {
                break;
            }
            $rule = null;
            if ($parserState->getSettings()->usesLenientParsing()) {
                try {
                    $rule = Rule::parse($parserState, $commentsBeforeRule);
                } catch (UnexpectedTokenException $e) {
                    try {
                        $consumedText = $parserState->consumeUntil(["\n", ';', '}'], true);
                        // We need to “unfind” the matches to the end of the ruleSet as this will be matched later
                        if ($parserState->streql(\substr($consumedText, -1), '}')) {
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
                $rule = Rule::parse($parserState, $commentsBeforeRule);
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

    public function addRule(Rule $ruleToAdd, ?Rule $sibling = null): void
    {
        $propertyName = $ruleToAdd->getRule();
        if (!isset($this->rules[$propertyName])) {
            $this->rules[$propertyName] = [];
        }

        $position = \count($this->rules[$propertyName]);

        if ($sibling !== null) {
            $siblingPosition = \array_search($sibling, $this->rules[$propertyName], true);
            if ($siblingPosition !== false) {
                $position = $siblingPosition;
                $ruleToAdd->setPosition($sibling->getLineNo(), $sibling->getColNo() - 1);
            }
        }
        if ($ruleToAdd->getLineNo() === 0 && $ruleToAdd->getColNo() === 0) {
            //this node is added manually, give it the next best line
            $rules = $this->getRules();
            $rulesCount = \count($rules);
            if ($rulesCount > 0) {
                $last = $rules[$rulesCount - 1];
                $ruleToAdd->setPosition($last->getLineNo() + 1, 0);
            }
        }

        \array_splice($this->rules[$propertyName], $position, 0, [$ruleToAdd]);
    }

    /**
     * Returns all rules matching the given rule name
     *
     * @example $ruleSet->getRules('font') // returns array(0 => $rule, …) or array().
     *
     * @example $ruleSet->getRules('font-')
     *          //returns an array of all rules either beginning with font- or matching font.
     *
     * @param Rule|string|null $searchPattern
     *        Pattern to search for. If null, returns all rules.
     *        If the pattern ends with a dash, all rules starting with the pattern are returned
     *        as well as one matching the pattern with the dash excluded.
     *        Passing a `Rule` behaves like calling `getRules($rule->getRule())`.
     *
     * @return array<int<0, max>, Rule>
     */
    public function getRules($searchPattern = null): array
    {
        if ($searchPattern instanceof Rule) {
            $searchPattern = $searchPattern->getRule();
        }
        $result = [];
        foreach ($this->rules as $propertyName => $rules) {
            // Either no search rule is given or the search rule matches the found rule exactly
            // or the search rule ends in “-” and the found rule starts with the search rule.
            if (
                !$searchPattern || $propertyName === $searchPattern
                || (
                    \strrpos($searchPattern, '-') === \strlen($searchPattern) - \strlen('-')
                    && (\strpos($propertyName, $searchPattern) === 0
                        || $propertyName === \substr($searchPattern, 0, -1))
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
     * @param array<Rule> $rules The rules to override with.
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
     * @param Rule|string|null $searchPattern
     *        Pattern to search for. If null, returns all rules. If the pattern ends with a dash,
     *        all rules starting with the pattern are returned as well as one matching the pattern with the dash
     *        excluded. Passing a `Rule` behaves like calling `getRules($rule->getRule())`.
     *
     * @return array<string, Rule>
     */
    public function getRulesAssoc($searchPattern = null): array
    {
        /** @var array<string, Rule> $result */
        $result = [];
        foreach ($this->getRules($searchPattern) as $rule) {
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
     * @param Rule|string|null $searchPattern
     *        pattern to remove. If null, all rules are removed. If the pattern ends in a dash,
     *        all rules starting with the pattern are removed as well as one matching the pattern with the dash
     *        excluded. Passing a Rule behaves matches by identity.
     */
    public function removeRule($searchPattern): void
    {
        if ($searchPattern instanceof Rule) {
            $nameOfPropertyToRemove = $searchPattern->getRule();
            if (!isset($this->rules[$nameOfPropertyToRemove])) {
                return;
            }
            foreach ($this->rules[$nameOfPropertyToRemove] as $key => $rule) {
                if ($rule === $searchPattern) {
                    unset($this->rules[$nameOfPropertyToRemove][$key]);
                }
            }
        } else {
            foreach ($this->rules as $propertyName => $rules) {
                // Either no search rule is given or the search rule matches the found rule exactly
                // or the search rule ends in “-” and the found rule starts with the search rule or equals it
                // (without the trailing dash).
                if (
                    !$searchPattern || $propertyName === $searchPattern
                    || (\strrpos($searchPattern, '-') === \strlen($searchPattern) - \strlen('-')
                        && (\strpos($propertyName, $searchPattern) === 0
                            || $propertyName === \substr($searchPattern, 0, -1)))
                ) {
                    unset($this->rules[$propertyName]);
                }
            }
        }
    }

    protected function renderRules(OutputFormat $outputFormat): string
    {
        $result = '';
        $isFirst = true;
        $nextLevelFormat = $outputFormat->nextLevel();
        foreach ($this->getRules() as $rule) {
            $nextLevelFormatter = $nextLevelFormat->getFormatter();
            $renderedRule = $nextLevelFormatter->safely(static function () use ($rule, $nextLevelFormat): string {
                return $rule->render($nextLevelFormat);
            });
            if ($renderedRule === null) {
                continue;
            }
            if ($isFirst) {
                $isFirst = false;
                $result .= $nextLevelFormatter->spaceBeforeRules();
            } else {
                $result .= $nextLevelFormatter->spaceBetweenRules();
            }
            $result .= $renderedRule;
        }

        $formatter = $outputFormat->getFormatter();
        if (!$isFirst) {
            // Had some output
            $result .= $formatter->spaceAfterRules();
        }

        return $formatter->removeLastSemicolon($result);
    }

    /**
     * @param list<Comment> $comments
     */
    public function addComments(array $comments): void
    {
        $this->comments = \array_merge($this->comments, $comments);
    }

    /**
     * @return list<Comment>
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    /**
     * @param list<Comment> $comments
     */
    public function setComments(array $comments): void
    {
        $this->comments = $comments;
    }
}
