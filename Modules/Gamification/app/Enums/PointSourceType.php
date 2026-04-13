<?php

namespace Modules\Gamification\Enums;

enum PointSourceType: string
{
    case Lesson = 'lesson';
    case Unit = 'unit';
    case Course = 'course';
    case Assignment = 'assignment';
    case Attempt = 'attempt';
    case Quiz = 'quiz';
    case Thread = 'thread';
    case Reply = 'reply';
    case Reaction = 'reaction';
    case Challenge = 'challenge';
    case System = 'system';
    case Grade = 'grade';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function rule(): string
    {
        return 'in:'.implode(',', self::values());
    }

    public function label(): string
    {
        return match ($this) {
            self::Lesson => __('enums.point_source_type.lesson'),
            self::Unit => __('enums.point_source_type.unit'),
            self::Course => __('enums.point_source_type.course'),
            self::Assignment => __('enums.point_source_type.assignment'),
            self::Attempt => __('enums.point_source_type.attempt'),
            self::Quiz => __('enums.point_source_type.quiz'),
            self::Thread => __('enums.point_source_type.thread'),
            self::Reply => __('enums.point_source_type.reply'),
            self::Reaction => __('enums.point_source_type.reaction'),
            self::Challenge => __('enums.point_source_type.challenge'),
            self::System => __('enums.point_source_type.system'),
            self::Grade => __('enums.point_source_type.grade'),
        };
    }
}
