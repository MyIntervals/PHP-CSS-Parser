<?php

namespace Sabberworm\CSS;

/**
 * Class OutputFormat
 *
 * @method OutputFormat setSemicolonAfterLastRule(bool $bSemicolonAfterLastRule) Set whether semicolons are added after
 *     last rule.
 */
class OutputFormat
{
    /**
     * Value format: `"` means double-quote, `'` means single-quote
     *
     * @var string
     */
    public $sStringQuotingType = '"';

    /**
     * Output RGB colors in hash notation if possible
     *
     * @var string
     */
    public $bRGBHashNotation = true;

    /**
     * Declaration format
     *
     * Semicolon after the last rule of a declaration block can be omitted. To do that, set this false.
     *
     * @var bool
     */
    public $bSemicolonAfterLastRule = true;

    /**
     * Spacing
     * Note that these strings are not sanity-checked: the value should only consist of whitespace
     * Any newline character will be indented according to the current level.
     * The triples (After, Before, Between) can be set using a wildcard (e.g. `$oFormat->set('Space*Rules', "\n");`)
     */
    public $sSpaceAfterRuleName = ' ';

    public $sSpaceBeforeRules = '';

    public $sSpaceAfterRules = '';

    public $sSpaceBetweenRules = '';

    public $sSpaceBeforeBlocks = '';

    public $sSpaceAfterBlocks = '';

    public $sSpaceBetweenBlocks = "\n";

    /**
     * Content injected in and around at-rule blocks.
     *
     * @var string
     */
    public $sBeforeAtRuleBlock = '';

    public $sAfterAtRuleBlock = '';

    /**
     * This is what’s printed before and after the comma if a declaration block contains multiple selectors.
     *
     * @var string
     */
    public $sSpaceBeforeSelectorSeparator = '';

    public $sSpaceAfterSelectorSeparator = ' ';

    /**
     * This is what’s printed after the comma of value lists
     *
     * @var string
     */
    public $sSpaceBeforeListArgumentSeparator = '';

    public $sSpaceAfterListArgumentSeparator = '';

    public $sSpaceBeforeOpeningBrace = ' ';

    /**
     * Content injected in and around declaration blocks.
     *
     * @var string
     */
    public $sBeforeDeclarationBlock = '';

    public $sAfterDeclarationBlockSelectors = '';

    public $sAfterDeclarationBlock = '';

    /**
     * Indentation character(s) per level. Only applicable if newlines are used in any of the spacing settings.
     *
     * @var string
     */
    public $sIndentation = "\t";

    /**
     * Output exceptions.
     *
     * @var bool
     */
    public $bIgnoreExceptions = false;

    private $oFormatter = null;

    private $oNextLevelFormat = null;

    private $iIndentationLevel = 0;

    public function __construct()
    {
    }

    public function get($sName)
    {
        $aVarPrefixes = ['a', 's', 'm', 'b', 'f', 'o', 'c', 'i'];
        foreach ($aVarPrefixes as $sPrefix) {
            $sFieldName = $sPrefix . ucfirst($sName);
            if (isset($this->$sFieldName)) {
                return $this->$sFieldName;
            }
        }
        return null;
    }

    /**
     * @param array<array-key, string>|string $aNames
     */
    public function set($aNames, $mValue)
    {
        $aVarPrefixes = ['a', 's', 'm', 'b', 'f', 'o', 'c', 'i'];
        if (is_string($aNames) && strpos($aNames, '*') !== false) {
            $aNames =
                [
                    str_replace('*', 'Before', $aNames),
                    str_replace('*', 'Between', $aNames),
                    str_replace('*', 'After', $aNames),
                ];
        } elseif (!is_array($aNames)) {
            $aNames = [$aNames];
        }
        foreach ($aVarPrefixes as $sPrefix) {
            $bDidReplace = false;
            foreach ($aNames as $sName) {
                $sFieldName = $sPrefix . ucfirst($sName);
                if (isset($this->$sFieldName)) {
                    $this->$sFieldName = $mValue;
                    $bDidReplace = true;
                }
            }
            if ($bDidReplace) {
                return $this;
            }
        }
        // Break the chain so the user knows this option is invalid
        return false;
    }

    public function __call($sMethodName, array $aArguments)
    {
        if (strpos($sMethodName, 'set') === 0) {
            return $this->set(substr($sMethodName, 3), $aArguments[0]);
        } elseif (strpos($sMethodName, 'get') === 0) {
            return $this->get(substr($sMethodName, 3));
        } elseif (method_exists(OutputFormatter::class, $sMethodName)) {
            return call_user_func_array([$this->getFormatter(), $sMethodName], $aArguments);
        } else {
            throw new \Exception('Unknown OutputFormat method called: ' . $sMethodName);
        }
    }

    public function indentWithTabs($iNumber = 1)
    {
        return $this->setIndentation(str_repeat("\t", $iNumber));
    }

    public function indentWithSpaces($iNumber = 2)
    {
        return $this->setIndentation(str_repeat(" ", $iNumber));
    }

    public function nextLevel()
    {
        if ($this->oNextLevelFormat === null) {
            $this->oNextLevelFormat = clone $this;
            $this->oNextLevelFormat->iIndentationLevel++;
            $this->oNextLevelFormat->oFormatter = null;
        }
        return $this->oNextLevelFormat;
    }

    public function beLenient()
    {
        $this->bIgnoreExceptions = true;
    }

    public function getFormatter()
    {
        if ($this->oFormatter === null) {
            $this->oFormatter = new OutputFormatter($this);
        }
        return $this->oFormatter;
    }

    public function level()
    {
        return $this->iIndentationLevel;
    }

    /**
     * Create format.
     *
     * @return OutputFormat Format.
     */
    public static function create()
    {
        return new OutputFormat();
    }

    /**
     * Create compact format.
     *
     * @return OutputFormat Format.
     */
    public static function createCompact()
    {
        $format = self::create();
        $format->set('Space*Rules', "")->set('Space*Blocks', "")->setSpaceAfterRuleName('')
            ->setSpaceBeforeOpeningBrace('')->setSpaceAfterSelectorSeparator('');
        return $format;
    }

    /**
     * Create pretty format.
     *
     * @return OutputFormat Format.
     */
    public static function createPretty()
    {
        $format = self::create();
        $format->set('Space*Rules', "\n")->set('Space*Blocks', "\n")
            ->setSpaceBetweenBlocks("\n\n")->set('SpaceAfterListArgumentSeparator', ['default' => '', ',' => ' ']);
        return $format;
    }
}
