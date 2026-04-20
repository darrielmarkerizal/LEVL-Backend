<?php

declare(strict_types=1);

namespace App\Support\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Catch_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class QueryCatchWithDbCallRule implements Rule
{
    private const CAUGHT_TYPES = [
        'Illuminate\\Database\\QueryException',
        'Illuminate\\Database\\Exception',
        'Exception',
        'Throwable',
    ];

    private const DB_FACADE_METHODS = [
        'select', 'insert', 'update', 'delete', 'statement', 'affectingStatement',
        'table', 'raw', 'transaction', 'beginTransaction', 'commit', 'rollBack',
        'unprepared', 'cursor', 'selectOne', 'selectFromWriteConnection',
    ];

    private const QUERY_METHODS = [
        'query', 'where', 'whereRaw', 'find', 'findOrFail', 'first', 'firstOrFail',
        'get', 'all', 'create', 'delete', 'destroy', 'insert',
        'insertGetId', 'updateOrCreate', 'firstOrCreate', 'upsert', 'insertOrIgnore',
        'count', 'sum', 'avg', 'min', 'max', 'exists', 'value', 'pluck',
        'save', 'fresh', 'refresh', 'push',
        'sync', 'attach', 'detach',
    ];

    public function getNodeType(): string
    {
        return Catch_::class;
    }

    /** @return list<\PHPStan\Rules\IdentifierRuleError> */
    
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof Catch_) {
            return [];
        }

        if (! $this->catchesQueryException($node)) {
            return [];
        }

        if (! $this->hasDbCallsInBlock($node->stmts)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Catch block for QueryException/Exception performs additional database queries. '
                .'In PostgreSQL, after a query fails inside a transaction the connection enters '
                .'aborted state (25P02) and all subsequent queries will fail. '
                .'Use INSERT ... ON CONFLICT (upsert/insertOrIgnore) or move the logic outside the transaction instead.'
            )
                ->identifier('query.catch.dbcall')
                ->build(),
        ];
    }

    private function catchesQueryException(Catch_ $node): bool
    {
        foreach ($node->types as $type) {
            if (! $type instanceof Node\Name) {
                continue;
            }

            $typeName = ltrim($type->toString(), '\\');

            foreach (self::CAUGHT_TYPES as $target) {
                if (strcasecmp($typeName, ltrim($target, '\\')) === 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /** @param array<Node\Stmt> $stmts */
    private function hasDbCallsInBlock(array $stmts): bool
    {
        foreach ($stmts as $stmt) {
            if ($this->hasDbCallsInNode($stmt)) {
                return true;
            }
        }

        return false;
    }

    private function hasDbCallsInNode(Node $node): bool
    {
        if ($node instanceof Node\Expr\StaticCall) {
            if ($this->isDbFacadeCall($node) || $this->isModelStaticQuery($node)) {
                return true;
            }
        }

        if ($node instanceof Node\Expr\MethodCall) {
            if ($this->isEloquentBuilderCall($node)) {
                return true;
            }
        }

        foreach ($node->getSubNodeNames() as $subName) {
            $sub = $node->$subName;
            if ($sub instanceof Node && $this->hasDbCallsInNode($sub)) {
                return true;
            }
            if (is_array($sub)) {
                foreach ($sub as $item) {
                    if ($item instanceof Node && $this->hasDbCallsInNode($item)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function isDbFacadeCall(Node\Expr\StaticCall $node): bool
    {
        if (! $node->class instanceof Node\Name) {
            return false;
        }

        if (! $node->name instanceof Node\Identifier) {
            return false;
        }

        $class = strtolower($node->class->toString());
        if (! in_array($class, ['db', 'illuminate\\support\\facades\\db'], true)) {
            return false;
        }

        return in_array($node->name->toString(), self::DB_FACADE_METHODS, true);
    }

    private function isModelStaticQuery(Node\Expr\StaticCall $node): bool
    {
        if (! $node->class instanceof Node\Name) {
            return false;
        }

        if (! $node->name instanceof Node\Identifier) {
            return false;
        }

        return in_array($node->name->toString(), self::QUERY_METHODS, true);
    }

    private function isEloquentBuilderCall(Node\Expr\MethodCall $node): bool
    {
        if (! $node->name instanceof Node\Identifier) {
            return false;
        }

        return in_array($node->name->toString(), self::QUERY_METHODS, true);
    }
}
