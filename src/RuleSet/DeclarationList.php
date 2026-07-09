<?php

declare(strict_types=1);

namespace Sabberworm\CSS\RuleSet;

use Sabberworm\CSS\Property\Declaration;

/**
 * Represents a CSS item that contains `Declaration`s, defining the methods to manipulate them.
 */
interface DeclarationList
{
    public function addDeclaration(Declaration $declarationToAdd, ?Declaration $sibling = null): void;

    public function removeDeclaration(Declaration $declarationToRemove): void;

    public function removeMatchingDeclarations(string $searchPattern): void;

    public function removeAllDeclarations(): void;

    /**
     * @param array<Declaration> $declarations
     */
    public function setDeclarations(array $declarations): void;

    /**
     * @return array<int<0, max>, Declaration>
     */
    public function getDeclarations(?string $searchPattern = null): array;

    /**
     * @return array<string, Declaration>
     */
    public function getDeclarationsAssociative(?string $searchPattern = null): array;

    /**
     * @deprecated in v9.2, will be removed in v10.0; use `addDeclaration()` instead.
     */
    public function addRule(Declaration $declarationToAdd, ?Declaration $sibling = null): void;

    /**
     * @deprecated in v9.2, will be removed in v10.0; use `removeDeclaration()` instead.
     */
    public function removeRule(Declaration $declarationToRemove): void;

    /**
     * @deprecated in v9.2, will be removed in v10.0; use `removeMatchingDeclarations()` instead.
     */
    public function removeMatchingRules(string $searchPattern): void;

    /**
     * @deprecated in v9.2, will be removed in v10.0; use `removeAllDeclarations()` instead.
     */
    public function removeAllRules(): void;

    /**
     * @param array<Declaration> $declarations
     *
     * @deprecated in v9.2, will be removed in v10.0; use `setDeclarations()` instead.
     */
    public function setRules(array $declarations): void;

    /**
     * @return array<int<0, max>, Declaration>
     *
     * @deprecated in v9.2, will be removed in v10.0; use `getDeclarations()` instead.
     */
    public function getRules(?string $searchPattern = null): array;

    /**
     * @return array<string, Declaration>
     *
     * @deprecated in v9.2, will be removed in v10.0; use `getDeclarationsAssociative()` instead.
     */
    public function getRulesAssoc(?string $searchPattern = null): array;
}
