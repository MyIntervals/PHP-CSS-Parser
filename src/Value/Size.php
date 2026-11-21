<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\ShortClassNameProvider;

/**
 * A `Size` represents a '<dimension>'.
 *
 * @see https://drafts.csswg.org/css-values-4/#dimensions
 *
 * It comprises a '<number>' (which is required, though may need to be an '<integer>' for some unit values),
 * and a unit (which may be optional if the number is zero, or in some other cases).
 */
class Size extends PrimitiveValue
{
    use ShortClassNameProvider;

    /**
     * vh/vw/vm(ax)/vmin/rem are absolute insofar as they don’t scale to the immediate parent (only the viewport)
     */
    private const ABSOLUTE_SIZE_UNITS = [
        'px',
        'pt',
        'pc',
        'cm',
        'mm',
        'mozmm',
        'in',
        'vh',
        'dvh',
        'svh',
        'lvh',
        'vw',
        'dvw',
        'svw',
        'lvw',
        'vi',
        'dvi',
        'svi',
        'lvi',
        'vb',
        'dvb',
        'svb',
        'lvb',
        'vmin',
        'dvmin',
        'svmin',
        'lvmin',
        'vmax',
        'dvmax',
        'svmax',
        'lvmax',
        'rem',
        'rex',
        'rch',
        'rcap',
        'ric',
        'rlh',
        'Q',
    ];

    private const RELATIVE_SIZE_UNITS = [
        '%',
        'em',
        'ex',
        'ch',
        'cap',
        'ic',
        'lh',
        'fr',
        'cqw',
        'cqh',
        'cqi',
        'cqb',
        'cqmin',
        'cqmax',
    ];

    private const NON_SIZE_UNITS = [
        'deg',
        'grad',
        'rad',
        's',
        'ms',
        'turn',
        'Hz',
        'kHz',
        'dpi',
        'dpcm',
        'dppx',
        'x',
    ];

    /**
     * This is a mapping from lowercased dimension units to their expected rendering (e.g. "khz" -> "hKz"),
     * and includes all known dimension units.
     *
     * @var array<non-empty-lowercase-string, non-empty-string>|null
     */
    private static $SIZE_UNITS = null;

    /**
     * @var float
     */
    private $number;

    /**
     * @var string|null
     */
    private $unit;

    /**
     * @var bool
     */
    private $isColorComponent;

    /**
     * @param float|int|string $number
     * @param int<1, max>|null $lineNumber
     */
    public function __construct($number, ?string $unit = null, bool $isColorComponent = false, ?int $lineNumber = null)
    {
        parent::__construct($lineNumber);
        $this->number = (float) $number;
        $this->unit = $unit;
        $this->isColorComponent = $isColorComponent;
    }

    /**
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     *
     * @internal since V8.8.0
     */
    public static function parse(ParserState $parserState, bool $isColorComponent = false): Size
    {
        $number = '';
        if ($parserState->comes('-')) {
            $number .= $parserState->consume('-');
        }
        while (\is_numeric($parserState->peek()) || $parserState->comes('.') || $parserState->comes('e', true)) {
            if ($parserState->comes('.')) {
                $number .= $parserState->consume('.');
            } elseif ($parserState->comes('e', true)) {
                $lookahead = $parserState->peek(1, 1);
                if (\is_numeric($lookahead) || $lookahead === '+' || $lookahead === '-') {
                    $number .= $parserState->consume(2);
                } else {
                    break; // Reached the unit part of the number like "em" or "ex"
                }
            } else {
                $number .= $parserState->consume(1);
            }
        }

        if ($number === '') {
            // A 'dimension' requires a number.  What has arisen here is exceptional.  The caller should deal with it.
            throw new SourceException(
                'Number not found when attempting to parse dimension',
                $parserState->currentLine()
            );
        }

        // The unit could be `%`, a seqeunce of ASCII or unicode letters, or absent.
        // The letter sequence is not likely to be more than 5 characters (4 is the most currently),
        // so a limit is applied as an optimization.
        // The regex allows for an empty string for numbers without a unit, so exception handling is not required.
        $parsedUnit = $parserState->consumeExpression('/^(?:%|[\\p{L}]*+)/u', 5);
        $unit = $parsedUnit !== '' ? self::getUnitInRecommendedCasing($parsedUnit) : null;

        return new Size((float) $number, $unit, $isColorComponent, $parserState->currentLine());
    }

    /**
     * @return array<non-empty-lowercase-string, non-empty-string>
     *         Keys are known units in lowercase; values are in expected rendering format (e.g. "kHz").
     */
    private static function getSizeUnits(): array
    {
        if (!\is_array(self::$SIZE_UNITS)) {
            self::$SIZE_UNITS = [];
            $sizeUnits = \array_merge(self::ABSOLUTE_SIZE_UNITS, self::RELATIVE_SIZE_UNITS, self::NON_SIZE_UNITS);
            foreach ($sizeUnits as $sizeUnit) {
                self::$SIZE_UNITS[\strtolower($sizeUnit)] = $sizeUnit;
            }
        }

        return self::$SIZE_UNITS;
    }

    /**
     * @param non-empty-string $unit
     *
     * @return non-empty-string
     */
    private static function getUnitInRecommendedCasing(string $unit): string
    {
        return self::getSizeUnits()[\strtolower($unit)] ?? $unit;
    }

    public function setUnit(string $unit): void
    {
        $this->unit = $unit;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    /**
     * @param float|int|string $number
     */
    public function setNumber($number): void
    {
        $this->number = (float) $number;
    }

    public function getNumber(): float
    {
        return $this->number;
    }

    /**
     * @param float|int|string $number
     *
     * @deprecated in version 9.6, will be removed in v10.0.  Use `setNumber()` instead, which is a direct replacement.
     */
    public function setSize($number): void
    {
        $this->setNumber($number);
    }

    /**
     * @deprecated in version 9.6, will be removed in v10.0.  Use `getNumber()` instead, which is a direct replacement.
     */
    public function getSize(): float
    {
        return $this->getNumber();
    }

    public function isColorComponent(): bool
    {
        return $this->isColorComponent;
    }

    /**
     * @return bool
     * `true` if the stored number represents a length, e.g. of something on screen;
     * `false` if the unit is an angle, a duration, a frequency, a resolution, or the number is a component in
     * a `Color` object.
     */
    public function isLength(): bool
    {
        if (\in_array($this->unit, self::NON_SIZE_UNITS, true)) {
            return false;
        }
        return !$this->isColorComponent();
    }

    /**
     * @deprecated in version 9.6, will be removed in v10.0.  Use `isLength()` instead, which is a direct replacement.
     */
    public function isSize(): bool
    {
        return $this->isLength();
    }

    public function isRelative(): bool
    {
        if (\in_array($this->unit, self::RELATIVE_SIZE_UNITS, true)) {
            return true;
        }
        if ($this->unit === null && $this->number !== 0.0) {
            return true;
        }
        return false;
    }

    /**
     * @return non-empty-string
     */
    public function render(OutputFormat $outputFormat): string
    {
        // Floating point numbers have only 14-15 digits of precision.
        // Determine how many decimal places to include to avoid noise at the end.
        $magnitude = $this->number !== 0.0 ? (int) \ceil(\log10(\abs($this->number))) : 0;
        $numberOfDecimals = \max(15 - $magnitude, 0);

        $formattedNumber = \number_format($this->number, $numberOfDecimals, '.', ''); // no thousands separator

        if (\strpos($formattedNumber, '.') !== false) {
            // Remove trailing zeros after decimal point, and perhaps the point itself
            $formattedNumber = \rtrim(\rtrim($formattedNumber, '0'), '.');
        }
        // Remove leading zero
        $formattedNumber = \preg_replace('/^(-?+)0(?=\\.)/', '$1', $formattedNumber);

        return $formattedNumber . ($this->unit ?? '');
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
            'number' => $this->number,
            'unit' => $this->unit,
        ];
    }
}
