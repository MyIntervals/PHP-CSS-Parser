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
    public $multibyteSupport;

    /**
     * The default charset for the CSS if no `@charset` declaration is found. Defaults to utf-8.
     *
     * @var non-empty-string
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $defaultCharset = 'utf-8';

    /**
     * Whether the parser silently ignore invalid rules instead of choking on them.
     *
     * @var bool
     *
     * @internal since 8.8.0, will be made private in 9.0.0
     */
    public $lenientParsing = true;

    private function __construct()
    {
        $this->multibyteSupport = \extension_loaded('mbstring');
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
    public function withMultibyteSupport(bool $multibyteSupport = true): self
    {
        $this->multibyteSupport = $multibyteSupport;

        return $this;
    }

    /**
     * Sets the charset to be used if the CSS does not contain an `@charset` declaration.
     *
     * @param non-empty-string $defaultCharset
     *
     * @return $this fluent interface
     */
    public function withDefaultCharset(string $defaultCharset): self
    {
        $this->defaultCharset = $defaultCharset;

        return $this;
    }

    /**
     * Configures whether the parser should silently ignore invalid rules.
     *
     * @return $this fluent interface
     */
    public function withLenientParsing(bool $usesLenientParsing = true): self
    {
        $this->lenientParsing = $usesLenientParsing;

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

    /**
     * @internal
     */
    public function hasMultibyteSupport(): bool
    {
        return $this->multibyteSupport;
    }

    /**
     * @return non-empty-string
     *
     * @internal
     */
    public function getDefaultCharset(): string
    {
        return $this->defaultCharset;
    }

    /**
     * @internal
     */
    public function usesLenientParsing(): bool
    {
        return $this->lenientParsing;
    }
}
