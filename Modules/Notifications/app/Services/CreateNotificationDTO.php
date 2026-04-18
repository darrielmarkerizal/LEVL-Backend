<?php

namespace Modules\Notifications\Services;

class CreateNotificationDTO
{
    public function __construct(
        public int $userId,
        public string $type,
        public string $title,
        public string $message,
        public ?array $data = null,
        public ?string $actionUrl = null,
        public ?string $channel = 'in_app',
    ) {}

    public static function from(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            type: $data['type'],
            title: $data['title'],
            message: $data['message'],
            data: $data['data'] ?? null,
            actionUrl: $data['action_url'] ?? null,
            channel: $data['channel'] ?? 'in_app',
        );
    }

    public function toModelArray(): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'action_url' => $this->actionUrl,
            'channel' => $this->channel,
        ];
    }
}
