<?php

namespace Modules\Gamification\Enums;

enum PointReason: string
{
    case LessonCompleted = 'lesson_completed';
    case AssignmentSubmitted = 'assignment_submitted';
    case QuizSubmitted = 'quiz_submitted';
    case QuizCompleted = 'quiz_completed';
    case QuizPassed = 'quiz_passed';
    case PerfectScore = 'perfect_score';
    case FirstAttempt = 'first_attempt';
    case FirstSubmission = 'first_submission';
    case UnitCompleted = 'unit_completed';
    case AssignmentCompleted = 'assignment_completed';
    case DailyStreak = 'daily_streak';
    case ForumPost = 'forum_post';
    case ForumReply = 'forum_reply';
    case ReactionReceived = 'reaction_received';
    case Engagement = 'engagement';
    case Bonus = 'bonus';
    case Penalty = 'penalty';

    // Legacy support
    case Completion = 'completion';
    case Score = 'score';

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
            self::LessonCompleted => __('enums.point_reason.lesson_completed'),
            self::AssignmentSubmitted => __('enums.point_reason.assignment_submitted'),
            self::QuizSubmitted => __('enums.point_reason.quiz_submitted'),
            self::QuizCompleted => __('enums.point_reason.quiz_completed'),
            self::QuizPassed => __('enums.point_reason.quiz_passed'),
            self::PerfectScore => __('enums.point_reason.perfect_score'),
            self::FirstAttempt => __('enums.point_reason.first_attempt'),
            self::FirstSubmission => __('enums.point_reason.first_submission'),
            self::UnitCompleted => __('enums.point_reason.unit_completed'),
            self::AssignmentCompleted => __('enums.point_reason.assignment_completed'),
            self::DailyStreak => __('enums.point_reason.daily_streak'),
            self::ForumPost => __('enums.point_reason.forum_post'),
            self::ForumReply => __('enums.point_reason.forum_reply'),
            self::ReactionReceived => __('enums.point_reason.reaction_received'),
            self::Engagement => __('enums.point_reason.engagement'),
            self::Bonus => __('enums.point_reason.bonus'),
            self::Penalty => __('enums.point_reason.penalty'),
            self::Completion => __('enums.point_reason.completion'),
            self::Score => __('enums.point_reason.score'),
        };
    }
}
