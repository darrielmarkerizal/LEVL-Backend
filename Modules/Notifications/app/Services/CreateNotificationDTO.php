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
        public ?string $readAt = null,
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
            readAt: $data['read_at'] ?? null,
            channel: $data['channel'] ?? 'in_app',
        );
    }

    public function toModelArray(): array
    {
        return [
            'user_id' => $this->userId,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'read_at' => $this->readAt,
            'channel' => $this->channel,
        ];
    }
}
