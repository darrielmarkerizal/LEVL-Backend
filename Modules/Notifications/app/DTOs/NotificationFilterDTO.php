<?php

namespace Modules\Notifications\DTOs;

use App\Support\BaseDTO;

final class NotificationFilterDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $type = null,
        public readonly ?bool $read = null,
        public readonly ?string $sort = null,
        public readonly int $perPage = 15,
        public readonly int $page = 1,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            type: $data['type'] ?? $data['filter']['type'] ?? null,
            read: isset($data['read']) ? (bool) $data['read'] : null,
            sort: $data['sort'] ?? null,
            perPage: max(1, min(100, (int) ($data['per_page'] ?? 15))),
            page: max(1, (int) ($data['page'] ?? 1)),
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'read' => $this->read,
            'sort' => $this->sort,
            'per_page' => $this->perPage,
            'page' => $this->page,
        ];
    }
}
