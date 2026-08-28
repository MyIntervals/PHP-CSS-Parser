<?php

declare(strict_types=1);

namespace Sabberworm\CSS\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\IgnoreErrorExtension;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FunctionCallExpressionNode;

/**
 * Ignore PHPStan warnings where the DocBlocks indicate that a conditional expression would always be true (or false),
 * but a programming mistake elsewhere could lead to that not being the case, for the following:
 * - `assert($object instanceof Class);`.
 *
 * @internal
 */
final class IgnoreBooleanAlways implements IgnoreErrorExtension
{
    public function shouldIgnore(Error $error, Node $node, Scope $scope): bool
    {
        switch ($error->getIdentifier()) {
            case 'function.alreadyNarrowedType':
                return self::shouldIgnoreFunctionAlreadyNarrowedType($node);
            case 'instanceof.alwaysTrue':
                return self::shouldIgnoreInstanceofAlwaysTrue($scope);
            default:
                return false;
        }
    }

    /**
     * For an `assert()` that the DocBlocks say cannot fail.
     */
    private static function shouldIgnoreFunctionAlreadyNarrowedType(Node $node): bool
    {
        $shouldIgnore = false;

        // This is an unstable API that does not adhere to semver.
        // @phpstan-ignore phpstanApi.classConstant, phpstanApi.class
        if (\class_exists(FunctionCallExpressionNode::class) && $node instanceof FunctionCallExpressionNode) {
            // Unwrap for PHPStan >= 2.2.8
            // @phpstan-ignore phpstanApi.method
            $node = $node->getOriginalNode();
        }
        if ($node instanceof FuncCall) {
            $nameNode = $node->name;
            if ($nameNode instanceof Name && $nameNode->name === 'assert') {
                $shouldIgnore = true;
            }
        }

        return $shouldIgnore;
    }

    /**
     * For `instanceof` within an `assert()` that the DocBlocks say cannot fail.
     */
    private static function shouldIgnoreInstanceofAlwaysTrue(Scope $scope): bool
    {
        $shouldIgnore = false;

        $functionCallStack = $scope->getFunctionCallStack();
        if (isset($functionCallStack[0]) && $functionCallStack[0]->getName() === 'assert') {
            $shouldIgnore = true;
        }

        return $shouldIgnore;
    }
}
