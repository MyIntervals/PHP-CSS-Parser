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
    private $aSelectors = [];

    /**
     * @param CSSList|null $list
     *
     * @return DeclarationBlock|false
     *
     * @throws UnexpectedTokenException
     * @throws UnexpectedEOFException
     *
     * @internal since V8.8.0
     */
    public static function parse(ParserState $parserState, $list = null)
    {
        $comments = [];
        $result = new DeclarationBlock($parserState->currentLine());
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
            $result->setSelectors(\implode('', $aSelectorParts), $list);
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
        $result->setComments($comments);
        RuleSet::parseRuleSet($parserState, $result);
        return $result;
    }

    /**
     * @param array<int, Selector|string>|string $mSelector
     * @param CSSList|null $list
     *
     * @throws UnexpectedTokenException
     */
    public function setSelectors($mSelector, $list = null): void
    {
        if (\is_array($mSelector)) {
            $this->aSelectors = $mSelector;
        } else {
            $this->aSelectors = \explode(',', $mSelector);
        }
        foreach ($this->aSelectors as $key => $mSelector) {
            if (!($mSelector instanceof Selector)) {
                if ($list === null || !($list instanceof KeyFrame)) {
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
    public function render(OutputFormat $outputFormat): string
    {
        $result = $outputFormat->comments($this);
        if (\count($this->aSelectors) === 0) {
            // If all the selectors have been removed, this declaration block becomes invalid
            throw new OutputException('Attempt to print declaration block with missing selector', $this->lineNumber);
        }
        $result .= $outputFormat->sBeforeDeclarationBlock;
        $result .= $outputFormat->implode(
            $outputFormat->spaceBeforeSelectorSeparator() . ',' . $outputFormat->spaceAfterSelectorSeparator(),
            $this->aSelectors
        );
        $result .= $outputFormat->sAfterDeclarationBlockSelectors;
        $result .= $outputFormat->spaceBeforeOpeningBrace() . '{';
        $result .= $this->renderRules($outputFormat);
        $result .= '}';
        $result .= $outputFormat->sAfterDeclarationBlock;
        return $result;
    }
}
