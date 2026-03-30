<?php

namespace Modules\Notifications\Enums;

enum ReadStatus: string
{
    case Unread = 'unread';
    case Read = 'read';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function rule(): string
    {
        return 'in:' . implode(',', self::values());
    }

    public function label(): string
    {
        return match ($this) {
            self::Unread => 'Unread',
            self::Read => 'Read',
        };
    }
}
