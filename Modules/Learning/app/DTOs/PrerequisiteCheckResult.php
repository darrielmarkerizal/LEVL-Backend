<?php

declare(strict_types=1);

namespace Modules\Learning\DTOs;

use Illuminate\Support\Collection;

class PrerequisiteCheckResult
{
    public function __construct(
        public readonly bool $passed,
        public readonly Collection $incompletePrerequisites,
        public readonly ?string $message = null
    ) {}

    public static function pass(): self
    {
        return new self(
            passed: true,
            incompletePrerequisites: collect(),
            message: null
        );
    }

    public static function fail(Collection $incompletePrerequisites): self
    {
        $count = $incompletePrerequisites->count();
        $message = __('messages.prerequisites.incomplete_count', ['count' => $count]);

        return new self(
            passed: false,
            incompletePrerequisites: $incompletePrerequisites,
            message: $message
        );
    }

    public function toArray(): array
    {
        return [
            'passed' => $this->passed,
            'incomplete_prerequisites' => $this->incompletePrerequisites->map(fn ($item) => [
                'type' => $item['type'] ?? 'assignment',
                'id' => $item['id'] ?? $item->id,
                'title' => $item['title'] ?? $item->title,
                'slug' => $item['slug'] ?? $item->slug ?? null,
                'unit_title' => $item['unit_title'] ?? null,
            ])->toArray(),
        ];
    }
}
