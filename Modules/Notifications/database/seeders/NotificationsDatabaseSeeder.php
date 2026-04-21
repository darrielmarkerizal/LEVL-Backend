<?php

declare(strict_types=1);

namespace Modules\Notifications\Database\Seeders;

use App\Support\SeederDate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Content\Enums\Priority;
use Modules\Notifications\Enums\NotificationChannel;
use Modules\Notifications\Enums\NotificationType;

class NotificationsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PostSeeder::class,
        ]);

        $this->command->info('Seeding in-app notifications...');

        $now = SeederDate::randomPastDateTimeBetween(1, 180);
        $channel = NotificationChannel::InApp->value;
        $sentAt = $channel === NotificationChannel::InApp->value ? null : $now;

        $templates = [
            [
                NotificationType::Enrollment,
                'Enrollment activated',
                'Your enrollment is now active. Start exploring lessons in your course.',
                Priority::Normal,
                fn () => $this->recipientsForEnrollment(),
            ],
            [
                NotificationType::Assignment,
                'New assignment available',
                'A new assignment has been published in one of your enrolled courses.',
                Priority::Normal,
                fn () => $this->recipientsForActiveSubmission(),
            ],
            [
                NotificationType::Grading,
                'Grade released',
                'Your submission has been graded. Open it to view feedback and score.',
                Priority::High,
                fn () => $this->recipientsForRecentGrading(),
            ],
            [
                NotificationType::Gamification,
                'Points earned',
                'You earned XP for completing a learning activity.',
                Priority::Low,
                fn () => $this->recipientsForRecentPoints(),
            ],
            [
                NotificationType::System,
                'Platform notice',
                'New features and improvements are now available on LEVL.',
                Priority::Low,
                fn () => $this->recipientsForBroadcast(),
            ],
        ];

        foreach ($templates as $index => [$type, $title, $message, $priority, $recipientsResolver]) {
            $recipients = $recipientsResolver();
            if ($recipients === []) {
                continue;
            }

            $isBroadcast = $type === NotificationType::System;

            $id = DB::table('notifications')->insertGetId([
                'type' => $type->value,
                'title' => $title,
                'message' => $message,
                'data' => json_encode(['seed_index' => $index]),
                'action_url' => null,
                'channel' => $channel,
                'priority' => $priority->value,
                'is_broadcast' => $this->pgsqlBool($isBroadcast),
                'scheduled_at' => null,
                'sent_at' => $sentAt,
                'created_at' => SeederDate::randomPastDateTimeBetween(1, 180),
                'updated_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            ]);

            foreach ($recipients as $offset => $userId) {
                DB::table('user_notifications')->insertOrIgnore([
                    'user_id' => $userId,
                    'notification_id' => $id,
                    'status' => $offset % 3 === 0 ? 'read' : 'unread',
                    'read_at' => $offset % 3 === 0 ? SeederDate::randomPastDateTimeBetween(1, 180) : null,
                    'created_at' => SeederDate::randomPastDateTimeBetween(1, 180),
                    'updated_at' => SeederDate::randomPastDateTimeBetween(1, 180),
                ]);
            }
        }

        $this->command->info('Notifications seeded.');
    }

    private function recipientsForEnrollment(): array
    {
        return DB::table('enrollments')
            ->whereIn('status', ['active', 'pending'])
            ->inRandomOrder()
            ->limit(40)
            ->pluck('user_id')
            ->unique()
            ->values()
            ->all();
    }

    private function recipientsForActiveSubmission(): array
    {
        return DB::table('submissions')
            ->where('status', 'draft')
            ->inRandomOrder()
            ->limit(30)
            ->pluck('user_id')
            ->unique()
            ->values()
            ->all();
    }

    private function recipientsForRecentGrading(): array
    {
        return DB::table('grades')
            ->where('status', 'graded')
            ->whereNotNull('released_at')
            ->inRandomOrder()
            ->limit(30)
            ->pluck('user_id')
            ->unique()
            ->values()
            ->all();
    }

    private function recipientsForRecentPoints(): array
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('points')) {
            return $this->recipientsForBroadcast();
        }

        return DB::table('points')
            ->inRandomOrder()
            ->limit(30)
            ->pluck('user_id')
            ->unique()
            ->values()
            ->all();
    }

    private function recipientsForBroadcast(): array
    {
        return User::query()
            ->whereNull('deleted_at')
            ->inRandomOrder()
            ->limit(80)
            ->pluck('id')
            ->all();
    }

    private function pgsqlBool(bool $value): \Illuminate\Contracts\Database\Query\Expression
    {
        return DB::raw($value ? 'true' : 'false');
    }
}
