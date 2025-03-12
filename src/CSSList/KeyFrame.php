<?php

declare(strict_types=1);

namespace Sabberworm\CSS\CSSList;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Property\AtRule;

class KeyFrame extends CSSList implements AtRule
{
    /**
     * @var non-empty-string
     */
    private $vendorKeyFrame = 'keyframes';

    /**
     * @var non-empty-string
     */
    private $animationName = 'none';

    /**
     * @param non-empty-string $vendorKeyFrame
     */
    public function setVendorKeyFrame(string $vendorKeyFrame): void
    {
        $this->vendorKeyFrame = $vendorKeyFrame;
    }

    /**
     * @return non-empty-string
     */
    public function getVendorKeyFrame(): string
    {
        return $this->vendorKeyFrame;
    }

    /**
     * @param non-empty-string $animationName
     */
    public function setAnimationName(string $animationName): void
    {
        $this->animationName = $animationName;
    }

    /**
     * @return non-empty-string
     */
    public function getAnimationName(): string
    {
        return $this->animationName;
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
        $result = $outputFormat->comments($this);
        $result .= "@{$this->vendorKeyFrame} {$this->animationName}{$outputFormat->spaceBeforeOpeningBrace()}{";
        $result .= $this->renderListContents($outputFormat);
        $result .= '}';
        return $result;
    }

    public function isRootList(): bool
    {
        return false;
    }

    /**
     * @return non-empty-string
     */
    public function atRuleName(): string
    {
        return $this->vendorKeyFrame;
    }

    /**
     * @return non-empty-string
     */
    public function atRuleArgs(): string
    {
        return $this->animationName;
    }
}
