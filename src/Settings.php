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
     */
    public $bMultibyteSupport;

    /**
     * The default charset for the CSS if no `@charset` declaration is found. Defaults to utf-8.
     *
     * @var string
     */
    public $sDefaultCharset = 'utf-8';

    /**
     * Whether the parser silently ignore invalid rules instead of choking on them.
     *
     * @var bool
     */
    public $bLenientParsing = true;

    private function __construct()
    {
        $this->bMultibyteSupport = \extension_loaded('mbstring');
    }

    /**
     * @return self new instance
     */
    public static function create(): Settings
    {
        return new Settings();
    }

    /**
     * Enables/disables multi-byte string support.
     *
     * If `true` (`mbstring` extension must be enabled), will use (slower) `mb_strlen`, `mb_convert_case`, `mb_substr`
     * and `mb_strpos` functions. Otherwise, the normal (ASCII-Only) functions will be used.
     *
     * @param bool $bMultibyteSupport
     *
     * @return $this fluent interface
     */
    public function withMultibyteSupport($bMultibyteSupport = true): self
    {
        $this->bMultibyteSupport = $bMultibyteSupport;
        return $this;
    }

    /**
     * Sets the charset to be used if the CSS does not contain an `@charset` declaration.
     *
     * @param string $sDefaultCharset
     *
     * @return $this fluent interface
     */
    public function withDefaultCharset($sDefaultCharset): self
    {
        $this->sDefaultCharset = $sDefaultCharset;
        return $this;
    }

    /**
     * Configures whether the parser should silently ignore invalid rules.
     *
     * @param bool $bLenientParsing
     *
     * @return $this fluent interface
     */
    public function withLenientParsing($bLenientParsing = true): self
    {
        $this->bLenientParsing = $bLenientParsing;
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
