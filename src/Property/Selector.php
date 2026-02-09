<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Property;

use Sabberworm\CSS\Comment\Comment;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Property\Selector\Combinator;
use Sabberworm\CSS\Property\Selector\Component;
use Sabberworm\CSS\Property\Selector\CompoundSelector;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\ShortClassNameProvider;

use function Safe\preg_match;

/**
 * Class representing a single CSS selector. Selectors have to be split by the comma prior to being passed into this
 * class.
 */
class Selector implements Renderable
{
    use ShortClassNameProvider;

    /**
     * @internal since 8.5.2
     */
    public const SELECTOR_VALIDATION_RX = '/
        ^(
            # not whitespace only
            (?!\\s*+$)
            (?:
                # any sequence of valid unescaped characters, except quotes
                [a-zA-Z0-9\\x{00A0}-\\x{FFFF}_^$|*=~\\[\\]()\\-\\s\\.:#+>,]++
                |
                # one or more escaped characters
                (?:\\\\.)++
                |
                # quoted text, like in `[id="example"]`
                (?:
                    # opening quote
                    ([\'"])
                    (?:
                        # sequence of characters except closing quote or backslash
                        (?:(?!\\g{-1}|\\\\).)++
                        |
                        # one or more escaped characters
                        (?:\\\\.)++
                    )*+ # zero or more times
                    # closing quote or end (unmatched quote is currently allowed)
                    (?:\\g{-1}|$)
                )
            )*+ # zero or more times
        )$
        /ux';

    /**
     * @var non-empty-list<Component>
     */
    private $components;

    /**
     * @internal since V8.8.0
     */
    public static function isValid(string $selector): bool
    {
        // Note: We need to use `static::` here as the constant is overridden in the `KeyframeSelector` class.
        $numberOfMatches = preg_match(static::SELECTOR_VALIDATION_RX, $selector);

        return $numberOfMatches === 1;
    }

    /**
     * @param non-empty-string|non-empty-list<Component> $selector
     *        Providing a string is deprecated in version 9.2 and will not work from v10.0
     *
     * @throws UnexpectedTokenException if the selector is not valid
     */
    final public function __construct($selector)
    {
        if (\is_string($selector)) {
            $this->setSelector($selector);
        } else {
            $this->setComponents($selector);
        }
    }

    /**
     * @param list<Comment> $comments
     *
     * @return non-empty-list<Component>
     *
     * @throws UnexpectedTokenException
     */
    private static function parseComponents(ParserState $parserState, array &$comments = []): array
    {
        // Whitespace is a descendent combinator, not allowed around a compound selector.
        // (It is allowed within, e.g. as part of a string or within a function like `:not()`.)
        // Gobble any up now to get a clean start.
        $parserState->consumeWhiteSpace($comments);

        $selectorParts = [];
        while (true) {
            try {
                $selectorParts[] = CompoundSelector::parse($parserState, $comments);
            } catch (UnexpectedTokenException $e) {
                if ($selectorParts !== [] && \end($selectorParts)->getValue() === ' ') {
                    // The whitespace was not a descendent combinator, and was, in fact, arbitrary,
                    // after the end of the selector.  Discard it.
                    \array_pop($selectorParts);
                    break;
                } else {
                    throw $e;
                }
            }
            try {
                $selectorParts[] = Combinator::parse($parserState, $comments);
            } catch (UnexpectedTokenException $e) {
                // End of selector has been reached.
                break;
            }
        }

        return $selectorParts;
    }

    /**
     * @param list<Comment> $comments
     *
     * @throws UnexpectedTokenException
     *
     * @internal
     */
    public static function parse(ParserState $parserState, array &$comments = []): self
    {
        $selectorParts = self::parseComponents($parserState, $comments);

        // Check that the selector has been fully parsed:
        if (!\in_array($parserState->peek(), ['{', '}', ',', ''], true)) {
            throw new UnexpectedTokenException(
                '`,`, `{`, `}` or EOF',
                $parserState->peek(5),
                'literal',
                $parserState->currentLine()
            );
        }

        return new static($selectorParts);
    }

    /**
     * @return non-empty-list<Component>
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * @param non-empty-list<Component> $components
     *        This should be an alternating sequence of `CompoundSelector` and `Combinator`, starting and ending with a
     *        `CompoundSelector`, and may be a single `CompoundSelector`.
     */
    public function setComponents(array $components): self
    {
        $this->components = $components;

        return $this;
    }

    /**
     * @return non-empty-string
     *
     * @deprecated in version 9.2, will be removed in v10.0.  Use either `getComponents()` or `render()` instead.
     */
    public function getSelector(): string
    {
        return $this->render(new OutputFormat());
    }

    /**
     * @param non-empty-string $selector
     *
     * @throws UnexpectedTokenException if the selector is not valid
     *
     * @deprecated in version 9.2, will be removed in v10.0.  Use `setComponents()` instead.
     */
    public function setSelector(string $selector): void
    {
        $parserState = new ParserState($selector, Settings::create());

        $components = self::parseComponents($parserState);

        // Check that the selector has been fully parsed:
        if (!$parserState->isEnd()) {
            throw new UnexpectedTokenException(
                'EOF',
                $parserState->peek(5),
                'literal'
            );
        }

        $this->components = $components;
    }

    /**
     * @return int<0, max>
     */
    public function getSpecificity(): int
    {
        return \array_sum(\array_map(
            static function (Component $component): int {
                return $component->getSpecificity();
            },
            $this->components
        ));
    }

    public function render(OutputFormat $outputFormat): string
    {
        return \implode('', \array_map(
            static function (Component $component) use ($outputFormat): string {
                return $component->render($outputFormat);
            },
            $this->components
        ));
    }

    /**
     * @return array<string, bool|int|float|string|array<mixed>|null>
     *
     * @internal
     */
    public function getArrayRepresentation(): array
    {
        return [
            'class' => $this->getShortClassName(),
            'components' => \array_map(
                static function (Component $component): array {
                    return $component->getArrayRepresentation();
                },
                $this->components
            ),
        ];
    }
}
