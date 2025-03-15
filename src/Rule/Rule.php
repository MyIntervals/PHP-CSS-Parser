<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Rule;

use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\Value\RuleValueList;
use Sabberworm\CSS\Value\Value;

/**
 * `Rule`s just have a string key (the rule) and a 'Value'.
 *
 * In CSS, `Rule`s are expressed as follows: “key: value[0][0] value[0][1], value[1][0] value[1][1];”
 */
class Rule implements Renderable, Commentable
{
    /**
     * @var string
     */
    private $rule;

    /**
     * @var RuleValueList|string|null
     */
    private $value;

    /**
     * @var bool
     */
    private $isImportant = false;

    /**
     * @var int<0, max> $lineNumber
     */
    protected $lineNumber;

    /**
     * @var int
     *
     * @internal since 8.8.0
     */
    protected $columnNumber;

    /**
     * @var array<array-key, Comment>
     *
     * @internal since 8.8.0
     */
    protected $comments = [];

    /**
     * @param string $rule
     * @param int<0, max> $lineNumber
     * @param int $columnNumber
     */
    public function __construct($rule, int $lineNumber = 0, $columnNumber = 0)
    {
        $this->rule = $rule;
        $this->lineNumber = $lineNumber;
        $this->columnNumber = $columnNumber;
    }

    /**
     * @param list<Comment> $commentsBeforeRule
     *
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     *
     * @internal since V8.8.0
     */
    public static function parse(ParserState $parserState, array $commentsBeforeRule = []): Rule
    {
        $comments = \array_merge($commentsBeforeRule, $parserState->consumeWhiteSpace());
        $rule = new Rule(
            $parserState->parseIdentifier(!$parserState->comes('--')),
            $parserState->currentLine(),
            $parserState->currentColumn()
        );
        $rule->setComments($comments);
        $rule->addComments($parserState->consumeWhiteSpace());
        $parserState->consume(':');
        $value = Value::parseValue($parserState, self::listDelimiterForRule($rule->getRule()));
        $rule->setValue($value);
        $parserState->consumeWhiteSpace();
        if ($parserState->comes('!')) {
            $parserState->consume('!');
            $parserState->consumeWhiteSpace();
            $parserState->consume('important');
            $rule->setIsImportant(true);
        }
        $parserState->consumeWhiteSpace();
        while ($parserState->comes(';')) {
            $parserState->consume(';');
        }

        return $rule;
    }

    /**
     * Returns a list of delimiters (or separators).
     * The first item is the innermost separator (or, put another way, the highest-precedence operator).
     * The sequence continues to the outermost separator (or lowest-precedence operator).
     *
     * @param string $rule
     *
     * @return list<non-empty-string>
     */
    private static function listDelimiterForRule($rule): array
    {
        if (\preg_match('/^font($|-)/', $rule)) {
            return [',', '/', ' '];
        }

        switch ($rule) {
            case 'src':
                return [' ', ','];
            default:
                return [',', ' ', '/'];
        }
    }

    /**
     * @return int<0, max>
     */
    public function getLineNo(): int
    {
        return $this->lineNumber;
    }

    /**
     * @return int
     */
    public function getColNo()
    {
        return $this->columnNumber;
    }

    /**
     * @param int<0, max> $lineNumber
     * @param int $columnNumber
     */
    public function setPosition(int $lineNumber, $columnNumber): void
    {
        $this->columnNumber = $columnNumber;
        $this->lineNumber = $lineNumber;
    }

    /**
     * @param string $rule
     */
    public function setRule($rule): void
    {
        $this->rule = $rule;
    }

    /**
     * @return string
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * @return RuleValueList|string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param RuleValueList|string|null $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * Adds a value to the existing value. Value will be appended if a `RuleValueList` exists of the given type.
     * Otherwise, the existing value will be wrapped by one.
     *
     * @param RuleValueList|array<int, RuleValueList> $value
     * @param string $type
     */
    public function addValue($value, $type = ' '): void
    {
        if (!\is_array($value)) {
            $value = [$value];
        }
        if (!($this->value instanceof RuleValueList) || $this->value->getListSeparator() !== $type) {
            $currentValue = $this->value;
            $this->value = new RuleValueList($type, $this->lineNumber);
            if ($currentValue) {
                $this->value->addListComponent($currentValue);
            }
        }
        foreach ($value as $valueItem) {
            $this->value->addListComponent($valueItem);
        }
    }

    /**
     * @param bool $isImportant
     */
    public function setIsImportant($isImportant): void
    {
        $this->isImportant = $isImportant;
    }

    /**
     * @return bool
     */
    public function getIsImportant()
    {
        return $this->isImportant;
    }

    /**
     * @deprecated in V8.8.0, will be removed in V9.0.0. Use `render` instead.
     */
    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $outputFormat): string
    {
        $formatter = $outputFormat->getFormatter();
        $result = "{$formatter->comments($this)}{$this->rule}:{$formatter->spaceAfterRuleName()}";
        if ($this->value instanceof Value) { // Can also be a ValueList
            $result .= $this->value->render($outputFormat);
        } else {
            $result .= $this->value;
        }
        if ($this->isImportant) {
            $result .= ' !important';
        }
        $result .= ';';
        return $result;
    }

    /**
     * @param array<array-key, Comment> $comments
     */
    public function addComments(array $comments): void
    {
        $this->comments = \array_merge($this->comments, $comments);
    }

    /**
     * @return array<array-key, Comment>
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    /**
     * @param array<array-key, Comment> $comments
     */
    public function setComments(array $comments): void
    {
        $this->comments = $comments;
    }
}
