<?php

namespace App\Support\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;


class ControllerMustNotQueryModelsDirectlyRule implements Rule
{
    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        
        $namespace = $scope->getNamespace();
        if ($namespace === null || ! str_contains($namespace, '\\Controllers\\')) {
            return [];
        }

        
        if (! $node->class instanceof Node\Name) {
            return [];
        }

        $className = $scope->resolveName($node->class);

        
        if (! $this->isModelClass($className)) {
            return [];
        }

        
        if (! $node->name instanceof Node\Identifier) {
            return [];
        }

        $methodName = $node->name->toString();
        $queryMethods = [
            'query', 'where', 'find', 'findOrFail', 'first', 'firstOrFail',
            'get', 'all', 'create', 'update', 'delete', 'destroy',
            'insert', 'insertGetId', 'updateOrCreate', 'firstOrCreate',
            'count', 'sum', 'avg', 'min', 'max', 'exists',
        ];

        if (in_array($methodName, $queryMethods, true)) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Controller should not query models directly. Use repository or service instead. Found: %s::%s()',
                        $this->getShortClassName($className),
                        $methodName
                    )
                )->tip('Move this query to a repository or service method')->build(),
            ];
        }

        return [];
    }

    private function isModelClass(string $className): bool
    {
        
        if (str_contains($className, '\\Models\\')) {
            return true;
        }

        
        $modelBaseClasses = [
            'Illuminate\\Database\\Eloquent\\Model',
            'Illuminate\\Foundation\\Auth\\User',
        ];

        foreach ($modelBaseClasses as $baseClass) {
            if (is_subclass_of($className, $baseClass)) {
                return true;
            }
        }

        return false;
    }

    private function getShortClassName(string $className): string
    {
        $parts = explode('\\', $className);

        return end($parts);
    }
}
