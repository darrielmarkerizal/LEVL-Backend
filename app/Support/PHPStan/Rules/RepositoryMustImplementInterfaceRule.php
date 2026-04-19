<?php

namespace App\Support\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;


class RepositoryMustImplementInterfaceRule implements Rule
{
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $node->getClassReflection();
        $className = $classReflection->getName();

        
        if (! str_contains($className, '\\Repositories\\')) {
            return [];
        }

        
        if (! str_ends_with($className, 'Repository')) {
            return [];
        }

        
        if ($classReflection->isAbstract()) {
            return [];
        }

        
        $interfaces = $classReflection->getInterfaces();

        if (empty($interfaces)) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Repository class %s must implement an interface (e.g., %sInterface)',
                        $classReflection->getDisplayName(),
                        $classReflection->getDisplayName()
                    )
                )->build(),
            ];
        }

        
        $expectedInterfaceName = $className.'Interface';
        $hasCorrespondingInterface = false;

        foreach ($interfaces as $interface) {
            if ($interface->getName() === $expectedInterfaceName) {
                $hasCorrespondingInterface = true;
                break;
            }
        }

        if (! $hasCorrespondingInterface) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Repository class %s should implement %s',
                        $classReflection->getDisplayName(),
                        $expectedInterfaceName
                    )
                )->tip('Create the interface and make the repository implement it')->build(),
            ];
        }

        
        $errors = [];
        foreach ($classReflection->getNativeMethods() as $method) {
            if ($method->isPrivate() || $method->isProtected()) {
                continue;
            }

            $methodName = $method->getName();

            
            if (str_starts_with($methodName, '__')) {
                continue;
            }

            
            $validPrefixes = ['find', 'get', 'create', 'update', 'delete', 'save', 'paginate', 'count', 'exists', 'sum'];
            $hasValidPrefix = false;

            foreach ($validPrefixes as $prefix) {
                if (str_starts_with($methodName, $prefix)) {
                    $hasValidPrefix = true;
                    break;
                }
            }

            if (! $hasValidPrefix) {
                $errors[] = RuleErrorBuilder::message(
                    sprintf(
                        'Repository method %s::%s() should follow naming convention (start with: %s)',
                        $classReflection->getDisplayName(),
                        $methodName,
                        implode(', ', $validPrefixes)
                    )
                )->tip('Repository methods should describe data operations')->build();
            }
        }

        return $errors;
    }
}
