<?php

declare(strict_types=1);

namespace Sabberworm\CSS\RuleSet;

use Sabberworm\CSS\Property\Declaration;

/**
 * @internal
 */
trait LegacyRuleContainerMethods
{
    public function addRule(Declaration $declarationToAdd, ?Declaration $sibling = null): void
    {
        $this->addDeclaration($declarationToAdd, $sibling);
    }

    public function removeRule(Declaration $declarationToRemove): void
    {
        $this->removeDeclaration($declarationToRemove);
    }

    public function removeMatchingRules(string $searchPattern): void
    {
        $this->removeMatchingDeclarations($searchPattern);
    }

    public function removeAllRules(): void
    {
        $this->removeAllDeclarations();
    }

    /**
     * @param array<Declaration> $declarations
     */
    public function setRules(array $declarations): void
    {
        $this->setDeclarations($declarations);
    }

    /**
     * @return array<int<0, max>, Declaration>
     */
    public function getRules(?string $searchPattern = null): array
    {
        return $this->getDeclarations($searchPattern);
    }

    /**
     * @return array<string, Declaration>
     */
    public function getRulesAssoc(?string $searchPattern = null): array
    {
        return $this->getDeclarationsAssociative($searchPattern);
    }
}
