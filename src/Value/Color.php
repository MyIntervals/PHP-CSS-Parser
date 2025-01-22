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
     * @param array<int, Value|string> $aColor
     * @param int $iLineNo
     */
    public function __construct(array $aColor, $iLineNo = 0)
    {
        parent::__construct(\implode('', \array_keys($aColor)), $aColor, ',', $iLineNo);
    }

    /**
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     */
    public static function parse(ParserState $oParserState, bool $bIgnoreCase = false): CSSFunction
    {
        $aColor = [];
        if ($oParserState->comes('#')) {
            $oParserState->consume('#');
            $sValue = $oParserState->parseIdentifier(false);
            if ($oParserState->strlen($sValue) === 3) {
                $sValue = $sValue[0] . $sValue[0] . $sValue[1] . $sValue[1] . $sValue[2] . $sValue[2];
            } elseif ($oParserState->strlen($sValue) === 4) {
                $sValue = $sValue[0] . $sValue[0] . $sValue[1] . $sValue[1] . $sValue[2] . $sValue[2] . $sValue[3]
                    . $sValue[3];
            }

            if ($oParserState->strlen($sValue) === 8) {
                $aColor = [
                    'r' => new Size(\intval($sValue[0] . $sValue[1], 16), null, true, $oParserState->currentLine()),
                    'g' => new Size(\intval($sValue[2] . $sValue[3], 16), null, true, $oParserState->currentLine()),
                    'b' => new Size(\intval($sValue[4] . $sValue[5], 16), null, true, $oParserState->currentLine()),
                    'a' => new Size(
                        \round(self::mapRange(\intval($sValue[6] . $sValue[7], 16), 0, 255, 0, 1), 2),
                        null,
                        true,
                        $oParserState->currentLine()
                    ),
                ];
            } elseif ($oParserState->strlen($sValue) === 6) {
                $aColor = [
                    'r' => new Size(\intval($sValue[0] . $sValue[1], 16), null, true, $oParserState->currentLine()),
                    'g' => new Size(\intval($sValue[2] . $sValue[3], 16), null, true, $oParserState->currentLine()),
                    'b' => new Size(\intval($sValue[4] . $sValue[5], 16), null, true, $oParserState->currentLine()),
                ];
            } else {
                throw new UnexpectedTokenException(
                    'Invalid hex color value',
                    $sValue,
                    'custom',
                    $oParserState->currentLine()
                );
            }
        } else {
            $sColorMode = $oParserState->parseIdentifier(true);
            $oParserState->consumeWhiteSpace();
            $oParserState->consume('(');

            // CSS Color Module Level 4 says that `rgb` and `rgba` are now aliases; likewise `hsl` and `hsla`.
            // So, attempt to parse with the `a`, and allow for it not being there.
            switch ($sColorMode) {
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
                    $colorModeForParsing = $sColorMode;
                    $mayHaveOptionalAlpha = true;
                    break;
                default:
                    $colorModeForParsing = $sColorMode;
                    $mayHaveOptionalAlpha = false;
            }

            $bContainsVar = false;
            $iLength = $oParserState->strlen($colorModeForParsing);
            for ($i = 0; $i < $iLength; ++$i) {
                $oParserState->consumeWhiteSpace();
                if ($oParserState->comes('var')) {
                    $aColor[$colorModeForParsing[$i]] = CSSFunction::parseIdentifierOrFunction($oParserState);
                    $bContainsVar = true;
                } else {
                    $aColor[$colorModeForParsing[$i]] = Size::parse($oParserState, true);
                }

                // This must be done first, to consume comments as well, so that the `comes` test will work.
                $oParserState->consumeWhiteSpace();

                // With a `var` argument, the function can have fewer arguments.
                // And as of CSS Color Module Level 4, the alpha argument is optional.
                $canCloseNow =
                    $bContainsVar ||
                    ($mayHaveOptionalAlpha && $i >= $iLength - 2);
                if ($canCloseNow && $oParserState->comes(')')) {
                    break;
                }

                if ($i < ($iLength - 1)) {
                    $oParserState->consume(',');
                }
            }
            $oParserState->consume(')');

            if ($bContainsVar) {
                return new CSSFunction($sColorMode, \array_values($aColor), ',', $oParserState->currentLine());
            }
        }
        return new Color($aColor, $oParserState->currentLine());
    }

    private static function mapRange(float $fVal, float $fFromMin, float $fFromMax, float $fToMin, float $fToMax): float
    {
        $fFromRange = $fFromMax - $fFromMin;
        $fToRange = $fToMax - $fToMin;
        $fMultiplier = $fToRange / $fFromRange;
        $fNewVal = $fVal - $fFromMin;
        $fNewVal *= $fMultiplier;
        return $fNewVal + $fToMin;
    }

    /**
     * @return array<int, Value|string>
     */
    public function getColor()
    {
        return $this->aComponents;
    }

    /**
     * @param array<int, Value|string> $aColor
     */
    public function setColor(array $aColor): void
    {
        $this->setName(\implode('', \array_keys($aColor)));
        $this->aComponents = $aColor;
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

    public function render(OutputFormat $oOutputFormat): string
    {
        // Shorthand RGB color values
        if ($oOutputFormat->getRGBHashNotation() && \implode('', \array_keys($this->aComponents)) === 'rgb') {
            $sResult = \sprintf(
                '%02x%02x%02x',
                $this->aComponents['r']->getSize(),
                $this->aComponents['g']->getSize(),
                $this->aComponents['b']->getSize()
            );
            return '#' . (($sResult[0] == $sResult[1]) && ($sResult[2] == $sResult[3]) && ($sResult[4] == $sResult[5])
                    ? "$sResult[0]$sResult[2]$sResult[4]" : $sResult);
        }
        return parent::render($oOutputFormat);
    }
}
