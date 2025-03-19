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
     * @var array<Selector|string>
     */
    private $selectors = [];

    /**
     * @return DeclarationBlock|false
     *
     * @throws UnexpectedTokenException
     * @throws UnexpectedEOFException
     *
     * @internal since V8.8.0
     */
    public static function parse(ParserState $parserState, ?CSSList $list = null)
    {
        $comments = [];
        $result = new DeclarationBlock($parserState->currentLine());
        try {
            $selectorParts = [];
            do {
                $selectorParts[] = $parserState->consume(1)
                    . $parserState->consumeUntil(['{', '}', '\'', '"'], false, false, $comments);
                if (\in_array($parserState->peek(), ['\'', '"'], true) && \substr(\end($selectorParts), -1) != '\\') {
                    if (!isset($stringWrapperCharacter)) {
                        $stringWrapperCharacter = $parserState->peek();
                    } elseif ($stringWrapperCharacter === $parserState->peek()) {
                        unset($stringWrapperCharacter);
                    }
                }
            } while (!\in_array($parserState->peek(), ['{', '}'], true) || isset($stringWrapperCharacter));
            $result->setSelectors(\implode('', $selectorParts), $list);
            if ($parserState->comes('{')) {
                $parserState->consume(1);
            }
        } catch (UnexpectedTokenException $e) {
            if ($parserState->getSettings()->usesLenientParsing()) {
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
     * @param array<Selector|string>|string $selectors
     *
     * @throws UnexpectedTokenException
     */
    public function setSelectors($selectors, ?CSSList $list = null): void
    {
        if (\is_array($selectors)) {
            $this->selectors = $selectors;
        } else {
            $this->selectors = \explode(',', $selectors);
        }
        foreach ($this->selectors as $key => $selector) {
            if (!($selector instanceof Selector)) {
                if ($list === null || !($list instanceof KeyFrame)) {
                    if (!Selector::isValid($selector)) {
                        throw new UnexpectedTokenException(
                            "Selector did not match '" . Selector::SELECTOR_VALIDATION_RX . "'.",
                            $selectors,
                            'custom'
                        );
                    }
                    $this->selectors[$key] = new Selector($selector);
                } else {
                    if (!KeyframeSelector::isValid($selector)) {
                        throw new UnexpectedTokenException(
                            "Selector did not match '" . KeyframeSelector::SELECTOR_VALIDATION_RX . "'.",
                            $selector,
                            'custom'
                        );
                    }
                    $this->selectors[$key] = new KeyframeSelector($selector);
                }
            }
        }
    }

    /**
     * Remove one of the selectors of the block.
     *
     * @param Selector|string $selectorToRemove
     */
    public function removeSelector($selectorToRemove): bool
    {
        if ($selectorToRemove instanceof Selector) {
            $selectorToRemove = $selectorToRemove->getSelector();
        }
        foreach ($this->selectors as $key => $selector) {
            if ($selector->getSelector() === $selectorToRemove) {
                unset($this->selectors[$key]);
                return true;
            }
        }
        return false;
    }

    /**
     * @return array<Selector>
     */
    public function getSelectors(): array
    {
        return $this->selectors;
    }

    /**
     * @return non-empty-string
     *
     * @throws OutputException
     */
    public function render(OutputFormat $outputFormat): string
    {
        $formatter = $outputFormat->getFormatter();
        $result = $formatter->comments($this);
        if (\count($this->selectors) === 0) {
            // If all the selectors have been removed, this declaration block becomes invalid
            throw new OutputException('Attempt to print declaration block with missing selector', $this->lineNumber);
        }
        $result .= $outputFormat->getContentBeforeDeclarationBlock();
        $result .= $formatter->implode(
            $formatter->spaceBeforeSelectorSeparator() . ',' . $formatter->spaceAfterSelectorSeparator(),
            $this->selectors
        );
        $result .= $outputFormat->getContentAfterDeclarationBlockSelectors();
        $result .= $formatter->spaceBeforeOpeningBrace() . '{';
        $result .= $this->renderRules($outputFormat);
        $result .= '}';
        $result .= $outputFormat->getContentAfterDeclarationBlock();

        return $result;
    }
}
