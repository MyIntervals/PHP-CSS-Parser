<?php

declare(strict_types=1);

namespace Sabberworm\CSS;

class OutputFormat
{
    /**
     * Value format: `"` means double-quote, `'` means single-quote
     *
     * @var string
     */
    private $stringQuotingType = '"';

    /**
     * Output RGB colors in hash notation if possible
     *
     * @var bool
     */
    private $usesRgbHashNotation = true;

    /**
     * Declaration format
     *
     * Semicolon after the last rule of a declaration block can be omitted. To do that, set this false.
     *
     * @var bool
     */
    private $renderSemicolonAfterLastRule = true;

    /**
     * Spacing
     * Note that these strings are not sanity-checked: the value should only consist of whitespace
     * Any newline character will be indented according to the current level.
     * The triples (After, Before, Between) can be set using a wildcard
     * (e.g. `$outputFormat->set('Space*Rules', "\n");`)
     *
     * @var string
     */
    private $spaceAfterRuleName = ' ';

    /**
     * @var string
     */
    private $spaceBeforeRules = '';

    /**
     * @var string
     */
    private $spaceAfterRules = '';

    /**
     * @var string
     */
    private $spaceBetweenRules = '';

    /**
     * @var string
     */
    private $sSpaceBeforeBlocks = '';

    /**
     * @var string
     */
    private $sSpaceAfterBlocks = '';

    /**
     * @var string
     */
    private $sSpaceBetweenBlocks = "\n";

    /**
     * Content injected in and around at-rule blocks.
     *
     * @var string
     */
    private $sBeforeAtRuleBlock = '';

    /**
     * @var string
     */
    private $sAfterAtRuleBlock = '';

    /**
     * This is what’s printed before and after the comma if a declaration block contains multiple selectors.
     *
     * @var string
     */
    private $sSpaceBeforeSelectorSeparator = '';

    /**
     * @var string
     */
    private $sSpaceAfterSelectorSeparator = ' ';

    /**
     * This is what’s inserted before the separator in value lists, by default.
     *
     * @var string
     */
    private $sSpaceBeforeListArgumentSeparator = '';

    /**
     * Keys are separators (e.g. `,`).  Values are the space sequence to insert, or an empty string.
     *
     * @var array<non-empty-string, string>
     */
    private $aSpaceBeforeListArgumentSeparators = [];

    /**
     * This is what’s inserted after the separator in value lists, by default.
     *
     * @var string
     */
    private $sSpaceAfterListArgumentSeparator = '';

    /**
     * Keys are separators (e.g. `,`).  Values are the space sequence to insert, or an empty string.
     *
     * @var array<non-empty-string, string>
     */
    private $aSpaceAfterListArgumentSeparators = [];

    /**
     * @var string
     */
    private $sSpaceBeforeOpeningBrace = ' ';

    /**
     * Content injected in and around declaration blocks.
     *
     * @var string
     */
    private $sBeforeDeclarationBlock = '';

    /**
     * @var string
     */
    private $sAfterDeclarationBlockSelectors = '';

    /**
     * @var string
     */
    private $sAfterDeclarationBlock = '';

    /**
     * Indentation character(s) per level. Only applicable if newlines are used in any of the spacing settings.
     *
     * @var string
     */
    private $sIndentation = "\t";

    /**
     * Output exceptions.
     *
     * @var bool
     */
    private $bIgnoreExceptions = false;

    /**
     * Render comments for lists and RuleSets
     *
     * @var bool
     */
    private $bRenderComments = false;

    /**
     * @var OutputFormatter|null
     */
    private $outputFormatter;

    /**
     * @var OutputFormat|null
     */
    private $nextLevelFormat;

    /**
     * @var int
     */
    private $iIndentationLevel = 0;

    public function __construct() {}

    /**
     * @param array<array-key, string>|string $names
     * @param mixed $value
     *
     * @return self|false
     */
    public function set($names, $value)
    {
        $aVarPrefixes = ['a', 's', 'm', 'b', 'f', 'o', 'c', 'i'];
        if (\is_string($names) && \strpos($names, '*') !== false) {
            $names =
                [
                    \str_replace('*', 'Before', $names),
                    \str_replace('*', 'Between', $names),
                    \str_replace('*', 'After', $names),
                ];
        } elseif (!\is_array($names)) {
            $names = [$names];
        }
        foreach ($aVarPrefixes as $prefix) {
            $bDidReplace = false;
            foreach ($names as $name) {
                $sFieldName = $prefix . \ucfirst($name);
                if (isset($this->$sFieldName)) {
                    $this->$sFieldName = $value;
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
     * @param non-empty-string $sMethodName
     * @param array<array-key, mixed> $arguments
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function __call(string $sMethodName, array $arguments)
    {
        if (\method_exists(OutputFormatter::class, $sMethodName)) {
            // @deprecated since 8.8.0, will be removed in 9.0.0. Call the method on the formatter directly instead.
            return \call_user_func_array([$this->getFormatter(), $sMethodName], $arguments);
        } else {
            throw new \Exception('Unknown OutputFormat method called: ' . $sMethodName);
        }
    }

    /**
     * @internal
     */
    public function getStringQuotingType(): string
    {
        return $this->stringQuotingType;
    }

    /**
     * @return $this fluent interface
     */
    public function setStringQuotingType(string $quotingType): self
    {
        $this->stringQuotingType = $quotingType;

        return $this;
    }

    /**
     * @internal
     */
    public function usesRgbHashNotation(): bool
    {
        return $this->usesRgbHashNotation;
    }

    /**
     * @return $this fluent interface
     */
    public function setRGBHashNotation(bool $usesRgbHashNotation): self
    {
        $this->usesRgbHashNotation = $usesRgbHashNotation;

        return $this;
    }

    /**
     * @internal
     */
    public function shouldRenderSemicolonAfterLastRule(): bool
    {
        return $this->renderSemicolonAfterLastRule;
    }

    /**
     * @return $this fluent interface
     */
    public function setSemicolonAfterLastRule(bool $renderSemicolonAfterLastRule): self
    {
        $this->renderSemicolonAfterLastRule = $renderSemicolonAfterLastRule;

        return $this;
    }

    /**
     * @internal
     */
    public function getSpaceAfterRuleName(): string
    {
        return $this->spaceAfterRuleName;
    }

    /**
     * @return $this fluent interface
     */
    public function setSpaceAfterRuleName(string $whitespace): self
    {
        $this->spaceAfterRuleName = $whitespace;

        return $this;
    }

    /**
     * @internal
     */
    public function getSpaceBeforeRules(): string
    {
        return $this->spaceBeforeRules;
    }

    /**
     * @return $this fluent interface
     */
    public function setSpaceBeforeRules(string $whitespace): self
    {
        $this->spaceBeforeRules = $whitespace;

        return $this;
    }

    /**
     * @internal
     */
    public function getSpaceAfterRules(): string
    {
        return $this->spaceAfterRules;
    }

    /**
     * @return $this fluent interface
     */
    public function setSpaceAfterRules(string $whitespace): self
    {
        $this->spaceAfterRules = $whitespace;

        return $this;
    }

    /**
     * @internal
     */
    public function getSpaceBetweenRules(): string
    {
        return $this->spaceBetweenRules;
    }

    /**
     * @return $this fluent interface
     */
    public function setSpaceBetweenRules(string $whitespace): self
    {
        $this->spaceBetweenRules = $whitespace;

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
        if ($this->nextLevelFormat === null) {
            $this->nextLevelFormat = clone $this;
            $this->nextLevelFormat->iIndentationLevel++;
            $this->nextLevelFormat->outputFormatter = null;
        }
        return $this->nextLevelFormat;
    }

    public function beLenient(): void
    {
        $this->bIgnoreExceptions = true;
    }

    /**
     * @internal since 8.8.0
     */
    public function getFormatter(): OutputFormatter
    {
        if ($this->outputFormatter === null) {
            $this->outputFormatter = new OutputFormatter($this);
        }

        return $this->outputFormatter;
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
