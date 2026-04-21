<?php

declare(strict_types=1);

namespace Modules\Forums\Database\Seeders;

use Carbon\Carbon;
use App\Support\SeederDate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Forums\Models\Mention;
use Modules\Forums\Models\Reaction;
use Modules\Forums\Models\Reply;
use Modules\Forums\Models\Thread;
use Modules\Schemes\Models\Course;

class ForumSeeder extends Seeder
{
    public function run(): void
    {
        if (Thread::count() > 50) {
            $this->command->info('Forum threads already seeded; skipping.');

            return;
        }

        $this->command->info('Starting forum seeding...');

        $courses = Course::query()
            ->where('status', 'published')
            ->with('instructor:id')
            ->get();

        if ($courses->isEmpty()) {
            $this->command->warn('No published courses found. Cannot seed forum.');

            return;
        }

        foreach ($courses as $course) {
            $this->seedCourseForums($course);
        }

        $this->seedUserMentions($courses);

        $this->command->info('✓ Forum seeding completed successfully!');
    }

    private function seedCourseForums(Course $course): void
    {
        $pool = $this->participantsForCourse($course);
        if ($pool->count() < 2) {
            return;
        }

        $this->command->line("  → Seeding forum for course: {$course->title}");

        for ($i = 1; $i <= 10; $i++) {
            $thread = $this->createThread(
                $course,
                $pool,
                "Course Discussion: {$course->title} - Topic $i"
            );

            $this->createReplies($thread, $pool, rand(3, 8));

            if (rand(1, 100) <= 40) {
                $acceptedReply = $thread->replies()->inRandomOrder()->first();
                if ($acceptedReply) {
                    $acceptedReply->update(['is_accepted_answer' => $this->pgsqlBool(true)]);
                    $thread->update(['is_resolved' => $this->pgsqlBool(true)]);
                }
            }

            $this->createReactions($thread, $pool);
        }
    }

    private function participantsForCourse(Course $course): Collection
    {
        $studentIds = DB::table('enrollments')
            ->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])
            ->pluck('user_id')
            ->all();

        $instructorIds = [];
        if ($course->instructor_id) {
            $instructorIds[] = $course->instructor_id;
        }

        $ids = array_values(array_unique(array_merge($studentIds, $instructorIds)));

        if ($ids === []) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $ids)
            ->limit(30)
            ->get();
    }

    private function createThread(Course $course, Collection $pool, string $title): Thread
    {
        $author = $pool->random();
        $hasMention = rand(0, 100) < 40;
        $mentionedUsers = $hasMention ? $this->selectMentionedUsers($pool, $author->id, rand(1, 3)) : collect();
        $content = $this->generateThreadContent($mentionedUsers);

        $createdAt = SeederDate::randomPastCarbonBetween(1, 180);

        $thread = Thread::create([
            'course_id' => $course->id,
            'author_id' => $author->id,
            'title' => $title,
            'content' => $content,
            'is_pinned' => $this->pgsqlBool(rand(1, 10) > 8),
            'is_closed' => $this->pgsqlBool(rand(1, 10) > 8),
            'is_resolved' => $this->pgsqlBool(rand(1, 10) > 7),
            'views_count' => rand(5, 100),
            'replies_count' => 0,
            'last_activity_at' => $createdAt,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        foreach ($mentionedUsers as $user) {
            $this->createMention($thread, $user, $createdAt);
        }

        return $thread;
    }

    private function createReplies(Thread $thread, Collection $pool, int $count): void
    {
        $threadCreated = $thread->created_at ?? SeederDate::randomPastCarbonBetween(1, 180);

        for ($i = 1; $i <= $count; $i++) {
            $author = $pool->random();
            $hasMention = rand(0, 100) < 30;
            $mentionedUsers = $hasMention ? $this->selectMentionedUsers($pool, $author->id, rand(1, 3)) : collect();

            $repliedAt = Carbon::parse($threadCreated)
                ->addMinutes(rand(5, 60 * 24 * 14))
                ->addSeconds(rand(0, 59));

            $reply = Reply::create([
                'thread_id' => $thread->id,
                'author_id' => $author->id,
                'content' => $this->generateReplyContent($mentionedUsers),
                'parent_id' => null,
                'depth' => 0,
                'is_accepted_answer' => $this->pgsqlBool(false),
                'created_at' => $repliedAt,
                'updated_at' => $repliedAt,
            ]);

            foreach ($mentionedUsers as $user) {
                $this->createMention($reply, $user, $repliedAt);
            }

            if (rand(0, 1) && $i < $count) {
                $this->createNestedReply($thread, $reply, $pool, 1, $repliedAt);
            }

            $this->createReactions($reply, $pool);
        }

        $thread->increment('replies_count', $count);

        $latestReply = $thread->replies()->orderByDesc('created_at')->first();
        if ($latestReply) {
            $thread->update(['last_activity_at' => $latestReply->created_at]);
        }
    }

    private function createNestedReply(Thread $thread, Reply $parent, Collection $pool, int $depth, Carbon $parentCreatedAt): void
    {
        if ($depth > 3) {
            return;
        }

        $author = $pool->random();
        $hasMention = rand(0, 100) < 25;
        $mentionedUsers = $hasMention ? $this->selectMentionedUsers($pool, $author->id, 1) : collect();
        $repliedAt = $parentCreatedAt->copy()->addMinutes(rand(10, 60 * 24 * 3));

        $reply = Reply::create([
            'thread_id' => $thread->id,
            'parent_id' => $parent->id,
            'author_id' => $author->id,
            'content' => $this->generateReplyContent($mentionedUsers),
            'depth' => $depth,
            'is_accepted_answer' => $this->pgsqlBool(false),
            'created_at' => $repliedAt,
            'updated_at' => $repliedAt,
        ]);

        foreach ($mentionedUsers as $user) {
            $this->createMention($reply, $user, $repliedAt);
        }

        $thread->increment('replies_count');

        if (rand(0, 1) && $depth < 3) {
            $this->createNestedReply($thread, $reply, $pool, $depth + 1, $repliedAt);
        }
    }

    private function selectMentionedUsers(Collection $pool, int $excludeUserId, int $count = 1): Collection
    {
        $availableUsers = $pool->where('id', '!=', $excludeUserId);

        if ($availableUsers->isEmpty()) {
            return collect();
        }

        $count = min($count, $availableUsers->count());

        return $availableUsers->random($count);
    }

    private function createMention($model, User $user, ?Carbon $when = null): void
    {
        $timestamp = $when ?? SeederDate::randomPastCarbonBetween(1, 180);
        Mention::create([
            'user_id' => $user->id,
            'mentionable_type' => $model::class,
            'mentionable_id' => $model->id,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }

    private function createReactions($model, Collection $pool): void
    {
        $reactionCount = rand(0, 3);

        for ($i = 0; $i < $reactionCount; $i++) {
            try {
                $type = ['like', 'helpful', 'solved'][rand(0, 2)];

                Reaction::firstOrCreate([
                    'user_id' => $pool->random()->id,
                    'reactable_type' => $model::class,
                    'reactable_id' => $model->id,
                    'type' => $type,
                ]);
            } catch (\Exception $e) {
            }
        }
    }

    private function generateThreadContent(Collection $mentionedUsers): string
    {
        $nonMentionContents = [
            'Bagaimana cara solve masalah ini? Saya sudah coba berbagai cara tapi masih error.',
            'Apakah ada yang bisa explain konsep ini dengan cara yang lebih simple?',
            'Saya punya pertanyaan tentang implementation dari topik yang kemarin dibahas.',
            'Bisa minta bantuan untuk debug code saya? Saya stuck di bagian ini.',
            'Ada yang punya reference atau tutorial yang bisa help saya understand ini better?',
            'Saya ingin share solution yang mungkin bisa membantu teman-teman lainnya.',
            'Bisa explain lebih detail tentang edge case yang mungkin terjadi di sini?',
            'Saya sempat baca documentation tapi masih kurang jelas, ada yang bisa clarify?',
            'Kenapa hasil output saya berbeda dengan yang di tutorial? Ada yang salah dengan setup saya?',
            'Saya berhasil implement fitur ini, tapi performance-nya kurang optimal. Ada saran?',
            'Apakah best practice untuk handle error di kasus seperti ini?',
            'Saya menemukan bug di library yang kita pakai, ada workaround yang bisa digunakan?',
        ];

        $content = $nonMentionContents[array_rand($nonMentionContents)];

        if ($mentionedUsers->isNotEmpty()) {
            $mentionPrefixes = [' cc ', ' FYI ', ' ', "\n\n"];
            $prefix = $mentionPrefixes[array_rand($mentionPrefixes)];
            $mentions = $mentionedUsers->map(fn ($u) => "@{$u->username}")->implode(' ');
            $content .= $prefix.$mentions;
        }

        return $content;
    }

    private function generateReplyContent(Collection $mentionedUsers): string
    {
        $nonMentionReplies = [
            'Coba approach dengan cara ini, semoga membantu!',
            'Saya pernah hadapi masalah yang sama, solution-nya adalah dengan mengubah konfigurasi di file .env',
            'Good point! Aku setuju dengan perspective ini.',
            'Benar sekali, ini adalah best practice untuk kasus seperti ini.',
            'Terima kasih atas input-nya, sangat membantu!',
            'Interesting! Aku belum pernah pikir dari angle itu.',
            'This makes sense now, thank you for explaining!',
            'Setuju banget dengan penjelasan ini, very clear!',
            'Kalau boleh saran, coba refactor code-nya supaya lebih readable.',
            'Aku pernah baca artikel tentang ini, wait aku cariin link-nya.',
            'Hmm, sepertinya ada typo di code kamu. Coba cek lagi bagian variable declaration.',
            'Solusi yang bagus! Tapi mungkin perlu consider juga untuk edge case ketika data kosong.',
        ];

        $content = $nonMentionReplies[array_rand($nonMentionReplies)];

        if ($mentionedUsers->isNotEmpty()) {
            $mentionPositions = ['prefix', 'suffix'];
            $position = $mentionPositions[array_rand($mentionPositions)];
            $mentions = $mentionedUsers->map(fn ($u) => "@{$u->username}")->implode(' ');

            if ($position === 'prefix') {
                $content = $mentions.' '.$content;
            } else {
                $content .= ' '.$mentions;
            }
        }

        return $content;
    }

    private function seedUserMentions(Collection $courses): void
    {
        $this->command->line('  → Ensuring key users have at least one mention...');

        $mentionedUserIds = Mention::distinct('user_id')->pluck('user_id')->toArray();

        $eligibleUserIds = User::query()
            ->whereNull('deleted_at')
            ->limit(50)
            ->pluck('id')
            ->all();

        $unmentionedUserIds = array_values(array_diff($eligibleUserIds, $mentionedUserIds));

        if ($unmentionedUserIds === []) {
            $this->command->info('    ✓ All sampled users already have mentions');

            return;
        }

        $this->command->info('    → Creating threads to mention '.count($unmentionedUserIds).' users');

        foreach ($unmentionedUserIds as $targetUserId) {
            $targetUser = User::find($targetUserId);
            if (! $targetUser) {
                continue;
            }

            $course = $this->courseForUser($targetUser->id, $courses);
            if (! $course) {
                continue;
            }

            $pool = $this->participantsForCourse($course);
            if ($pool->count() < 2) {
                continue;
            }

            $authorCandidates = $pool->where('id', '!=', $targetUser->id);
            if ($authorCandidates->isEmpty()) {
                continue;
            }
            $author = $authorCandidates->random();

            $mentionedUsers = collect([$targetUser]);
            $otherMentions = rand(0, 2);
            if ($otherMentions > 0) {
                $additionalUsers = $this->selectMentionedUsers($pool, $author->id, $otherMentions)
                    ->filter(fn ($u) => $u->id !== $targetUser->id);
                $mentionedUsers = $mentionedUsers->merge($additionalUsers)->unique('id');
            }

            $content = $this->generateThreadContent($mentionedUsers);
            $createdAt = SeederDate::randomPastCarbonBetween(1, 180);

            $thread = Thread::create([
                'course_id' => $course->id,
                'author_id' => $author->id,
                'title' => "Discussion: Question for @{$targetUser->username}",
                'content' => $content,
                'is_pinned' => $this->pgsqlBool(false),
                'is_closed' => $this->pgsqlBool(false),
                'is_resolved' => $this->pgsqlBool(false),
                'views_count' => rand(5, 50),
                'replies_count' => 0,
                'last_activity_at' => $createdAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($mentionedUsers as $user) {
                $this->createMention($thread, $user, $createdAt);
            }
        }
    }

    private function courseForUser(int $userId, Collection $courses): ?Course
    {
        $enrolledCourseId = DB::table('enrollments')
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'completed', 'pending'])
            ->inRandomOrder()
            ->value('course_id');

        if ($enrolledCourseId) {
            $match = $courses->firstWhere('id', $enrolledCourseId);
            if ($match) {
                return $match;
            }
        }

        return $courses->isEmpty() ? null : $courses->random();
    }

    private function pgsqlBool(bool $value): \Illuminate\Contracts\Database\Query\Expression
    {
        return DB::raw($value ? 'true' : 'false');
    }
}
