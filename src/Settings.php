<?php

declare(strict_types=1);

namespace Sabberworm\CSS;

/**
 * Parser settings class.
 *
 * Configure parser behaviour here.
 */
class Settings
{
    /**
     * Multi-byte string support.
     *
     * If `true` (`mbstring` extension must be enabled), will use (slower) `mb_strlen`, `mb_convert_case`, `mb_substr`
     * and `mb_strpos` functions. Otherwise, the normal (ASCII-Only) functions will be used.
     *
     * @var bool
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $bMultibyteSupport;

    /**
     * The default charset for the CSS if no `@charset` declaration is found. Defaults to utf-8.
     *
     * @var string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $sDefaultCharset = 'utf-8';

    /**
     * Whether the parser silently ignore invalid rules instead of choking on them.
     *
     * @var bool
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $bLenientParsing = true;

    private function __construct()
    {
        $this->bMultibyteSupport = \extension_loaded('mbstring');
    }

    public static function create(): self
    {
        return new Settings();
    }

    /**
     * Enables/disables multi-byte string support.
     *
     * If `true` (`mbstring` extension must be enabled), will use (slower) `mb_strlen`, `mb_convert_case`, `mb_substr`
     * and `mb_strpos` functions. Otherwise, the normal (ASCII-Only) functions will be used.
     *
     * @return $this fluent interface
     */
    public function withMultibyteSupport(bool $bMultibyteSupport = true): self
    {
        $this->bMultibyteSupport = $bMultibyteSupport;
        return $this;
    }

    /**
     * Sets the charset to be used if the CSS does not contain an `@charset` declaration.
     *
     * @return $this fluent interface
     */
    public function withDefaultCharset(string $sDefaultCharset): self
    {
        $this->sDefaultCharset = $sDefaultCharset;
        return $this;
    }

    /**
     * Configures whether the parser should silently ignore invalid rules.
     *
     * @return $this fluent interface
     */
    public function withLenientParsing(bool $usesLenientParsing = true): self
    {
        $this->bLenientParsing = $usesLenientParsing;
        return $this;
    }

    /**
     * Configures the parser to choke on invalid rules.
     *
     * @return $this fluent interface
     */
    public function beStrict(): self
    {
        return $this->withLenientParsing(false);
    }
}
