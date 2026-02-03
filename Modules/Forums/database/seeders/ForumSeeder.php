<?php

namespace Modules\Forums\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Models\User;
use Modules\Forums\Models\Reaction;
use Modules\Forums\Models\Reply;
use Modules\Forums\Models\Thread;
use Modules\Schemes\Models\Course;

class ForumSeeder extends Seeder
{
     
    public function run(): void
    {
        $users = User::limit(10)->get();
        $courses = Course::limit(3)->get();

        if ($users->isEmpty() || $courses->isEmpty()) {
            $this->command->warn('No users or courses found. Please seed users and courses first.');
            return;
        }

        foreach ($courses as $course) {
            for ($i = 1; $i <= 5; $i++) {
                $this->seedThread($course, $users, $i);
            }
        }

        $this->command->info('Forum seeder completed successfully!');
    }

    private function seedThread($course, $users, int $index): void
    {
        $author = $users->random();
        $thread = Thread::create([
            'scheme_id' => $course->id,
            'author_id' => $author->id,
            'title' => "Sample Thread $index for {$course->name}",
            'content' => "This is the content of sample thread $index. It contains some discussion.",
            'is_pinned' => $index === 1,
            'is_closed' => false,
            'is_resolved' => false,
            'views_count' => rand(10, 100),
            'last_activity_at' => now()->subDays(rand(0, 7)),
        ]);

        $this->seedReplies($thread, $users, $index);
        $this->seedReactions($thread, $users, Thread::class);
        
        $thread->replies_count = $thread->replies()->count();
        $thread->save();
    }

    private function seedReplies(Thread $thread, $users, int $threadIndex): void
    {
        $replyCount = rand(3, 7);
        for ($j = 1; $j <= $replyCount; $j++) {
            $this->createReply($thread, $users, $j, $threadIndex);
        }
    }

    private function createReply(Thread $thread, $users, int $replyIndex, int $threadIndex): void
    {
        $reply = Reply::create([
            'thread_id' => $thread->id,
            'parent_id' => null,
            'author_id' => $users->random()->id,
            'content' => "This is reply $replyIndex.",
            'depth' => 0,
            'is_accepted_answer' => $replyIndex === 1 && $threadIndex % 2 === 0,
        ]);

        if (rand(0, 1)) {
            $this->createNestedReply($thread, $reply, $users);
        }

        if (rand(0, 1)) {
            $this->seedReactions($reply, $users, Reply::class);
        }
    }

    private function createNestedReply(Thread $thread, Reply $parent, $users): void
    {
        Reply::create([
            'thread_id' => $thread->id,
            'parent_id' => $parent->id,
            'author_id' => $users->random()->id,
            'content' => 'This is a nested reply.',
            'depth' => 1,
        ]);
    }

    private function seedReactions($model, $users, string $type): void
    {
        $count = rand(1, 3);
        // Avoid duplicate logic simplified
        for ($k = 0; $k < $count; $k++) {
            try {
                Reaction::create([
                    'user_id' => $users->random()->id,
                    'reactable_type' => $type,
                    'reactable_id' => $model->id,
                    'type' => ['like', 'helpful', 'solved'][rand(0, 2)],
                ]);
            } catch (\Exception $e) {
                // Ignore duplicates
            }
        }
    }
}
