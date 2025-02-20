<?php

declare(strict_types=1);

namespace Sabberworm\CSS;

use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\Parsing\OutputException;

class OutputFormatter
{
    /**
     * @var OutputFormat
     */
    private $outputFormat;

    public function __construct(OutputFormat $outputFormat)
    {
        $this->outputFormat = $outputFormat;
    }

    /**
     * @param non-empty-string $sName
     *
     * @throws \InvalidArgumentException
     */
    public function space(string $sName): string
    {
        switch ($sName) {
            case 'AfterRuleName':
                $sSpaceString = $this->outputFormat->getSpaceAfterRuleName();
                break;
            case 'BeforeRules':
                $sSpaceString = $this->outputFormat->getSpaceBeforeRules();
                break;
            case 'AfterRules':
                $sSpaceString = $this->outputFormat->getSpaceAfterRules();
                break;
            case 'BetweenRules':
                $sSpaceString = $this->outputFormat->getSpaceBetweenRules();
                break;
            case 'BeforeBlocks':
                $sSpaceString = $this->outputFormat->getSpaceBeforeBlocks();
                break;
            case 'AfterBlocks':
                $sSpaceString = $this->outputFormat->getSpaceAfterBlocks();
                break;
            case 'BetweenBlocks':
                $sSpaceString = $this->outputFormat->getSpaceBetweenBlocks();
                break;
            case 'BeforeSelectorSeparator':
                $sSpaceString = $this->outputFormat->getSpaceBeforeSelectorSeparator();
                break;
            case 'AfterSelectorSeparator':
                $sSpaceString = $this->outputFormat->getSpaceAfterSelectorSeparator();
                break;
            case 'BeforeOpeningBrace':
                $sSpaceString = $this->outputFormat->getSpaceBeforeOpeningBrace();
                break;
            case 'BeforeListArgumentSeparator':
                $sSpaceString = $this->outputFormat->getSpaceBeforeListArgumentSeparator();
                break;
            case 'AfterListArgumentSeparator':
                $sSpaceString = $this->outputFormat->getSpaceAfterListArgumentSeparator();
                break;
            default:
                throw new \InvalidArgumentException("Unknown space type: $sName", 1740049248);
        }

        return $this->prepareSpace($sSpaceString);
    }

    public function spaceAfterRuleName(): string
    {
        return $this->space('AfterRuleName');
    }

    public function spaceBeforeRules(): string
    {
        return $this->space('BeforeRules');
    }

    public function spaceAfterRules(): string
    {
        return $this->space('AfterRules');
    }

    public function spaceBetweenRules(): string
    {
        return $this->space('BetweenRules');
    }

    public function spaceBeforeBlocks(): string
    {
        return $this->space('BeforeBlocks');
    }

    public function spaceAfterBlocks(): string
    {
        return $this->space('AfterBlocks');
    }

    public function spaceBetweenBlocks(): string
    {
        return $this->space('BetweenBlocks');
    }

    public function spaceBeforeSelectorSeparator(): string
    {
        return $this->space('BeforeSelectorSeparator');
    }

    public function spaceAfterSelectorSeparator(): string
    {
        return $this->space('AfterSelectorSeparator');
    }

    /**
     * @param non-empty-string $sSeparator
     */
    public function spaceBeforeListArgumentSeparator(string $sSeparator): string
    {
        $spaceForSeparator = $this->outputFormat->getSpaceBeforeListArgumentSeparators();

        return $spaceForSeparator[$sSeparator] ?? $this->space('BeforeListArgumentSeparator');
    }

    /**
     * @param non-empty-string $sSeparator
     */
    public function spaceAfterListArgumentSeparator(string $sSeparator): string
    {
        $spaceForSeparator = $this->outputFormat->getSpaceAfterListArgumentSeparators();

        return $spaceForSeparator[$sSeparator] ?? $this->space('AfterListArgumentSeparator');
    }

    public function spaceBeforeOpeningBrace(): string
    {
        return $this->space('BeforeOpeningBrace');
    }

    /**
     * Runs the given code, either swallowing or passing exceptions, depending on the `ignoreExceptions` setting.
     */
    public function safely(callable $cCode): ?string
    {
        if ($this->outputFormat->getIgnoreExceptions()) {
            // If output exceptions are ignored, run the code with exception guards
            try {
                return $cCode();
            } catch (OutputException $e) {
                return null;
            } // Do nothing
        } else {
            // Run the code as-is
            return $cCode();
        }
    }

    /**
     * Clone of the `implode` function, but calls `render` with the current output format instead of `__toString()`.
     *
     * @param array<array-key, Renderable|string> $aValues
     */
    public function implode(string $sSeparator, array $aValues, bool $bIncreaseLevel = false): string
    {
        $result = '';
        $outputFormat = $this->outputFormat;
        if ($bIncreaseLevel) {
            $outputFormat = $outputFormat->nextLevel();
        }
        $bIsFirst = true;
        foreach ($aValues as $mValue) {
            if ($bIsFirst) {
                $bIsFirst = false;
            } else {
                $result .= $sSeparator;
            }
            if ($mValue instanceof Renderable) {
                $result .= $mValue->render($outputFormat);
            } else {
                $result .= $mValue;
            }
        }
        return $result;
    }

    public function removeLastSemicolon(string $sString): string
    {
        if ($this->outputFormat->getSemicolonAfterLastRule()) {
            return $sString;
        }
        $sString = \explode(';', $sString);
        if (\count($sString) < 2) {
            return $sString[0];
        }
        $sLast = \array_pop($sString);
        $sNextToLast = \array_pop($sString);
        \array_push($sString, $sNextToLast . $sLast);
        return \implode(';', $sString);
    }

    public function comments(Commentable $oCommentable): string
    {
        if (!$this->outputFormat->getRenderComments()) {
            return '';
        }

        $result = '';
        $comments = $oCommentable->getComments();
        $iLastCommentIndex = \count($comments) - 1;

        foreach ($comments as $i => $oComment) {
            $result .= $oComment->render($this->outputFormat);
            $result .= $i === $iLastCommentIndex ? $this->spaceAfterBlocks() : $this->spaceBetweenBlocks();
        }
        return $result;
    }

    private function prepareSpace(string $sSpaceString): string
    {
        return \str_replace("\n", "\n" . $this->indent(), $sSpaceString);
    }

    private function indent(): string
    {
        return \str_repeat($this->outputFormat->sIndentation, $this->outputFormat->getIndentationLevel());
    }
}
