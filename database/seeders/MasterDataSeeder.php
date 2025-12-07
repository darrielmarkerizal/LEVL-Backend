<?php

namespace Database\Seeders;

use App\Models\MasterDataItem;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * All enum classes to seed from.
     */
    private array $enumClasses = [
        // Auth Module
        'user-status' => \Modules\Auth\Enums\UserStatus::class,

        // Common Module
        'category-status' => \Modules\Common\Enums\CategoryStatus::class,
        'setting-types' => \Modules\Common\Enums\SettingType::class,

        // Content Module
        'content-status' => \Modules\Content\Enums\ContentStatus::class,
        'priorities' => \Modules\Content\Enums\Priority::class,
        'target-types' => \Modules\Content\Enums\TargetType::class,

        // Enrollments Module
        'enrollment-status' => \Modules\Enrollments\Enums\EnrollmentStatus::class,
        'progress-status' => \Modules\Enrollments\Enums\ProgressStatus::class,

        // Gamification Module
        'badge-types' => \Modules\Gamification\Enums\BadgeType::class,
        'challenge-assignment-status' => \Modules\Gamification\Enums\ChallengeAssignmentStatus::class,
        'challenge-criteria-types' => \Modules\Gamification\Enums\ChallengeCriteriaType::class,
        'challenge-types' => \Modules\Gamification\Enums\ChallengeType::class,
        'point-reasons' => \Modules\Gamification\Enums\PointReason::class,
        'point-source-types' => \Modules\Gamification\Enums\PointSourceType::class,

        // Grading Module
        'grade-status' => \Modules\Grading\Enums\GradeStatus::class,
        'grade-source-types' => \Modules\Grading\Enums\SourceType::class,

        // Learning Module
        'assignment-status' => \Modules\Learning\Enums\AssignmentStatus::class,
        'submission-status' => \Modules\Learning\Enums\SubmissionStatus::class,
        'submission-types' => \Modules\Learning\Enums\SubmissionType::class,

        // Notifications Module
        'notification-channels' => \Modules\Notifications\Enums\NotificationChannel::class,
        'notification-frequencies' => \Modules\Notifications\Enums\NotificationFrequency::class,
        'notification-types' => \Modules\Notifications\Enums\NotificationType::class,

        // Schemes Module
        'content-types' => \Modules\Schemes\Enums\ContentType::class,
        'course-status' => \Modules\Schemes\Enums\CourseStatus::class,
        'course-types' => \Modules\Schemes\Enums\CourseType::class,
        'enrollment-types' => \Modules\Schemes\Enums\EnrollmentType::class,
        'level-tags' => \Modules\Schemes\Enums\LevelTag::class,
        'progression-modes' => \Modules\Schemes\Enums\ProgressionMode::class,
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable Scout syncing during seeding to avoid Meilisearch connection issues
        MasterDataItem::withoutSyncingToSearch(function () {
            foreach ($this->enumClasses as $type => $enumClass) {
                $this->seedFromEnum($type, $enumClass);
            }
        });

        $this->command->info('Master data seeded successfully from '.count($this->enumClasses).' enums.');
    }

    /**
     * Seed master data from an enum class.
     */
    private function seedFromEnum(string $type, string $enumClass): void
    {
        if (! enum_exists($enumClass)) {
            $this->command->warn("Enum class not found: {$enumClass}");

            return;
        }

        $cases = $enumClass::cases();
        $sortOrder = 0;

        foreach ($cases as $case) {
            $value = $case->value ?? $case->name;
            $label = $this->getLabel($case);

            MasterDataItem::updateOrCreate(
                ['type' => $type, 'value' => $value],
                [
                    'label' => $label,
                    'is_system' => true,
                    'is_active' => true,
                    'sort_order' => $sortOrder++,
                ]
            );
        }

        $this->command->info("Seeded {$type}: ".count($cases).' items');
    }

    /**
     * Get label from enum case.
     */
    private function getLabel($case): string
    {
        // Try to get label from enum method if exists
        if (method_exists($case, 'label')) {
            return $case->label();
        }

        if (method_exists($case, 'getLabel')) {
            return $case->getLabel();
        }

        // Fallback: convert case name to title case
        return str_replace('_', ' ', ucwords(strtolower($case->name), '_'));
    }
}
