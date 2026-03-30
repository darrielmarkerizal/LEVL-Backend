<?php

namespace Modules\Forums\Enums;

enum ReactionType: string
{
    case Like = 'like';
    case Helpful = 'helpful';
    case Solved = 'solved';

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
            self::Like => 'Like',
            self::Helpful => 'Helpful',
            self::Solved => 'Solved',
        };
    }
}
