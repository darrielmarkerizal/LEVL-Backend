<?php

namespace Modules\Notifications\Services;

class SendNotificationDTO
{
    public function __construct(
        public int $userId,
        public string $type,
        public string $title,
        public string $message,
        public ?array $data = null,
    ) {}
}
