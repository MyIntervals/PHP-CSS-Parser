<?php

declare(strict_types=1);

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

    /**
     * @var string
     */
    public $sSpaceBeforeRules = '';

    /**
     * @var string
     */
    public $sSpaceAfterRules = '';

    /**
     * @var string
     */
    public $sSpaceBetweenRules = '';

    /**
     * @var string
     */
    public $sSpaceBeforeBlocks = '';

    /**
     * @var string
     */
    public $sSpaceAfterBlocks = '';

    /**
     * @var string
     */
    public $sSpaceBetweenBlocks = "\n";

    /**
     * Content injected in and around at-rule blocks.
     *
     * @var string
     */
    public $sBeforeAtRuleBlock = '';

    /**
     * @var string
     */
    public $sAfterAtRuleBlock = '';

    /**
     * This is what’s printed before and after the comma if a declaration block contains multiple selectors.
     *
     * @var string
     */
    public $sSpaceBeforeSelectorSeparator = '';

    /**
     * @var string
     */
    public $sSpaceAfterSelectorSeparator = ' ';

    /**
     * This is what’s inserted before the separator in value lists, by default.
     *
     * `array` is deprecated in version 8.8.0, and will be removed in version 9.0.0.
     * To set the spacing for specific separators, use {@see $aSpaceBeforeListArgumentSeparators} instead.
     *
     * @var string|array<non-empty-string, string>
     */
    public $sSpaceBeforeListArgumentSeparator = '';

    /**
     * Keys are separators (e.g. `,`).  Values are the space sequence to insert, or an empty string.
     *
     * @var array<non-empty-string, string>
     */
    public $aSpaceBeforeListArgumentSeparators = [];

    /**
     * This is what’s inserted after the separator in value lists, by default.
     *
     * `array` is deprecated in version 8.8.0, and will be removed in version 9.0.0.
     * To set the spacing for specific separators, use {@see $aSpaceAfterListArgumentSeparators} instead.
     *
     * @var string|array<non-empty-string, string>
     */
    public $sSpaceAfterListArgumentSeparator = '';

    /**
     * Keys are separators (e.g. `,`).  Values are the space sequence to insert, or an empty string.
     *
     * @var array<non-empty-string, string>
     */
    public $aSpaceAfterListArgumentSeparators = [];

    /**
     * @var string
     */
    public $sSpaceBeforeOpeningBrace = ' ';

    /**
     * Content injected in and around declaration blocks.
     *
     * @var string
     */
    public $sBeforeDeclarationBlock = '';

    /**
     * @var string
     */
    public $sAfterDeclarationBlockSelectors = '';

    /**
     * @var string
     */
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

    /**
     * Render comments for lists and RuleSets
     *
     * @var bool
     */
    public $bRenderComments = false;

    /**
     * @var OutputFormatter|null
     */
    private $oFormatter = null;

    /**
     * @var OutputFormat|null
     */
    private $oNextLevelFormat = null;

    /**
     * @var int
     */
    private $iIndentationLevel = 0;

    public function __construct() {}

    /**
     * @return string|int|bool|null
     */
    public function get(string $sName)
    {
        $aVarPrefixes = ['a', 's', 'm', 'b', 'f', 'o', 'c', 'i'];
        foreach ($aVarPrefixes as $sPrefix) {
            $sFieldName = $sPrefix . \ucfirst($sName);
            if (isset($this->$sFieldName)) {
                return $this->$sFieldName;
            }
        }
        return null;
    }

    /**
     * @param array<array-key, string>|string $aNames
     * @param mixed $mValue
     *
     * @return self|false
     */
    public function set($aNames, $mValue)
    {
        $aVarPrefixes = ['a', 's', 'm', 'b', 'f', 'o', 'c', 'i'];
        if (\is_string($aNames) && \strpos($aNames, '*') !== false) {
            $aNames =
                [
                    \str_replace('*', 'Before', $aNames),
                    \str_replace('*', 'Between', $aNames),
                    \str_replace('*', 'After', $aNames),
                ];
        } elseif (!\is_array($aNames)) {
            $aNames = [$aNames];
        }
        foreach ($aVarPrefixes as $sPrefix) {
            $bDidReplace = false;
            foreach ($aNames as $sName) {
                $sFieldName = $sPrefix . \ucfirst($sName);
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

    /**
     * @param array<array-key, mixed> $aArguments
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function __call(string $sMethodName, array $aArguments)
    {
        if (\strpos($sMethodName, 'set') === 0) {
            return $this->set(\substr($sMethodName, 3), $aArguments[0]);
        } elseif (\strpos($sMethodName, 'get') === 0) {
            return $this->get(\substr($sMethodName, 3));
        } elseif (\method_exists(OutputFormatter::class, $sMethodName)) {
            return \call_user_func_array([$this->getFormatter(), $sMethodName], $aArguments);
        } else {
            throw new \Exception('Unknown OutputFormat method called: ' . $sMethodName);
        }
    }

    /**
     * @return $this fluent interface
     */
    public function indentWithTabs(int $numberOfTabs = 1): self
    {
        return $this->setIndentation(\str_repeat("\t", $numberOfTabs));
    }

    /**
     * @return $this fluent interface
     */
    public function indentWithSpaces(int $numberOfSpaces = 2): self
    {
        return $this->setIndentation(\str_repeat(' ', $numberOfSpaces));
    }

    public function nextLevel(): self
    {
        if ($this->oNextLevelFormat === null) {
            $this->oNextLevelFormat = clone $this;
            $this->oNextLevelFormat->iIndentationLevel++;
            $this->oNextLevelFormat->oFormatter = null;
        }
        return $this->oNextLevelFormat;
    }

    public function beLenient(): void
    {
        $this->bIgnoreExceptions = true;
    }

    public function getFormatter(): OutputFormatter
    {
        if ($this->oFormatter === null) {
            $this->oFormatter = new OutputFormatter($this);
        }
        return $this->oFormatter;
    }

    /**
     * Creates an instance of this class without any particular formatting settings.
     */
    public static function create(): self
    {
        return new OutputFormat();
    }

    /**
     * Creates an instance of this class with a preset for compact formatting.
     */
    public static function createCompact(): self
    {
        $format = self::create();
        $format->set('Space*Rules', '')
            ->set('Space*Blocks', '')
            ->setSpaceAfterRuleName('')
            ->setSpaceBeforeOpeningBrace('')
            ->setSpaceAfterSelectorSeparator('')
            ->setRenderComments(false);
        return $format;
    }

    /**
     * Creates an instance of this class with a preset for pretty formatting.
     */
    public static function createPretty(): self
    {
        $format = self::create();
        $format->set('Space*Rules', "\n")
            ->set('Space*Blocks', "\n")
            ->setSpaceBetweenBlocks("\n\n")
            ->set('SpaceAfterListArgumentSeparators', [',' => ' '])
            ->setRenderComments(true);
        return $format;
    }
}
