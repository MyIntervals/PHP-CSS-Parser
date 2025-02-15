<?php

declare(strict_types=1);

namespace Sabberworm\CSS;

class OutputFormat
{
    /**
     * Value format: `"` means double-quote, `'` means single-quote
     *
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sStringQuotingType = '"';

    /**
     * Output RGB colors in hash notation if possible
     *
     * @var bool
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $bRGBHashNotation = true;

    /**
     * Declaration format
     *
     * Semicolon after the last rule of a declaration block can be omitted. To do that, set this false.
     *
     * @var bool
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $bSemicolonAfterLastRule = true;

    /**
     * Spacing
     * Note that these strings are not sanity-checked: the value should only consist of whitespace
     * Any newline character will be indented according to the current level.
     * The triples (After, Before, Between) can be set using a wildcard (e.g. `$oFormat->set('Space*Rules', "\n");`)
     *
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sSpaceAfterRuleName = ' ';

    /**
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sSpaceBeforeRules = '';

    /**
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sSpaceAfterRules = '';

    /**
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sSpaceBetweenRules = '';

    /**
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sSpaceBeforeBlocks = '';

    /**
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sSpaceAfterBlocks = '';

    /**
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sSpaceBetweenBlocks = "\n";

    /**
     * Content injected in and around at-rule blocks.
     *
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sBeforeAtRuleBlock = '';

    /**
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sAfterAtRuleBlock = '';

    /**
     * This is what’s printed before and after the comma if a declaration block contains multiple selectors.
     *
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sSpaceBeforeSelectorSeparator = '';

    /**
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sSpaceAfterSelectorSeparator = ' ';

    /**
     * This is what’s inserted before the separator in value lists, by default.
     *
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sSpaceBeforeListArgumentSeparator = '';

    /**
     * Keys are separators (e.g. `,`).  Values are the space sequence to insert, or an empty string.
     *
     * @var array<non-empty-string, string>
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $aSpaceBeforeListArgumentSeparators = [];

    /**
     * This is what’s inserted after the separator in value lists, by default.
     *
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sSpaceAfterListArgumentSeparator = '';

    /**
     * Keys are separators (e.g. `,`).  Values are the space sequence to insert, or an empty string.
     *
     * @var array<non-empty-string, string>
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $aSpaceAfterListArgumentSeparators = [];

    /**
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sSpaceBeforeOpeningBrace = ' ';

    /**
     * Content injected in and around declaration blocks.
     *
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sBeforeDeclarationBlock = '';

    /**
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sAfterDeclarationBlockSelectors = '';

    /**
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sAfterDeclarationBlock = '';

    /**
     * Indentation character(s) per level. Only applicable if newlines are used in any of the spacing settings.
     *
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sIndentation = "\t";

    /**
     * Output exceptions.
     *
     * @var bool
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $bIgnoreExceptions = false;

    /**
     * Render comments for lists and RuleSets
     *
     * @var bool
     *
     * @internal since 8.8.0, will be made private in 9.0.0
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
        foreach ($aVarPrefixes as $prefix) {
            $sFieldName = $prefix . \ucfirst($sName);
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
        foreach ($aVarPrefixes as $prefix) {
            $bDidReplace = false;
            foreach ($aNames as $sName) {
                $sFieldName = $prefix . \ucfirst($sName);
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
        if (\method_exists(OutputFormatter::class, $sMethodName)) {
            return \call_user_func_array([$this->getFormatter(), $sMethodName], $aArguments);
        } else {
            throw new \Exception('Unknown OutputFormat method called: ' . $sMethodName);
        }
    }

    /**
     * @internal
     */
    public function getStringQuotingType(): string
    {
        return $this->sStringQuotingType;
    }

    /**
     * @return $this fluent interface
     */
    public function setStringQuotingType(string $quotingType): self
    {
        $this->sStringQuotingType = $quotingType;

        return $this;
    }

    /**
     * @internal
     */
    public function getRGBHashNotation(): bool
    {
        return $this->bRGBHashNotation;
    }

    /**
     * @return $this fluent interface
     */
    public function setRGBHashNotation(bool $rgbHashNotation): self
    {
        $this->bRGBHashNotation = $rgbHashNotation;

        return $this;
    }

    /**
     * @internal
     */
    public function getSemicolonAfterLastRule(): bool
    {
        return $this->bSemicolonAfterLastRule;
    }

    /**
     * @return $this fluent interface
     */
    public function setSemicolonAfterLastRule(bool $semicolonAfterLastRule): self
    {
        $this->bSemicolonAfterLastRule = $semicolonAfterLastRule;

        return $this;
    }

    /**
     * @internal
     */
    public function getSpaceAfterRuleName(): string
    {
        return $this->sSpaceAfterRuleName;
    }

    /**
     * @return $this fluent interface
     */
    public function setSpaceAfterRuleName(string $whitespace): self
    {
        $this->sSpaceAfterRuleName = $whitespace;

        return $this;
    }

    /**
     * @internal
     */
    public function getSpaceBeforeRules(): string
    {
        return $this->sSpaceBeforeRules;
    }

    /**
     * @return $this fluent interface
     */
    public function setSpaceBeforeRules(string $whitespace): self
    {
        $this->sSpaceBeforeRules = $whitespace;

        return $this;
    }

    /**
     * @internal
     */
    public function getSpaceAfterRules(): string
    {
        return $this->sSpaceAfterRules;
    }

    /**
     * @return $this fluent interface
     */
    public function setSpaceAfterRules(string $whitespace): self
    {
        $this->sSpaceAfterRules = $whitespace;

        return $this;
    }

    /**
     * @internal
     */
    public function getSpaceBetweenRules(): string
    {
        return $this->sSpaceBetweenRules;
    }

    /**
     * @return $this fluent interface
     */
    public function setSpaceBetweenRules(string $whitespace): self
    {
        $this->sSpaceBetweenRules = $whitespace;

        return $this;
    }

    /**
     * @internal
     */
    public function getSpaceBeforeBlocks(): string
    {
        return $this->sSpaceBeforeBlocks;
    }

    /**
     * @return $this fluent interface
     */
    public function setSpaceBeforeBlocks(string $whitespace): self
    {
        $this->sSpaceBeforeBlocks = $whitespace;

        return $this;
    }

    /**
     * @internal
     */
    public function getSpaceAfterBlocks(): string
    {
        return $this->sSpaceAfterBlocks;
    }

    /**
     * @return $this fluent interface
     */
    public function setSpaceAfterBlocks(string $whitespace): self
    {
        $this->sSpaceAfterBlocks = $whitespace;

        return $this;
    }

    /**
     * @internal
     */
    public function getSpaceBetweenBlocks(): string
    {
        return $this->sSpaceBetweenBlocks;
    }

    /**
     * @return $this fluent interface
     */
    public function setSpaceBetweenBlocks(string $whitespace): self
    {
        $this->sSpaceBetweenBlocks = $whitespace;

        return $this;
    }

    /**
     * @internal
     */
    public function getBeforeAtRuleBlock(): string
    {
        return $this->sBeforeAtRuleBlock;
    }

    /**
     * @return $this fluent interface
     */
    public function setBeforeAtRuleBlock(string $content): self
    {
        $this->sBeforeAtRuleBlock = $content;

        return $this;
    }

    /**
     * @internal
     */
    public function getAfterAtRuleBlock(): string
    {
        return $this->sAfterAtRuleBlock;
    }

    /**
     * @return $this fluent interface
     */
    public function setAfterAtRuleBlock(string $content): self
    {
        $this->sAfterAtRuleBlock = $content;

        return $this;
    }

    /**
     * @internal
     */
    public function getSpaceBeforeSelectorSeparator(): string
    {
        return $this->sSpaceBeforeSelectorSeparator;
    }

    /**
     * @return $this fluent interface
     */
    public function setSpaceBeforeSelectorSeparator(string $whitespace): self
    {
        $this->sSpaceBeforeSelectorSeparator = $whitespace;

        return $this;
    }

    /**
     * @internal
     */
    public function getSpaceAfterSelectorSeparator(): string
    {
        return $this->sSpaceAfterSelectorSeparator;
    }

    /**
     * @return $this fluent interface
     */
    public function setSpaceAfterSelectorSeparator(string $whitespace): self
    {
        $this->sSpaceAfterSelectorSeparator = $whitespace;

        return $this;
    }

    /**
     * @internal
     */
    public function getSpaceBeforeListArgumentSeparator(): string
    {
        return $this->sSpaceBeforeListArgumentSeparator;
    }

    /**
     * @return $this fluent interface
     */
    public function setSpaceBeforeListArgumentSeparator(string $whitespace): self
    {
        $this->sSpaceBeforeListArgumentSeparator = $whitespace;

        return $this;
    }

    /**
     * @return array<non-empty-string, string>
     *
     * @internal
     */
    public function getSpaceBeforeListArgumentSeparators(): array
    {
        return $this->aSpaceBeforeListArgumentSeparators;
    }

    /**
     * @param array<non-empty-string, string> $separatorSpaces
     *
     * @return $this fluent interface
     */
    public function setSpaceBeforeListArgumentSeparators(array $separatorSpaces): self
    {
        $this->aSpaceBeforeListArgumentSeparators = $separatorSpaces;

        return $this;
    }

    /**
     * @internal
     */
    public function getSpaceAfterListArgumentSeparator(): string
    {
        return $this->sSpaceAfterListArgumentSeparator;
    }

    /**
     * @return $this fluent interface
     */
    public function setSpaceAfterListArgumentSeparator(string $whitespace): self
    {
        $this->sSpaceAfterListArgumentSeparator = $whitespace;

        return $this;
    }

    /**
     * @return array<non-empty-string, string>
     *
     * @internal
     */
    public function getSpaceAfterListArgumentSeparators(): array
    {
        return $this->aSpaceAfterListArgumentSeparators;
    }

    /**
     * @param array<non-empty-string, string> $separatorSpaces
     *
     * @return $this fluent interface
     */
    public function setSpaceAfterListArgumentSeparators(array $separatorSpaces): self
    {
        $this->aSpaceAfterListArgumentSeparators = $separatorSpaces;

        return $this;
    }

    /**
     * @internal
     */
    public function getSpaceBeforeOpeningBrace(): string
    {
        return $this->sSpaceBeforeOpeningBrace;
    }

    /**
     * @return $this fluent interface
     */
    public function setSpaceBeforeOpeningBrace(string $whitespace): self
    {
        $this->sSpaceBeforeOpeningBrace = $whitespace;

        return $this;
    }

    /**
     * @internal
     */
    public function getBeforeDeclarationBlock(): string
    {
        return $this->sBeforeDeclarationBlock;
    }

    /**
     * @return $this fluent interface
     */
    public function setBeforeDeclarationBlock(string $content): self
    {
        $this->sBeforeDeclarationBlock = $content;

        return $this;
    }

    /**
     * @internal
     */
    public function getAfterDeclarationBlockSelectors(): string
    {
        return $this->sAfterDeclarationBlockSelectors;
    }

    /**
     * @return $this fluent interface
     */
    public function setAfterDeclarationBlockSelectors(string $content): self
    {
        $this->sAfterDeclarationBlockSelectors = $content;

        return $this;
    }

    /**
     * @internal
     */
    public function getAfterDeclarationBlock(): string
    {
        return $this->sAfterDeclarationBlock;
    }

    /**
     * @return $this fluent interface
     */
    public function setAfterDeclarationBlock(string $content): self
    {
        $this->sAfterDeclarationBlock = $content;

        return $this;
    }

    /**
     * @internal
     */
    public function getIndentation(): string
    {
        return $this->sIndentation;
    }

    /**
     * @return $this fluent interface
     */
    public function setIndentation(string $indentation): self
    {
        $this->sIndentation = $indentation;

        return $this;
    }

    /**
     * @internal
     */
    public function getIgnoreExceptions(): bool
    {
        return $this->bIgnoreExceptions;
    }

    /**
     * @return $this fluent interface
     */
    public function setIgnoreExceptions(bool $ignoreExceptions): self
    {
        $this->bIgnoreExceptions = $ignoreExceptions;

        return $this;
    }

    /**
     * @internal
     */
    public function getRenderComments(): bool
    {
        return $this->bRenderComments;
    }

    /**
     * @return $this fluent interface
     */
    public function setRenderComments(bool $renderComments): self
    {
        $this->bRenderComments = $renderComments;

        return $this;
    }

    /**
     * @internal
     */
    public function getIndentationLevel(): int
    {
        return $this->iIndentationLevel;
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

    /**
     * @internal since V8.8.0
     */
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
        $format
            ->setSpaceBeforeRules('')
            ->setSpaceBetweenRules('')
            ->setSpaceAfterRules('')
            ->setSpaceBeforeBlocks('')
            ->setSpaceBetweenBlocks('')
            ->setSpaceAfterBlocks('')
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
        $format
            ->setSpaceBeforeRules("\n")
            ->setSpaceBetweenRules("\n")
            ->setSpaceAfterRules("\n")
            ->setSpaceBeforeBlocks("\n")
            ->setSpaceBetweenBlocks("\n\n")
            ->setSpaceAfterBlocks("\n")
            ->setSpaceAfterListArgumentSeparators([',' => ' '])
            ->setRenderComments(true);

        return $format;
    }
}
