<?php

declare(strict_types=1);

namespace Sabberworm\CSS\RuleSet;

use Sabberworm\CSS\CSSList\CSSList;
use Sabberworm\CSS\CSSList\KeyFrame;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\OutputException;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Property\KeyframeSelector;
use Sabberworm\CSS\Property\Selector;

/**
 * This class represents a `RuleSet` constrained by a `Selector`.
 *
 * It contains an array of selector objects (comma-separated in the CSS) as well as the rules to be applied to the
 * matching elements.
 *
 * Declaration blocks usually appear directly inside a `Document` or another `CSSList` (mostly a `MediaQuery`).
 */
class DeclarationBlock extends RuleSet
{
    /**
     * @var array<int, Selector|string>
     */
    private $aSelectors;

    /**
     * @param int $lineNumber
     */
    public function __construct($lineNumber = 0)
    {
        parent::__construct($lineNumber);
        $this->aSelectors = [];
    }

    /**
     * @param CSSList|null $oList
     *
     * @return DeclarationBlock|false
     *
     * @throws UnexpectedTokenException
     * @throws UnexpectedEOFException
     */
    public static function parse(ParserState $parserState, $oList = null)
    {
        $comments = [];
        $oResult = new DeclarationBlock($parserState->currentLine());
        try {
            $aSelectorParts = [];
            $sStringWrapperChar = false;
            do {
                $aSelectorParts[] = $parserState->consume(1)
                    . $parserState->consumeUntil(['{', '}', '\'', '"'], false, false, $comments);
                if (\in_array($parserState->peek(), ['\'', '"'], true) && \substr(\end($aSelectorParts), -1) != '\\') {
                    if ($sStringWrapperChar === false) {
                        $sStringWrapperChar = $parserState->peek();
                    } elseif ($sStringWrapperChar == $parserState->peek()) {
                        $sStringWrapperChar = false;
                    }
                }
            } while (!\in_array($parserState->peek(), ['{', '}'], true) || $sStringWrapperChar !== false);
            $oResult->setSelectors(\implode('', $aSelectorParts), $oList);
            if ($parserState->comes('{')) {
                $parserState->consume(1);
            }
        } catch (UnexpectedTokenException $e) {
            if ($parserState->getSettings()->bLenientParsing) {
                if (!$parserState->comes('}')) {
                    $parserState->consumeUntil('}', false, true);
                }
                return false;
            } else {
                throw $e;
            }
        }
        $oResult->setComments($comments);
        RuleSet::parseRuleSet($parserState, $oResult);
        return $oResult;
    }

    /**
     * @param array<int, Selector|string>|string $mSelector
     * @param CSSList|null $oList
     *
     * @throws UnexpectedTokenException
     */
    public function setSelectors($mSelector, $oList = null): void
    {
        if (\is_array($mSelector)) {
            $this->aSelectors = $mSelector;
        } else {
            $this->aSelectors = \explode(',', $mSelector);
        }
        foreach ($this->aSelectors as $key => $mSelector) {
            if (!($mSelector instanceof Selector)) {
                if ($oList === null || !($oList instanceof KeyFrame)) {
                    if (!Selector::isValid($mSelector)) {
                        throw new UnexpectedTokenException(
                            "Selector did not match '" . Selector::SELECTOR_VALIDATION_RX . "'.",
                            $mSelector,
                            'custom'
                        );
                    }
                    $this->aSelectors[$key] = new Selector($mSelector);
                } else {
                    if (!KeyframeSelector::isValid($mSelector)) {
                        throw new UnexpectedTokenException(
                            "Selector did not match '" . KeyframeSelector::SELECTOR_VALIDATION_RX . "'.",
                            $mSelector,
                            'custom'
                        );
                    }
                    $this->aSelectors[$key] = new KeyframeSelector($mSelector);
                }
            }
        }
    }

    /**
     * Remove one of the selectors of the block.
     *
     * @param Selector|string $mSelector
     */
    public function removeSelector($mSelector): bool
    {
        if ($mSelector instanceof Selector) {
            $mSelector = $mSelector->getSelector();
        }
        foreach ($this->aSelectors as $key => $selector) {
            if ($selector->getSelector() === $mSelector) {
                unset($this->aSelectors[$key]);
                return true;
            }
        }
        return false;
    }

    /**
     * @return array<int, Selector|string>
     */
    public function getSelectors()
    {
        return $this->aSelectors;
    }

    /**
     * @throws OutputException
     */
    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    /**
     * @throws OutputException
     */
    public function render(OutputFormat $oOutputFormat): string
    {
        $sResult = $oOutputFormat->comments($this);
        if (\count($this->aSelectors) === 0) {
            // If all the selectors have been removed, this declaration block becomes invalid
            throw new OutputException('Attempt to print declaration block with missing selector', $this->lineNumber);
        }
        $sResult .= $oOutputFormat->sBeforeDeclarationBlock;
        $sResult .= $oOutputFormat->implode(
            $oOutputFormat->spaceBeforeSelectorSeparator() . ',' . $oOutputFormat->spaceAfterSelectorSeparator(),
            $this->aSelectors
        );
        $sResult .= $oOutputFormat->sAfterDeclarationBlockSelectors;
        $sResult .= $oOutputFormat->spaceBeforeOpeningBrace() . '{';
        $sResult .= $this->renderRules($oOutputFormat);
        $sResult .= '}';
        $sResult .= $oOutputFormat->sAfterDeclarationBlock;
        return $sResult;
    }
}
