<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;

/**
 * `Color's can be input in the form #rrggbb, #rgb or schema(val1, val2, …) but are always stored as an array of
 * ('s' => val1, 'c' => val2, 'h' => val3, …) and output in the second form.
 */
class Color extends CSSFunction
{
    /**
     * @param array<int, Value|string> $colorValues
     * @param int $lineNumber
     */
    public function __construct(array $colorValues, $lineNumber = 0)
    {
        parent::__construct(\implode('', \array_keys($colorValues)), $colorValues, ',', $lineNumber);
    }

    /**
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    public static function parse(ParserState $parserState, bool $ignoreCase = false): CSSFunction
    {
        return
            $parserState->comes('#')
            ? self::parseHexColor($parserState)
            : self::parseColorFunction($parserState);
    }

    /**
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    private static function parseHexColor(ParserState $parserState): CSSFunction
    {
        $parserState->consume('#');
        $hexValue = $parserState->parseIdentifier(false);
        if ($parserState->strlen($hexValue) === 3) {
            $hexValue = $hexValue[0] . $hexValue[0] . $hexValue[1] . $hexValue[1] . $hexValue[2] . $hexValue[2];
        } elseif ($parserState->strlen($hexValue) === 4) {
            $hexValue = $hexValue[0] . $hexValue[0] . $hexValue[1] . $hexValue[1] . $hexValue[2] . $hexValue[2]
                . $hexValue[3] . $hexValue[3];
        }

        if ($parserState->strlen($hexValue) === 8) {
            $colorValues = [
                'r' => new Size(\intval($hexValue[0] . $hexValue[1], 16), null, true, $parserState->currentLine()),
                'g' => new Size(\intval($hexValue[2] . $hexValue[3], 16), null, true, $parserState->currentLine()),
                'b' => new Size(\intval($hexValue[4] . $hexValue[5], 16), null, true, $parserState->currentLine()),
                'a' => new Size(
                    \round(self::mapRange(\intval($hexValue[6] . $hexValue[7], 16), 0, 255, 0, 1), 2),
                    null,
                    true,
                    $parserState->currentLine()
                ),
            ];
        } elseif ($parserState->strlen($hexValue) === 6) {
            $colorValues = [
                'r' => new Size(\intval($hexValue[0] . $hexValue[1], 16), null, true, $parserState->currentLine()),
                'g' => new Size(\intval($hexValue[2] . $hexValue[3], 16), null, true, $parserState->currentLine()),
                'b' => new Size(\intval($hexValue[4] . $hexValue[5], 16), null, true, $parserState->currentLine()),
            ];
        } else {
            throw new UnexpectedTokenException(
                'Invalid hex color value',
                $hexValue,
                'custom',
                $parserState->currentLine()
            );
        }

        return new Color($colorValues, $parserState->currentLine());
    }

    /**
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    private static function parseColorFunction(ParserState $parserState): CSSFunction
    {
        $colorValues = [];

        $colorMode = $parserState->parseIdentifier(true);
        $parserState->consumeWhiteSpace();
        $parserState->consume('(');

        // CSS Color Module Level 4 says that `rgb` and `rgba` are now aliases; likewise `hsl` and `hsla`.
        // So, attempt to parse with the `a`, and allow for it not being there.
        switch ($colorMode) {
            case 'rgb':
                $colorModeForParsing = 'rgba';
                $mayHaveOptionalAlpha = true;
                break;
            case 'hsl':
                $colorModeForParsing = 'hsla';
                $mayHaveOptionalAlpha = true;
                break;
            case 'rgba':
                // This is handled identically to the following case.
            case 'hsla':
                $colorModeForParsing = $colorMode;
                $mayHaveOptionalAlpha = true;
                break;
            default:
                $colorModeForParsing = $colorMode;
                $mayHaveOptionalAlpha = false;
        }

        $containsVar = false;
        $isLegacySyntax = false;
        $expectedArgumentCount = $parserState->strlen($colorModeForParsing);
        for ($argumentIndex = 0; $argumentIndex < $expectedArgumentCount; ++$argumentIndex) {
            $parserState->consumeWhiteSpace();
            if ($parserState->comes('var')) {
                $colorValues[$colorModeForParsing[$argumentIndex]] = CSSFunction::parseIdentifierOrFunction($parserState);
                $containsVar = true;
            } else {
                $colorValues[$colorModeForParsing[$argumentIndex]] = Size::parse($parserState, true);
            }

            // This must be done first, to consume comments as well, so that the `comes` test will work.
            $parserState->consumeWhiteSpace();

            // With a `var` argument, the function can have fewer arguments.
            // And as of CSS Color Module Level 4, the alpha argument is optional.
            $canCloseNow =
                $containsVar ||
                ($mayHaveOptionalAlpha && $argumentIndex >= $expectedArgumentCount - 2);
            if ($canCloseNow && $parserState->comes(')')) {
                break;
            }

            // "Legacy" syntax is comma-delimited.
            // "Modern" syntax is space-delimited, with `/` as alpha delimiter.
            // They cannot be mixed.
            if ($argumentIndex === 0) {
                // An immediate closing parenthesis is not valid.
                if ($parserState->comes(')')) {
                    throw new UnexpectedTokenException(
                        'Color function with no arguments',
                        '',
                        'custom',
                        $parserState->currentLine()
                    );
                }
                $isLegacySyntax = $parserState->comes(',');
            }

            if ($isLegacySyntax && $argumentIndex < ($expectedArgumentCount - 1)) {
                $parserState->consume(',');
            }

            // In the "modern" syntax, the alpha value must be delimited with `/`.
            if (!$isLegacySyntax) {
                if ($containsVar) {
                    // If the `var` substitution encompasses more than one argument,
                    // the alpha deliminator may come at any time.
                    if ($parserState->comes('/')) {
                        $parserState->consume('/');
                    }
                } elseif (($colorModeForParsing[$argumentIndex + 1] ?? '') === 'a') {
                    // Alpha value is the next expected argument.
                    // Since a closing parenthesis was not found, a `/` separator is now required.
                    $parserState->consume('/');
                }
            }
        }
        $parserState->consume(')');

        return
            $containsVar
            ? new CSSFunction($colorMode, \array_values($colorValues), ',', $parserState->currentLine())
            : new Color($colorValues, $parserState->currentLine());
    }

    private static function mapRange(float $value, float $fromMin, float $fromMax, float $toMin, float $toMax): float
    {
        $fromRange = $fromMax - $fromMin;
        $toRange = $toMax - $toMin;
        $multiplier = $toRange / $fromRange;
        $newValue = $value - $fromMin;
        $newValue *= $multiplier;
        return $newValue + $toMin;
    }

    /**
     * @return array<int, Value|string>
     */
    public function getColor()
    {
        return $this->aComponents;
    }

    /**
     * @param array<int, Value|string> $colorValues
     */
    public function setColor(array $colorValues): void
    {
        $this->setName(\implode('', \array_keys($colorValues)));
        $this->aComponents = $colorValues;
    }

    /**
     * @return string
     */
    public function getColorDescription()
    {
        return $this->getName();
    }

    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $outputFormat): string
    {
        // Shorthand RGB color values
        if (
            $outputFormat->getRGBHashNotation()
            && \implode('', \array_keys($this->aComponents)) === 'rgb'
            && $this->allComponentsAreNumbers()
        ) {
            $result = \sprintf(
                '%02x%02x%02x',
                $this->aComponents['r']->getSize(),
                $this->aComponents['g']->getSize(),
                $this->aComponents['b']->getSize()
            );
            return '#' . (($result[0] == $result[1]) && ($result[2] == $result[3]) && ($result[4] == $result[5])
                    ? "$result[0]$result[2]$result[4]" : $result);
        }
        return parent::render($outputFormat);
    }

    /**
     * Test whether all color components are absolute numbers (CSS type `number`), not percentages or anything else.
     * If any component is not an instance of `Size`, the method will also return `false`.
     */
    private function allComponentsAreNumbers(): bool
    {
        foreach ($this->aComponents as $component) {
            if (!$component instanceof Size || $component->getUnit() !== null) {
                return false;
            }
        }

        return true;
    }
}
