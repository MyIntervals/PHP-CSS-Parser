<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;

/**
 * A `Size` consists of a numeric `size` value and a unit.
 */
class Size extends PrimitiveValue
{
    /**
     * vh/vw/vm(ax)/vmin/rem are absolute insofar as they don’t scale to the immediate parent (only the viewport)
     *
     * @var array<int, string>
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
        'vmin',
        'vmax',
        'rem',
    ];

    /**
     * @var array<int, string>
     */
    private const RELATIVE_SIZE_UNITS = ['%', 'em', 'ex', 'ch', 'fr'];

    /**
     * @var array<int, string>
     */
    private const NON_SIZE_UNITS = ['deg', 'grad', 'rad', 's', 'ms', 'turn', 'Hz', 'kHz'];

    /**
     * @var array<int, array<string, string>>|null
     */
    private static $SIZE_UNITS = null;

    /**
     * @var float
     */
    private $size;

    /**
     * @var string|null
     */
    private $unit;

    /**
     * @var bool
     */
    private $isColorComponent;

    /**
     * @param float|int|string $size
     * @param string|null $unit
     * @param bool $isColorComponent
     * @param int<0, max> $lineNumber
     */
    public function __construct($size, $unit = null, $isColorComponent = false, $lineNumber = 0)
    {
        parent::__construct($lineNumber);
        $this->size = (float) $size;
        $this->unit = $unit;
        $this->isColorComponent = $isColorComponent;
    }

    /**
     * @param bool $isColorComponent
     *
     * @throws UnexpectedEOFException
     * @throws UnexpectedTokenException
     *
     * @internal since V8.8.0
     */
    public static function parse(ParserState $parserState, $isColorComponent = false): Size
    {
        $size = '';
        if ($parserState->comes('-')) {
            $size .= $parserState->consume('-');
        }
        while (\is_numeric($parserState->peek()) || $parserState->comes('.') || $parserState->comes('e', true)) {
            if ($parserState->comes('.')) {
                $size .= $parserState->consume('.');
            } elseif ($parserState->comes('e', true)) {
                $sLookahead = $parserState->peek(1, 1);
                if (\is_numeric($sLookahead) || $sLookahead === '+' || $sLookahead === '-') {
                    $size .= $parserState->consume(2);
                } else {
                    break; // Reached the unit part of the number like "em" or "ex"
                }
            } else {
                $size .= $parserState->consume(1);
            }
        }

        $unit = null;
        $sizeUnits = self::getSizeUnits();
        foreach ($sizeUnits as $length => &$aValues) {
            $sKey = \strtolower($parserState->peek($length));
            if (\array_key_exists($sKey, $aValues)) {
                if (($unit = $aValues[$sKey]) !== null) {
                    $parserState->consume($length);
                    break;
                }
            }
        }
        return new Size((float) $size, $unit, $isColorComponent, $parserState->currentLine());
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function getSizeUnits()
    {
        if (!\is_array(self::$SIZE_UNITS)) {
            self::$SIZE_UNITS = [];
            $sizeUnits = \array_merge(self::ABSOLUTE_SIZE_UNITS, self::RELATIVE_SIZE_UNITS, self::NON_SIZE_UNITS);
            foreach ($sizeUnits as $sizeUnit) {
                $tokenLength = \strlen($sizeUnit);
                if (!isset(self::$SIZE_UNITS[$tokenLength])) {
                    self::$SIZE_UNITS[$tokenLength] = [];
                }
                self::$SIZE_UNITS[$tokenLength][\strtolower($sizeUnit)] = $sizeUnit;
            }

            \krsort(self::$SIZE_UNITS, SORT_NUMERIC);
        }

        return self::$SIZE_UNITS;
    }

    /**
     * @param string $unit
     */
    public function setUnit($unit): void
    {
        $this->unit = $unit;
    }

    /**
     * @return string|null
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param float|int|string $size
     */
    public function setSize($size): void
    {
        $this->size = (float) $size;
    }

    /**
     * @return float
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return bool
     */
    public function isColorComponent()
    {
        return $this->isColorComponent;
    }

    /**
     * Returns whether the number stored in this Size really represents a size (as in a length of something on screen).
     *
     * Returns `false` if the unit is an angle, a duration, a frequency, or the number is a component in a `Color`
     * object.
     */
    public function isSize(): bool
    {
        if (\in_array($this->unit, self::NON_SIZE_UNITS, true)) {
            return false;
        }
        return !$this->isColorComponent();
    }

    public function isRelative(): bool
    {
        if (\in_array($this->unit, self::RELATIVE_SIZE_UNITS, true)) {
            return true;
        }
        if ($this->unit === null && $this->size != 0) {
            return true;
        }
        return false;
    }

    /**
     * @deprecated in V8.8.0, will be removed in V9.0.0. Use `render` instead.
     */
    public function __toString(): string
    {
        return $this->render(new OutputFormat());
    }

    public function render(OutputFormat $outputFormat): string
    {
        $locale = \localeconv();
        $decimalPoint = \preg_quote($locale['decimal_point'], '/');
        $size = \preg_match('/[\\d\\.]+e[+-]?\\d+/i', (string) $this->size)
            ? \preg_replace("/$decimalPoint?0+$/", '', \sprintf('%f', $this->size)) : (string) $this->size;

        return \preg_replace(["/$decimalPoint/", '/^(-?)0\\./'], ['.', '$1.'], $size) . ($this->unit ?? '');
    }
}
