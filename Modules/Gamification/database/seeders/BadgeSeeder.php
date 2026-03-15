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
        $this->command->info('Seeding Gamification Badges...');

        $badges = $this->generateInteractiveBadges();
        $count = 0;

        foreach ($badges as $badgeData) {
            $rules = $badgeData['rules'] ?? [];
            unset($badgeData['rules']);

            try {
                $badge = Badge::updateOrCreate(
                    ['code' => $badgeData['code']],
                    $badgeData
                );

                // Only add media if badge was just created and has no media
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
                        $this->command->warn("Failed to attach media to {$badge->code}: {$e->getMessage()}");
                    }
                }

                // Sync Rules - delete old rules and create new ones
                \Modules\Gamification\Models\BadgeRule::where('badge_id', $badge->id)->delete();
                foreach ($rules as $rule) {
                    \Modules\Gamification\Models\BadgeRule::create([
                        'badge_id' => $badge->id,
                        'event_trigger' => $rule['event_trigger'],
                        'conditions' => $rule['conditions'] ?? null,
                    ]);
                }

                $count++;
                if ($count % 10 === 0) {
                    $this->command->info("Seeded {$count} badges...");
                }
            } catch (\Exception $e) {
                $this->command->error("Failed to seed badge {$badgeData['code']}: {$e->getMessage()}");
            }
        }

        $this->command->info("✅ Successfully seeded {$count} badges.");
    }

    private function generateInteractiveBadges(): array
    {
        $badges = [];

        // 0. MILESTONE BADGES (Level Achievement Badges)
        $badges[] = ['code' => 'level_10_milestone', 'name' => 'Novice Achiever', 'description' => 'Mencapai level 10 - Menyelesaikan tier Beginner', 'type' => BadgeType::Milestone, 'category' => 'milestone', 'rarity' => 'common', 'xp_reward' => 50, 'threshold' => 10, 'rules' => [['event_trigger' => 'level_reached', 'conditions' => ['level' => 10]]]];
        $badges[] = ['code' => 'level_20_milestone', 'name' => 'Competent Learner', 'description' => 'Mencapai level 20 - Menyelesaikan tier Novice', 'type' => BadgeType::Milestone, 'category' => 'milestone', 'rarity' => 'common', 'xp_reward' => 100, 'threshold' => 20, 'rules' => [['event_trigger' => 'level_reached', 'conditions' => ['level' => 20]]]];
        $badges[] = ['code' => 'level_30_milestone', 'name' => 'Intermediate Master', 'description' => 'Mencapai level 30 - Menyelesaikan tier Competent', 'type' => BadgeType::Milestone, 'category' => 'milestone', 'rarity' => 'uncommon', 'xp_reward' => 150, 'threshold' => 30, 'rules' => [['event_trigger' => 'level_reached', 'conditions' => ['level' => 30]]]];
        $badges[] = ['code' => 'level_40_milestone', 'name' => 'Proficient Expert', 'description' => 'Mencapai level 40 - Menyelesaikan tier Intermediate', 'type' => BadgeType::Milestone, 'category' => 'milestone', 'rarity' => 'uncommon', 'xp_reward' => 200, 'threshold' => 40, 'rules' => [['event_trigger' => 'level_reached', 'conditions' => ['level' => 40]]]];
        $badges[] = ['code' => 'level_50_milestone', 'name' => 'Advanced Specialist', 'description' => 'Mencapai level 50 - Menyelesaikan tier Proficient', 'type' => BadgeType::Milestone, 'category' => 'milestone', 'rarity' => 'rare', 'xp_reward' => 300, 'threshold' => 50, 'rules' => [['event_trigger' => 'level_reached', 'conditions' => ['level' => 50]]]];
        $badges[] = ['code' => 'level_60_milestone', 'name' => 'Expert Champion', 'description' => 'Mencapai level 60 - Menyelesaikan tier Advanced', 'type' => BadgeType::Milestone, 'category' => 'milestone', 'rarity' => 'rare', 'xp_reward' => 400, 'threshold' => 60, 'rules' => [['event_trigger' => 'level_reached', 'conditions' => ['level' => 60]]]];
        $badges[] = ['code' => 'level_70_milestone', 'name' => 'Master Virtuoso', 'description' => 'Mencapai level 70 - Menyelesaikan tier Expert', 'type' => BadgeType::Milestone, 'category' => 'milestone', 'rarity' => 'epic', 'xp_reward' => 500, 'threshold' => 70, 'rules' => [['event_trigger' => 'level_reached', 'conditions' => ['level' => 70]]]];
        $badges[] = ['code' => 'level_80_milestone', 'name' => 'Grand Master', 'description' => 'Mencapai level 80 - Menyelesaikan tier Master', 'type' => BadgeType::Milestone, 'category' => 'milestone', 'rarity' => 'epic', 'xp_reward' => 700, 'threshold' => 80, 'rules' => [['event_trigger' => 'level_reached', 'conditions' => ['level' => 80]]]];
        $badges[] = ['code' => 'level_90_milestone', 'name' => 'Legendary Scholar', 'description' => 'Mencapai level 90 - Menyelesaikan tier Grand Master', 'type' => BadgeType::Milestone, 'category' => 'milestone', 'rarity' => 'legendary', 'xp_reward' => 1000, 'threshold' => 90, 'rules' => [['event_trigger' => 'level_reached', 'conditions' => ['level' => 90]]]];
        $badges[] = ['code' => 'level_100_milestone', 'name' => 'Ultimate Legend', 'description' => 'Mencapai level 100 - Menguasai semua tier!', 'type' => BadgeType::Milestone, 'category' => 'milestone', 'rarity' => 'legendary', 'xp_reward' => 2000, 'threshold' => 100, 'rules' => [['event_trigger' => 'level_reached', 'conditions' => ['level' => 100]]]];

        // 1. ONBOARDING BADGES (Added for StudentBadgesSeeder compatibility)
        $badges[] = ['code' => 'first_step', 'name' => 'Langkah Pertama', 'description' => 'Bagian dari permulaan perjalanan LMS Anda.', 'type' => BadgeType::Completion, 'threshold' => 1, 'rules' => [['event_trigger' => 'account_created']]];
        $badges[] = ['code' => 'rookie', 'name' => 'Pendatang Baru', 'description' => 'Mengeksplorasi LMS untuk pertama kali.', 'type' => BadgeType::Completion, 'threshold' => 1, 'rules' => [['event_trigger' => 'profile_updated']]];

        // 1. COMPLETION BADGES (20 Badges)
        $badges[] = ['code' => 'uiux_master', 'name' => 'UI/UX Design Master', 'description' => 'Menyelesaikan Skema UI/UX Design.', 'type' => BadgeType::Completion, 'threshold' => 1, 'rules' => [['event_trigger' => 'course_completed', 'conditions' => ['course_slug' => 'ui-ux-design']]]];
        $badges[] = ['code' => 'webdev_master', 'name' => 'Web Dev Master', 'description' => 'Menyelesaikan Skema Backend Web Development.', 'type' => BadgeType::Completion, 'threshold' => 1, 'rules' => [['event_trigger' => 'course_completed', 'conditions' => ['course_slug' => 'web-development']]]];

        for ($i = 1; $i <= 18; $i++) {
            $badges[] = [
                'code' => "course_finisher_{$i}",
                'name' => "Penyelesai Modul Level {$i}",
                'description' => "Menyelesaikan {$i} unit kursus dengan baik.",
                'type' => BadgeType::Completion,
                'threshold' => $i,
                'rules' => [['event_trigger' => 'unit_completed']],
            ];
        }

        // 2. QUALITY BADGES (20 Badges)
        $badges[] = ['code' => 'perfect_assignment', 'name' => 'Nilai Sempurna', 'description' => 'Mendapatkan nilai 100 pada penugasan.', 'type' => BadgeType::Quality, 'threshold' => 1, 'rules' => [['event_trigger' => 'assignment_graded', 'conditions' => ['min_score' => 100]]]];
        $badges[] = ['code' => 'perfect_quiz', 'name' => 'Kuis Akurat', 'description' => 'Mendapatkan nilai 100 pada kuis.', 'type' => BadgeType::Quality, 'threshold' => 1, 'rules' => [['event_trigger' => 'quiz_graded', 'conditions' => ['min_score' => 100]]]];
        $badges[] = ['code' => 'one_shot_kill', 'name' => 'Satu Tembakan', 'description' => 'Lulus kuis tanpa perbaikan', 'type' => BadgeType::Quality, 'threshold' => 1, 'rules' => [['event_trigger' => 'quiz_graded', 'conditions' => ['max_attempts' => 1, 'is_passed' => true]]]];

        for ($i = 1; $i <= 19; $i++) {
            $badges[] = [
                'code' => "quality_streak_{$i}",
                'name' => "Quality Assured {$i}x",
                'description' => "Secara konsisten menjaga performa evaluasi berkualitas sebanyak {$i} kali.",
                'type' => BadgeType::Quality,
                'threshold' => $i,
                'rules' => [['event_trigger' => 'assignment_graded', 'conditions' => ['min_score' => 85]]],
            ];
        }

        // 3. SPEED BADGES (22 Badges)
        $badges[] = ['code' => 'speed_runner', 'name' => 'Flash Learner', 'description' => 'Finish a course < 3 days.', 'type' => BadgeType::Speed, 'threshold' => 1, 'rules' => [['event_trigger' => 'course_completed', 'conditions' => ['max_duration_days' => 3]]]];
        $badges[] = ['code' => 'first_blood', 'name' => 'Pertama Mengumpul', 'description' => 'Menjadi orang pertama yang mengumpulkan assignment.', 'type' => BadgeType::Speed, 'threshold' => 1, 'rules' => [['event_trigger' => 'assignment_submitted', 'conditions' => ['is_first_submission' => true]]]];

        for ($i = 1; $i <= 20; $i++) {
            $badges[] = [
                'code' => "speedy_submission_{$i}",
                'name' => "Penyetor Cepat Level {$i}",
                'description' => "Mengeksekusi latihan dalam tenggat waktu yang mengesankan sebanyak {$i} kali.",
                'type' => BadgeType::Speed,
                'threshold' => $i,
                'rules' => [['event_trigger' => 'assignment_submitted', 'conditions' => ['max_duration_days' => 1]]],
            ];
        }

        // 4. HABIT BADGES (22 Badges)
        $badges[] = ['code' => 'login_streak_7', 'name' => 'Konsisten 7 Hari', 'description' => 'Login 7 hari berturut-turut.', 'type' => BadgeType::Habit, 'threshold' => 1, 'rules' => [['event_trigger' => 'login', 'conditions' => ['min_streak_days' => 7]]]];
        $badges[] = ['code' => 'login_streak_30', 'name' => 'Dedikasi Bulanan', 'description' => 'Login 30 hari berturut-turut.', 'type' => BadgeType::Habit, 'threshold' => 1, 'rules' => [['event_trigger' => 'login', 'conditions' => ['min_streak_days' => 30]]]];
        $badges[] = ['code' => 'morning_bird', 'name' => 'Burung Pagi', 'description' => 'Login sebelum jam 6:00 AM.', 'type' => BadgeType::Habit, 'threshold' => 5, 'rules' => [['event_trigger' => 'login', 'conditions' => ['time_before' => '06:00:00']]]];
        $badges[] = ['code' => 'weekend_warrior_unit', 'name' => 'Pejuang Akhir Pekan', 'description' => 'Menyelesaikan 5 Unit di akhir pekan.', 'type' => BadgeType::Habit, 'threshold' => 5, 'rules' => [['event_trigger' => 'unit_completed', 'conditions' => ['is_weekend' => true]]]];
        $badges[] = ['code' => 'weekend_warrior_lesson', 'name' => 'Belajar Tanpa Henti', 'description' => 'Menyelesaikan 5 Lesson di akhir pekan.', 'type' => BadgeType::Habit, 'threshold' => 5, 'rules' => [['event_trigger' => 'lesson_completed', 'conditions' => ['is_weekend' => true]]]];

        for ($i = 1; $i <= 17; $i++) {
            $badges[] = [
                'code' => "habit_builder_{$i}",
                'name' => 'Membangun Kebiasaan '.($i * 3).' Hari',
                'description' => 'Mencapai rantai aktivitas '.($i * 3).' hari berturut-turut.',
                'type' => BadgeType::Habit,
                'threshold' => 1,
                'rules' => [['event_trigger' => 'login', 'conditions' => ['min_streak_days' => ($i * 3)]]],
            ];
        }

        // 5. SOCIAL BADGES (12 Badges)
        $badges[] = ['code' => 'forum_popular', 'name' => 'Sangat Disukai', 'description' => 'Mendapatkan 10 likes di Forum.', 'type' => BadgeType::Social, 'threshold' => 10, 'rules' => [['event_trigger' => 'forum_liked']]];
        $badges[] = ['code' => 'forum_active', 'name' => 'Banyak Bicara', 'description' => 'Membuat 20 postingan forum.', 'type' => BadgeType::Social, 'threshold' => 20, 'rules' => [['event_trigger' => 'forum_post_created']]];
        $badges[] = ['code' => 'forum_helper', 'name' => 'Pahlawan Forum', 'description' => 'Membalas 5 pertanyaan tak terjawab.', 'type' => BadgeType::Social, 'threshold' => 5, 'rules' => [['event_trigger' => 'forum_reply_created', 'conditions' => ['is_unanswered' => true]]]];

        for ($i = 1; $i <= 9; $i++) {
            $badges[] = [
                'code' => "social_butterfly_{$i}",
                'name' => "Social Butterfly Bintang {$i}",
                'description' => 'Terlibat dalam interaksi kolektif dan komunal LMS sebanyak '.($i * 5).' kali.',
                'type' => BadgeType::Social,
                'threshold' => ($i * 5),
                'rules' => [['event_trigger' => 'forum_reply_created']],
            ];
        }

        // 6. HIDDEN BADGES (2 Badges)
        $badges[] = ['code' => 'night_owl', 'name' => 'Kelelawar Malam', 'description' => 'Mengumpulkan tugas di atas jam 12 Malam.', 'type' => BadgeType::Hidden, 'threshold' => 5, 'rules' => [['event_trigger' => 'assignment_submitted', 'conditions' => ['time_after' => '00:00:00', 'time_before' => '04:00:00']]]];
        $badges[] = ['code' => 'bug_hunter', 'name' => 'Pemburu Kutu', 'description' => 'Melaporkan bug pada sistem LMS.', 'type' => BadgeType::Hidden, 'threshold' => 1, 'rules' => [['event_trigger' => 'bug_reported']]];

        return $badges;
    }
}
