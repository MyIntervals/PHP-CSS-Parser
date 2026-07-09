<?php

declare(strict_types=1);

namespace Sabberworm\CSS\RuleSet;

use Sabberworm\CSS\Property\Declaration;

/**
 * Provides a mapping of the deprecated methods in a `DeclarationList` to their renamed replacements.
 */
trait LegacyDeclarationListMethods
{
    /**
     * @deprecated in v9.2, will be removed in v10.0; use `addDeclaration()` instead.
     */
    public function addRule(Declaration $declarationToAdd, ?Declaration $sibling = null): void
    {
        $this->addDeclaration($declarationToAdd, $sibling);
    }

    /**
     * @deprecated in v9.2, will be removed in v10.0; use `removeDeclaration()` instead.
     */
    public function removeRule(Declaration $declarationToRemove): void
    {
        $this->removeDeclaration($declarationToRemove);
    }

    /**
     * @deprecated in v9.2, will be removed in v10.0; use `removeMatchingDeclarations()` instead.
     */
    public function removeMatchingRules(string $searchPattern): void
    {
        $this->removeMatchingDeclarations($searchPattern);
    }

    /**
     * @deprecated in v9.2, will be removed in v10.0; use `removeAllDeclarations()` instead.
     */
    public function removeAllRules(): void
    {
        $this->removeAllDeclarations();
    }

    /**
     * @param array<Declaration> $declarations
     *
     * @deprecated in v9.2, will be removed in v10.0; use `setDeclarations()` instead.
     */
    public function setRules(array $declarations): void
    {
        $this->setDeclarations($declarations);
    }

    /**
     * @return array<int<0, max>, Declaration>
     *
     * @deprecated in v9.2, will be removed in v10.0; use `getDeclarations()` instead.
     */
    public function getRules(?string $searchPattern = null): array
    {
        return $this->getDeclarations($searchPattern);
    }

    /**
     * @return array<string, Declaration>
     *
     * @deprecated in v9.2, will be removed in v10.0; use `getDeclarationsAssociative()` instead.
     */
    public function getRulesAssoc(?string $searchPattern = null): array
    {
        return $this->getDeclarationsAssociative($searchPattern);
    }
}
