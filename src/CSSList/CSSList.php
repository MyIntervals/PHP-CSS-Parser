<?php

declare(strict_types=1);

namespace Sabberworm\CSS\CSSList;

use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Property\AtRule;
use Sabberworm\CSS\Property\Charset;
use Sabberworm\CSS\Property\CSSNamespace;
use Sabberworm\CSS\Property\Import;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\RuleSet\AtRuleSet;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\RuleSet\RuleSet;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\CSSString;
use Sabberworm\CSS\Value\URL;
use Sabberworm\CSS\Value\Value;

/**
 * This is the most generic container available. It can contain `DeclarationBlock`s (rule sets with a selector),
 * `RuleSet`s as well as other `CSSList` objects.
 *
 * It can also contain `Import` and `Charset` objects stemming from at-rules.
 */
abstract class CSSList implements Renderable, Commentable
{
    /**
     * @var array<array-key, Comment>
     */
    protected $comments;

    /**
     * @var array<int, RuleSet|CSSList|Import|Charset>
     */
    protected $contents;

    /**
     * @var int
     */
    protected $lineNumber;

    /**
     * @param int $lineNumber
     */
    public function __construct($lineNumber = 0)
    {
        $this->comments = [];
        $this->contents = [];
        $this->lineNumber = $lineNumber;
    }

    /**
     * @throws UnexpectedTokenException
     * @throws SourceException
     */
    public static function parseList(ParserState $parserState, CSSList $list): void
    {
        $bIsRoot = $list instanceof Document;
        if (\is_string($parserState)) {
            $parserState = new ParserState($parserState, Settings::create());
        }
        $usesLenientParsing = $parserState->getSettings()->bLenientParsing;
        $comments = [];
        while (!$parserState->isEnd()) {
            $comments = \array_merge($comments, $parserState->consumeWhiteSpace());
            $listItem = null;
            if ($usesLenientParsing) {
                try {
                    $listItem = self::parseListItem($parserState, $list);
                } catch (UnexpectedTokenException $e) {
                    $listItem = false;
                }
            } else {
                $listItem = self::parseListItem($parserState, $list);
            }
            if ($listItem === null) {
                // List parsing finished
                return;
            }
            if ($listItem) {
                $listItem->addComments($comments);
                $list->append($listItem);
            }
            $comments = $parserState->consumeWhiteSpace();
        }
        $list->addComments($comments);
        if (!$bIsRoot && !$usesLenientParsing) {
            throw new SourceException('Unexpected end of document', $parserState->currentLine());
        }
    }

    /**
     * @return AtRuleBlockList|KeyFrame|Charset|CSSNamespace|Import|AtRuleSet|DeclarationBlock|false|null
     *
     * @throws SourceException
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    private static function parseListItem(ParserState $parserState, CSSList $list)
    {
        $bIsRoot = $list instanceof Document;
        if ($parserState->comes('@')) {
            $oAtRule = self::parseAtRule($parserState);
            if ($oAtRule instanceof Charset) {
                if (!$bIsRoot) {
                    throw new UnexpectedTokenException(
                        '@charset may only occur in root document',
                        '',
                        'custom',
                        $parserState->currentLine()
                    );
                }
                if (\count($list->getContents()) > 0) {
                    throw new UnexpectedTokenException(
                        '@charset must be the first parseable token in a document',
                        '',
                        'custom',
                        $parserState->currentLine()
                    );
                }
                $parserState->setCharset($oAtRule->getCharset());
            }
            return $oAtRule;
        } elseif ($parserState->comes('}')) {
            if ($bIsRoot) {
                if ($parserState->getSettings()->bLenientParsing) {
                    return DeclarationBlock::parse($parserState);
                } else {
                    throw new SourceException('Unopened {', $parserState->currentLine());
                }
            } else {
                // End of list
                return null;
            }
        } else {
            return DeclarationBlock::parse($parserState, $list);
        }
    }

    /**
     * @return AtRuleBlockList|KeyFrame|Charset|CSSNamespace|Import|AtRuleSet|null
     *
     * @throws SourceException
     * @throws UnexpectedTokenException
     * @throws UnexpectedEOFException
     */
    private static function parseAtRule(ParserState $parserState)
    {
        $parserState->consume('@');
        $sIdentifier = $parserState->parseIdentifier();
        $iIdentifierLineNum = $parserState->currentLine();
        $parserState->consumeWhiteSpace();
        if ($sIdentifier === 'import') {
            $oLocation = URL::parse($parserState);
            $parserState->consumeWhiteSpace();
            $mediaQuery = null;
            if (!$parserState->comes(';')) {
                $mediaQuery = \trim($parserState->consumeUntil([';', ParserState::EOF]));
                if ($mediaQuery === '') {
                    $mediaQuery = null;
                }
            }
            $parserState->consumeUntil([';', ParserState::EOF], true, true);
            return new Import($oLocation, $mediaQuery, $iIdentifierLineNum);
        } elseif ($sIdentifier === 'charset') {
            $oCharsetString = CSSString::parse($parserState);
            $parserState->consumeWhiteSpace();
            $parserState->consumeUntil([';', ParserState::EOF], true, true);
            return new Charset($oCharsetString, $iIdentifierLineNum);
        } elseif (self::identifierIs($sIdentifier, 'keyframes')) {
            $oResult = new KeyFrame($iIdentifierLineNum);
            $oResult->setVendorKeyFrame($sIdentifier);
            $oResult->setAnimationName(\trim($parserState->consumeUntil('{', false, true)));
            CSSList::parseList($parserState, $oResult);
            if ($parserState->comes('}')) {
                $parserState->consume('}');
            }
            return $oResult;
        } elseif ($sIdentifier === 'namespace') {
            $sPrefix = null;
            $mUrl = Value::parsePrimitiveValue($parserState);
            if (!$parserState->comes(';')) {
                $sPrefix = $mUrl;
                $mUrl = Value::parsePrimitiveValue($parserState);
            }
            $parserState->consumeUntil([';', ParserState::EOF], true, true);
            if ($sPrefix !== null && !\is_string($sPrefix)) {
                throw new UnexpectedTokenException('Wrong namespace prefix', $sPrefix, 'custom', $iIdentifierLineNum);
            }
            if (!($mUrl instanceof CSSString || $mUrl instanceof URL)) {
                throw new UnexpectedTokenException(
                    'Wrong namespace url of invalid type',
                    $mUrl,
                    'custom',
                    $iIdentifierLineNum
                );
            }
            return new CSSNamespace($mUrl, $sPrefix, $iIdentifierLineNum);
        } else {
            // Unknown other at rule (font-face or such)
            $sArgs = \trim($parserState->consumeUntil('{', false, true));
            if (\substr_count($sArgs, '(') != \substr_count($sArgs, ')')) {
                if ($parserState->getSettings()->bLenientParsing) {
                    return null;
                } else {
                    throw new SourceException('Unmatched brace count in media query', $parserState->currentLine());
                }
            }
            $bUseRuleSet = true;
            foreach (\explode('/', AtRule::BLOCK_RULES) as $sBlockRuleName) {
                if (self::identifierIs($sIdentifier, $sBlockRuleName)) {
                    $bUseRuleSet = false;
                    break;
                }
            }
            if ($bUseRuleSet) {
                $oAtRule = new AtRuleSet($sIdentifier, $sArgs, $iIdentifierLineNum);
                RuleSet::parseRuleSet($parserState, $oAtRule);
            } else {
                $oAtRule = new AtRuleBlockList($sIdentifier, $sArgs, $iIdentifierLineNum);
                CSSList::parseList($parserState, $oAtRule);
                if ($parserState->comes('}')) {
                    $parserState->consume('}');
                }
            }
            return $oAtRule;
        }
    }

    /**
     * Tests an identifier for a given value. Since identifiers are all keywords, they can be vendor-prefixed.
     * We need to check for these versions too.
     *
     * @param string $sIdentifier
     */
    private static function identifierIs($sIdentifier, string $sMatch): bool
    {
        return (\strcasecmp($sIdentifier, $sMatch) === 0)
            ?: \preg_match("/^(-\\w+-)?$sMatch$/i", $sIdentifier) === 1;
    }

    /**
     * @return int
     */
    public function getLineNo()
    {
        return $this->lineNumber;
    }

    /**
     * Prepends an item to the list of contents.
     *
     * @param RuleSet|CSSList|Import|Charset $item
     */
    public function prepend($item): void
    {
        \array_unshift($this->contents, $item);
    }

    /**
     * Appends an item to the list of contents.
     *
     * @param RuleSet|CSSList|Import|Charset $item
     */
    public function append($item): void
    {
        $this->contents[] = $item;
    }

    /**
     * Splices the list of contents.
     *
     * @param int $iOffset
     * @param int $iLength
     * @param array<int, RuleSet|CSSList|Import|Charset> $mReplacement
     */
    public function splice($iOffset, $iLength = null, $mReplacement = null): void
    {
        \array_splice($this->contents, $iOffset, $iLength, $mReplacement);
    }

    /**
     * Inserts an item in the CSS list before its sibling. If the desired sibling cannot be found,
     * the item is appended at the end.
     *
     * @param RuleSet|CSSList|Import|Charset $item
     * @param RuleSet|CSSList|Import|Charset $sibling
     */
    public function insertBefore($item, $sibling): void
    {
        if (\in_array($sibling, $this->contents, true)) {
            $this->replace($sibling, [$item, $sibling]);
        } else {
            $this->append($item);
        }
    }

    /**
     * Removes an item from the CSS list.
     *
     * @param RuleSet|Import|Charset|CSSList $itemToRemove
     *        May be a `RuleSet` (most likely a `DeclarationBlock`), an `Import`,
     *        a `Charset` or another `CSSList` (most likely a `MediaQuery`)
     *
     * @return bool whether the item was removed
     */
    public function remove($itemToRemove)
    {
        $key = \array_search($itemToRemove, $this->contents, true);
        if ($key !== false) {
            unset($this->contents[$key]);
            return true;
        }
        return false;
    }

    /**
     * Replaces an item from the CSS list.
     *
     * @param RuleSet|Import|Charset|CSSList $oldItem
     *        May be a `RuleSet` (most likely a `DeclarationBlock`), an `Import`, a `Charset`
     *        or another `CSSList` (most likely a `MediaQuery`)
     *
     * @return bool
     */
    public function replace($oldItem, $newItem)
    {
        $key = \array_search($oldItem, $this->contents, true);
        if ($key !== false) {
            if (\is_array($newItem)) {
                \array_splice($this->contents, $key, 1, $newItem);
            } else {
                \array_splice($this->contents, $key, 1, [$newItem]);
            }
            return true;
        }
        return false;
    }

    /**
     * @param array<int, RuleSet|Import|Charset|CSSList> $contents
     */
    public function setContents(array $contents): void
    {
        $this->contents = [];
        foreach ($contents as $content) {
            $this->append($content);
        }
    }

    /**
     * Removes a declaration block from the CSS list if it matches all given selectors.
     *
     * @param DeclarationBlock|array<array-key, Selector>|string $mSelector the selectors to match
     * @param bool $bRemoveAll whether to stop at the first declaration block found or remove all blocks
     */
    public function removeDeclarationBlockBySelector($mSelector, $bRemoveAll = false): void
    {
        if ($mSelector instanceof DeclarationBlock) {
            $mSelector = $mSelector->getSelectors();
        }
        if (!\is_array($mSelector)) {
            $mSelector = \explode(',', $mSelector);
        }
        foreach ($mSelector as $key => &$mSel) {
            if (!($mSel instanceof Selector)) {
                if (!Selector::isValid($mSel)) {
                    throw new UnexpectedTokenException(
                        "Selector did not match '" . Selector::SELECTOR_VALIDATION_RX . "'.",
                        $mSel,
                        'custom'
                    );
                }
                $mSel = new Selector($mSel);
            }
        }
        foreach ($this->contents as $key => $item) {
            if (!($item instanceof DeclarationBlock)) {
                continue;
            }
            if ($item->getSelectors() == $mSelector) {
                unset($this->contents[$key]);
                if (!$bRemoveAll) {
                    return;
                }
            }
        }
    }

    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    /**
     * @return string
     */
    protected function renderListContents(OutputFormat $oOutputFormat)
    {
        $sResult = '';
        $bIsFirst = true;
        $oNextLevel = $oOutputFormat;
        if (!$this->isRootList()) {
            $oNextLevel = $oOutputFormat->nextLevel();
        }
        foreach ($this->contents as $listItem) {
            $sRendered = $oOutputFormat->safely(static function () use ($oNextLevel, $listItem): string {
                return $listItem->render($oNextLevel);
            });
            if ($sRendered === null) {
                continue;
            }
            if ($bIsFirst) {
                $bIsFirst = false;
                $sResult .= $oNextLevel->spaceBeforeBlocks();
            } else {
                $sResult .= $oNextLevel->spaceBetweenBlocks();
            }
            $sResult .= $sRendered;
        }

        if (!$bIsFirst) {
            // Had some output
            $sResult .= $oOutputFormat->spaceAfterBlocks();
        }

        return $sResult;
    }

    /**
     * Return true if the list can not be further outdented. Only important when rendering.
     *
     * @return bool
     */
    abstract public function isRootList();

    /**
     * Returns the stored items.
     *
     * @return array<int, RuleSet|Import|Charset|CSSList>
     */
    public function getContents()
    {
        return $this->contents;
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
