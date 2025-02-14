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
    private $sRule;

    /**
     * @var RuleValueList|string|null
     */
    private $mValue;

    /**
     * @var bool
     */
    private $bIsImportant;

    /**
     * @var array<int, int>
     */
    private $aIeHack;

    /**
     * @var int
     */
    protected $lineNumber;

    /**
     * @var int
     *
     * @internal since 8.8.0
     */
    protected $iColNo;

    /**
     * @var array<array-key, Comment>
     *
     * @internal since 8.8.0
     */
    protected $comments;

    /**
     * @param string $sRule
     * @param int<0, max> $lineNumber
     * @param int $iColNo
     */
    public function __construct($sRule, $lineNumber = 0, $iColNo = 0)
    {
        $this->sRule = $sRule;
        $this->mValue = null;
        $this->bIsImportant = false;
        $this->aIeHack = [];
        $this->lineNumber = $lineNumber;
        $this->iColNo = $iColNo;
        $this->comments = [];
    }

    /**
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     *
     * @internal since V8.8.0
     */
    public static function parse(ParserState $parserState): Rule
    {
        $comments = $parserState->consumeWhiteSpace();
        $rule = new Rule(
            $parserState->parseIdentifier(!$parserState->comes('--')),
            $parserState->currentLine(),
            $parserState->currentColumn()
        );
        $rule->setComments($comments);
        $rule->addComments($parserState->consumeWhiteSpace());
        $parserState->consume(':');
        $oValue = Value::parseValue($parserState, self::listDelimiterForRule($rule->getRule()));
        $rule->setValue($oValue);
        if ($parserState->getSettings()->bLenientParsing) {
            while ($parserState->comes('\\')) {
                $parserState->consume('\\');
                $rule->addIeHack($parserState->consume());
                $parserState->consumeWhiteSpace();
            }
        }
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

        $parserState->consumeWhiteSpace();

        return $rule;
    }

    /**
     * Returns a list of delimiters (or separators).
     * The first item is the innermost separator (or, put another way, the highest-precedence operator).
     * The sequence continues to the outermost separator (or lowest-precedence operator).
     *
     * @param string $sRule
     *
     * @return list<non-empty-string>
     */
    private static function listDelimiterForRule($sRule): array
    {
        if (\preg_match('/^font($|-)/', $sRule)) {
            return [',', '/', ' '];
        }

        switch ($sRule) {
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
        return $this->iColNo;
    }

    /**
     * @param int $iLine
     * @param int $iColumn
     */
    public function setPosition($iLine, $iColumn): void
    {
        $this->iColNo = $iColumn;
        $this->lineNumber = $iLine;
    }

    /**
     * @param string $sRule
     */
    public function setRule($sRule): void
    {
        $this->sRule = $sRule;
    }

    /**
     * @return string
     */
    public function getRule()
    {
        return $this->sRule;
    }

    /**
     * @return RuleValueList|string|null
     */
    public function getValue()
    {
        return $this->mValue;
    }

    /**
     * @param RuleValueList|string|null $mValue
     */
    public function setValue($mValue): void
    {
        $this->mValue = $mValue;
    }

    /**
     * Adds a value to the existing value. Value will be appended if a `RuleValueList` exists of the given type.
     * Otherwise, the existing value will be wrapped by one.
     *
     * @param RuleValueList|array<int, RuleValueList> $mValue
     * @param string $sType
     */
    public function addValue($mValue, $sType = ' '): void
    {
        if (!\is_array($mValue)) {
            $mValue = [$mValue];
        }
        if (!($this->mValue instanceof RuleValueList) || $this->mValue->getListSeparator() !== $sType) {
            $mCurrentValue = $this->mValue;
            $this->mValue = new RuleValueList($sType, $this->lineNumber);
            if ($mCurrentValue) {
                $this->mValue->addListComponent($mCurrentValue);
            }
        }
        foreach ($mValue as $mValueItem) {
            $this->mValue->addListComponent($mValueItem);
        }
    }

    /**
     * @param int $iModifier
     */
    public function addIeHack($iModifier): void
    {
        $this->aIeHack[] = $iModifier;
    }

    /**
     * @param array<int, int> $aModifiers
     */
    public function setIeHack(array $aModifiers): void
    {
        $this->aIeHack = $aModifiers;
    }

    /**
     * @return array<int, int>
     */
    public function getIeHack()
    {
        return $this->aIeHack;
    }

    /**
     * @param bool $bIsImportant
     */
    public function setIsImportant($bIsImportant): void
    {
        $this->bIsImportant = $bIsImportant;
    }

    /**
     * @return bool
     */
    public function getIsImportant()
    {
        return $this->bIsImportant;
    }

    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $outputFormat): string
    {
        $result = "{$outputFormat->comments($this)}{$this->sRule}:{$outputFormat->spaceAfterRuleName()}";
        if ($this->mValue instanceof Value) { // Can also be a ValueList
            $result .= $this->mValue->render($outputFormat);
        } else {
            $result .= $this->mValue;
        }
        if (!empty($this->aIeHack)) {
            $result .= ' \\' . \implode('\\', $this->aIeHack);
        }
        if ($this->bIsImportant) {
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
    public function getComments()
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
