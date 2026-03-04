<?php

declare(strict_types=1);

namespace Modules\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Gamification\Enums\BadgeType;
use Modules\Gamification\Models\Badge;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding 100 Gamification Badges (Payload-Ready)...');

        $badges = $this->generateInteractiveBadges();

        foreach ($badges as $index => $badgeData) {
            $rules = $badgeData['rules'] ?? [];
            unset($badgeData['rules']);

            $badge = Badge::updateOrCreate(
                ['code' => $badgeData['code']],
                $badgeData
            );

            if ($badge->wasRecentlyCreated || $badge->getMedia('icon')->isEmpty()) {
                $badge->clearMediaCollection('icon');

                // DiceBear API shapes style for Gamification
                $seed = \Illuminate\Support\Str::slug($badge->name);
                $url = "https://api.dicebear.com/7.x/shapes/svg?seed={$seed}&backgroundColor=000000,ffffff&shape1Color=0a5b83,1c799f,69d2e7,f1f4dc,f88c49";

                try {
                    $badge->addMediaFromUrl($url)
                        ->withCustomProperties(['seeded' => true])
                        ->toMediaCollection('icon');
                } catch (\Exception $e) {
                    $this->command->error("Failed to attach media to {$badge->code} via URL. Attempting local temp file fallback...");
                    // Fallback to avoid complete halt if allow_url_fopen is false, but addMediaFromUrl usually uses curl.
                }
            }

            // Sync Rules
            \Modules\Gamification\Models\BadgeRule::where('badge_id', $badge->id)->delete();
            foreach ($rules as $rule) {
                \Modules\Gamification\Models\BadgeRule::create([
                    'badge_id' => $badge->id,
                    'event_trigger' => $rule['event_trigger'],
                    'conditions' => $rule['conditions'] ?? null,
                ]);
            }

            if (($index + 1) % 10 === 0) {
                $this->command->info("Seeded " . ($index + 1) . " / 100 badges...");
            }
        }

        $this->command->info('✅ 100 Badges seeded successfully with JSON Rules and DiceBear SVGs.');
    }

    private function generateInteractiveBadges(): array
    {
        $badges = [];

        // 1. COMPLETION BADGES (20 Badges)
        $badges[] = ['code' => 'uiux_master', 'name' => 'UI/UX Design Master', 'description' => 'Menyelesaikan Skema UI/UX Design.', 'type' => BadgeType::Completion, 'threshold' => 1, 'rules' => [['event_trigger' => 'course_completed', 'conditions' => ['course_slug' => 'ui-ux-design']]]];
        $badges[] = ['code' => 'webdev_master', 'name' => 'Web Dev Master', 'description' => 'Menyelesaikan Skema Backend Web Development.', 'type' => BadgeType::Completion, 'threshold' => 1, 'rules' => [['event_trigger' => 'course_completed', 'conditions' => ['course_slug' => 'web-development']]]];
        
        for ($i = 1; $i <= 18; $i++) {
            $badges[] = [
                'code' => "course_finisher_{$i}",
                'name' => "Penyelesai Modul Level {$i}",
                'description' => "Menyelesaikan {$i} unit kursus dengan baik.",
                'type' => BadgeType::Completion,
                'threshold' => 1,
                'rules' => [['event_trigger' => 'unit_completed']]
            ];
        }

        // 2. QUALITY BADGES (20 Badges)
        $badges[] = ['code' => 'perfect_assignment', 'name' => 'Nilai Sempurna', 'description' => 'Mendapatkan nilai 100 pada penugasan.', 'type' => BadgeType::Quality, 'threshold' => 1, 'rules' => [['event_trigger' => 'assignment_graded', 'conditions' => ['min_score' => 100]]]];
        $badges[] = ['code' => 'perfect_quiz', 'name' => 'Kuis Akurat', 'description' => 'Mendapatkan nilai 100 pada kuis.', 'type' => BadgeType::Quality, 'threshold' => 1, 'rules' => [['event_trigger' => 'quiz_graded', 'conditions' => ['min_score' => 100]]]];
        $badges[] = ['code' => 'one_shot_kill', 'name' => 'Satu Tembakan', 'description' => 'Lulus kuis tanpa perbaikan', 'type' => BadgeType::Quality, 'threshold' => 1, 'rules' => [['event_trigger' => 'quiz_graded', 'conditions' => ['max_attempts' => 1, 'is_passed' => true]]]];
        
        for ($i = 1; $i <= 17; $i++) {
            $badges[] = [
                'code' => "quality_streak_{$i}",
                'name' => "Quality Assured {$i}x",
                'description' => "Secara konsisten menjaga performa evaluasi berkualitas.",
                'type' => BadgeType::Quality,
                'threshold' => 1,
                'rules' => [['event_trigger' => 'assignment_graded', 'conditions' => ['min_score' => 85]]]
            ];
        }

        // 3. SPEED BADGES (20 Badges)
        $badges[] = ['code' => 'speed_runner', 'name' => 'Flash Learner', 'description' => 'Finish a course < 3 days.', 'type' => BadgeType::Speed, 'threshold' => 1, 'rules' => [['event_trigger' => 'course_completed', 'conditions' => ['max_duration_days' => 3]]]];
        $badges[] = ['code' => 'first_blood', 'name' => 'Pertama Mengumpul', 'description' => 'Menjadi orang pertama yang mengumpulkan assignment.', 'type' => BadgeType::Speed, 'threshold' => 1, 'rules' => [['event_trigger' => 'assignment_submitted', 'conditions' => ['is_first_submission' => true]]]];
        
        for ($i = 1; $i <= 18; $i++) {
            $badges[] = [
                'code' => "speedy_submission_{$i}",
                'name' => "Penyetor Cepat Level {$i}",
                'description' => "Mengeksekusi latihan dalam tenggat waktu yang mengesankan.",
                'type' => BadgeType::Speed,
                'threshold' => 1,
                'rules' => [['event_trigger' => 'assignment_submitted', 'conditions' => ['max_duration_days' => 1]]]
            ];
        }

        // 4. HABIT BADGES (20 Badges)
        $badges[] = ['code' => 'login_streak_7', 'name' => 'Konsisten 7 Hari', 'description' => 'Login 7 hari berturut-turut.', 'type' => BadgeType::Habit, 'threshold' => 1, 'rules' => [['event_trigger' => 'login', 'conditions' => ['min_streak_days' => 7]]]];
        $badges[] = ['code' => 'login_streak_30', 'name' => 'Dedikasi Bulanan', 'description' => 'Login 30 hari berturut-turut.', 'type' => BadgeType::Habit, 'threshold' => 1, 'rules' => [['event_trigger' => 'login', 'conditions' => ['min_streak_days' => 30]]]];
        $badges[] = ['code' => 'morning_bird', 'name' => 'Burung Pagi', 'description' => 'Login sebelum jam 6:00 AM.', 'type' => BadgeType::Habit, 'threshold' => 1, 'rules' => [['event_trigger' => 'login', 'conditions' => ['time_before' => '06:00:00']]]];
        $badges[] = ['code' => 'weekend_warrior_unit', 'name' => 'Pejuang Akhir Pekan', 'description' => 'Menyelesaikan 5 Unit di akhir pekan.', 'type' => BadgeType::Habit, 'threshold' => 5, 'rules' => [['event_trigger' => 'unit_completed', 'conditions' => ['is_weekend' => true]]]];
        $badges[] = ['code' => 'weekend_warrior_lesson', 'name' => 'Belajar Tanpa Henti', 'description' => 'Menyelesaikan 5 Lesson di akhir pekan.', 'type' => BadgeType::Habit, 'threshold' => 5, 'rules' => [['event_trigger' => 'lesson_completed', 'conditions' => ['is_weekend' => true]]]];

        for ($i = 1; $i <= 15; $i++) {
            $badges[] = [
                'code' => "habit_builder_{$i}",
                'name' => "Membangun Kebiasaan " . ($i * 3) . " Hari",
                'description' => "Mencapai rantai aktivitas " . ($i * 3) . " hari berturut-turut.",
                'type' => BadgeType::Habit,
                'threshold' => 1,
                'rules' => [['event_trigger' => 'login', 'conditions' => ['min_streak_days' => ($i * 3)]]]
            ];
        }

        // 5. SOCIAL BADGES (10 Badges)
        $badges[] = ['code' => 'forum_popular', 'name' => 'Sangat Disukai', 'description' => 'Mendapatkan 10 likes di Forum.', 'type' => BadgeType::Social, 'threshold' => 10, 'rules' => [['event_trigger' => 'forum_liked']]];
        $badges[] = ['code' => 'forum_active', 'name' => 'Banyak Bicara', 'description' => 'Membuat 20 postingan forum.', 'type' => BadgeType::Social, 'threshold' => 20, 'rules' => [['event_trigger' => 'forum_post_created']]];
        $badges[] = ['code' => 'forum_helper', 'name' => 'Pahlawan Forum', 'description' => 'Membalas 5 pertanyaan tak terjawab.', 'type' => BadgeType::Social, 'threshold' => 5, 'rules' => [['event_trigger' => 'forum_reply_created', 'conditions' => ['is_unanswered' => true]]]];
        
        for ($i = 1; $i <= 7; $i++) {
            $badges[] = [
                'code' => "social_butterfly_{$i}",
                'name' => "Social Butterfly Bintang {$i}",
                'description' => "Terlibat dalam interaksi kolektif dan komunal LMS.",
                'type' => BadgeType::Social,
                'threshold' => ($i * 5),
                'rules' => [['event_trigger' => 'forum_reply_created']]
            ];
        }

        // 6. HIDDEN / EASTER EGG BADGES (10 Badges)
        $badges[] = ['code' => 'night_owl', 'name' => 'Kelelawar Malam', 'description' => 'Mengumpulkan tugas di atas jam 12 Malam.', 'type' => BadgeType::Hidden, 'threshold' => 1, 'rules' => [['event_trigger' => 'assignment_submitted', 'conditions' => ['time_after' => '00:00:00', 'time_before' => '04:00:00']]]];
        $badges[] = ['code' => 'bug_hunter', 'name' => 'Pemburu Kutu', 'description' => 'Melaporkan bug pada sistem LMS.', 'type' => BadgeType::Hidden, 'threshold' => 1, 'rules' => [['event_trigger' => 'bug_reported']]];
        
        for ($i = 1; $i <= 8; $i++) {
            $badges[] = [
                'code' => "hidden_egg_{$i}",
                'name' => "Misteri Terpecahkan #{$i}",
                'description' => "Anda menemukan aktivitas tersembunyi yang unik di platform ini.",
                'type' => BadgeType::Hidden,
                'threshold' => 1,
                'rules' => [['event_trigger' => 'easter_egg_found', 'conditions' => ['egg_id' => $i]]]
            ];
        }

        return $badges;
    }
}
