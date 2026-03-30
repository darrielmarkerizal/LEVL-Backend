<?php

namespace Modules\Auth\Enums;

enum ProfileVisibility: string
{
    case Public = 'public';
    case Private = 'private';
    case FriendsOnly = 'friends_only';

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
            self::Public => 'Public',
            self::Private => 'Private',
            self::FriendsOnly => 'Friends Only',
        };
    }
}
