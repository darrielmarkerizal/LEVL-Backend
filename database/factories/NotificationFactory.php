<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Notifications\Models\Notification;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    private static int $sequence = 0;

    public function definition(): array
    {
        self::$sequence++;
        $idx = self::$sequence;

        $types = ['system', 'assignment', 'assessment', 'grading', 'gamification', 'custom'];
        $channels = ['in_app', 'email', 'push'];
        $priorities = ['low', 'normal', 'high'];

        return [
            'type' => $types[$idx % count($types)],
            'title' => 'Notifikasi '.$idx.' — pembaruan status pembelajaran',
            'message' => 'Pesan sistem: ada pembaruan terkait kursus atau tugas Anda. Silakan buka aplikasi untuk detail.',
            'channel' => $channels[$idx % count($channels)],
            'priority' => $priorities[$idx % count($priorities)],
            'is_broadcast' => false,
            'scheduled_at' => null,
            'sent_at' => null,
        ];
    }

    public function broadcast(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_broadcast' => true,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(function (array $attributes) {
            $seed = crc32($attributes['title'] ?? 'n');

            return [
                'scheduled_at' => now()->addDays(($seed % 7) + 1),
            ];
        });
    }
}
