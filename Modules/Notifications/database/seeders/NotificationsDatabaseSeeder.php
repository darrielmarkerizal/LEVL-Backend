<?php

declare(strict_types=1);

namespace Modules\Notifications\Database\Seeders;

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
        $this->command->info('Seeding in-app notifications...');

        $studentIds = User::query()->role('Student')->orderBy('id')->limit(80)->pluck('id')->all();
        if ($studentIds === []) {
            $this->command->warn('No students found for notification seeding.');

            return;
        }

        $now = now()->toDateTimeString();
        $templates = [
            [NotificationType::Enrollment, 'Enrollment updated', 'Your enrollment status was refreshed.', Priority::Normal],
            [NotificationType::Assignment, 'New assignment', 'A new assignment is available in your course.', Priority::Normal],
            [NotificationType::Grading, 'Grade released', 'Your submission has been graded.', Priority::High],
            [NotificationType::Gamification, 'Points earned', 'You earned XP for completing learning activity.', Priority::Low],
            [NotificationType::System, 'Platform notice', 'Welcome to LEVL UAT dataset.', Priority::Low],
        ];

        foreach ($templates as $index => [$type, $title, $message, $priority]) {
            $id = DB::table('notifications')->insertGetId([
                'type' => $type->value,
                'title' => $title,
                'message' => $message,
                'data' => json_encode(['seed_index' => $index]),
                'action_url' => null,
                'channel' => NotificationChannel::InApp->value,
                'priority' => $priority->value,
                'is_broadcast' => false,
                'scheduled_at' => null,
                'sent_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $targets = array_slice($studentIds, 0, min(12, count($studentIds)));
            foreach ($targets as $offset => $userId) {
                DB::table('user_notifications')->insertOrIgnore([
                    'user_id' => $userId,
                    'notification_id' => $id,
                    'status' => $offset % 3 === 0 ? 'read' : 'unread',
                    'read_at' => $offset % 3 === 0 ? $now : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $this->command->info('Notifications seeded.');
    }
}
