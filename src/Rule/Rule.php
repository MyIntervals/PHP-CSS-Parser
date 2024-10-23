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
    protected $iLineNo;

    /**
     * @var int
     */
    protected $iColNo;

    /**
     * @var array<array-key, Comment>
     */
    protected $aComments;

    /**
     * @param string $sRule
     * @param int $iLineNo
     * @param int $iColNo
     */
    public function __construct($sRule, $iLineNo = 0, $iColNo = 0)
    {
        $this->sRule = $sRule;
        $this->mValue = null;
        $this->bIsImportant = false;
        $this->aIeHack = [];
        $this->iLineNo = $iLineNo;
        $this->iColNo = $iColNo;
        $this->aComments = [];
    }

    /**
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    public static function parse(ParserState $oParserState): Rule
    {
        $aComments = $oParserState->consumeWhiteSpace();
        $oRule = new Rule(
            $oParserState->parseIdentifier(!$oParserState->comes('--')),
            $oParserState->currentLine(),
            $oParserState->currentColumn()
        );
        $oRule->setComments($aComments);
        $oRule->addComments($oParserState->consumeWhiteSpace());
        $oParserState->consume(':');
        $oValue = Value::parseValue($oParserState, self::listDelimiterForRule($oRule->getRule()));
        $oRule->setValue($oValue);
        if ($oParserState->getSettings()->bLenientParsing) {
            while ($oParserState->comes('\\')) {
                $oParserState->consume('\\');
                $oRule->addIeHack($oParserState->consume());
                $oParserState->consumeWhiteSpace();
            }
        }
        $oParserState->consumeWhiteSpace();
        if ($oParserState->comes('!')) {
            $oParserState->consume('!');
            $oParserState->consumeWhiteSpace();
            $oParserState->consume('important');
            $oRule->setIsImportant(true);
        }
        $oParserState->consumeWhiteSpace();
        while ($oParserState->comes(';')) {
            $oParserState->consume(';');
        }

        $oParserState->consumeWhiteSpace();

        return $oRule;
    }

    /**
     * @param string $sRule
     *
     * @return array<int, string>
     */
    private static function listDelimiterForRule($sRule): array
    {
        if (\preg_match('/^font($|-)/', $sRule)) {
            return [',', '/', ' '];
        }
        return [',', ' ', '/'];
    }

    /**
     * @return int
     */
    public function getLineNo()
    {
        return $this->iLineNo;
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
        $this->iLineNo = $iLine;
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
        if (!$this->mValue instanceof RuleValueList || $this->mValue->getListSeparator() !== $sType) {
            $mCurrentValue = $this->mValue;
            $this->mValue = new RuleValueList($sType, $this->iLineNo);
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
     *
     * @return void
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

    public function render(OutputFormat $oOutputFormat): string
    {
        $sResult = "{$oOutputFormat->comments($this)}{$this->sRule}:{$oOutputFormat->spaceAfterRuleName()}";
        if ($this->mValue instanceof Value) { // Can also be a ValueList
            $sResult .= $this->mValue->render($oOutputFormat);
        } else {
            $sResult .= $this->mValue;
        }
        if (!empty($this->aIeHack)) {
            $sResult .= ' \\' . \implode('\\', $this->aIeHack);
        }
        if ($this->bIsImportant) {
            $sResult .= ' !important';
        }
        $sResult .= ';';
        return $sResult;
    }

    /**
     * @param array<array-key, Comment> $aComments
     */
    public function addComments(array $aComments): void
    {
        $this->aComments = \array_merge($this->aComments, $aComments);
    }

    /**
     * @return array<array-key, Comment>
     */
    public function getComments()
    {
        return $this->aComments;
    }

    /**
     * @param array<array-key, Comment> $aComments
     */
    public function setComments(array $aComments): void
    {
        $this->aComments = $aComments;
    }
}
