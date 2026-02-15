<?php

declare(strict_types=1);

namespace Sabberworm\CSS\RuleSet;

use Sabberworm\CSS\Property\Declaration;

/**
 * Represents a CSS item that contains `Declaration`s, defining the methods to manipulate them.
 */
interface RuleContainer
{
    public function addRule(Declaration $declarationToAdd, ?Declaration $sibling = null): void;

    public function removeRule(Declaration $declarationToRemove): void;

    public function removeMatchingRules(string $searchPattern): void;

    public function removeAllRules(): void;

    /**
     * @param array<Declaration> $declarations
     */
    public function setRules(array $declarations): void;

    /**
     * @return array<int<0, max>, Declaration>
     */
    public function getRules(?string $searchPattern = null): array;

    /**
     * @return array<string, Declaration>
     */
    public function getRulesAssoc(?string $searchPattern = null): array;
}
