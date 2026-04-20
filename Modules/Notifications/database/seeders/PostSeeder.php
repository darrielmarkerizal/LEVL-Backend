<?php

declare(strict_types=1);

namespace Modules\Notifications\Database\Seeders;

use App\Support\SeederDate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Auth\Models\User;
use Modules\Notifications\Enums\PostCategory;
use Modules\Notifications\Enums\PostStatus;
use Modules\Notifications\Models\Post;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $authorPool = User::query()
            ->role(['Admin', 'Superadmin', 'Instructor'])
            ->orderBy('id')
            ->limit(10)
            ->pluck('id')
            ->all();

        if ($authorPool === []) {
            $this->command?->warn('No admin, superadmin, or instructor users found for post seeding.');

            return;
        }

        $titles = [
            'LSP Certification Batch Registration Update',
            'New Learning Elements Added to the Curriculum',
            'Weekend Challenge Bonus Points Announcement',
            'Submission Deadline Extension Notice',
            'Scheduled Platform Maintenance Notice',
            'Top Candidate Recognition for January',
            'New Practice Quiz Pack Released',
            'Instructor Workshop Registration Now Open',
            'Performance Improvement Release Notes',
            'Monthly Achievement Highlights',
        ];

        $categories = [
            PostCategory::ANNOUNCEMENT,
            PostCategory::INFORMATION,
            PostCategory::GAMIFICATION,
            PostCategory::WARNING,
            PostCategory::SYSTEM,
            PostCategory::AWARD,
        ];

        for ($index = 1; $index <= 100; $index++) {
            $title = $titles[($index - 1) % count($titles)].' #'.str_pad((string) $index, 3, '0', STR_PAD_LEFT);
            $category = $categories[($index - 1) % count($categories)];
            $publishedAt = SeederDate::randomPastCarbonBetween(1, 180);
            $authorId = $authorPool[($index - 1) % count($authorPool)];
            $editorId = $authorPool[$index % count($authorPool)];

            Post::query()->firstOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'uuid' => (string) Str::uuid(),
                    'title' => $title,
                    'content' => $this->buildContent($title, $category),
                    'category' => $category->value,
                    'status' => PostStatus::PUBLISHED->value,
                    'is_pinned' => $index <= 3,
                    'author_id' => $authorId,
                    'last_editor_id' => $editorId,
                    'published_at' => $publishedAt,
                    'scheduled_at' => null,
                    'created_at' => $publishedAt,
                    'updated_at' => $publishedAt,
                ]
            );
        }
    }

    private function buildContent(string $title, PostCategory $category): string
    {
        return sprintf(
            '<p>%s for the %s category. This seeded post is used to populate the dashboard latest posts section.</p>',
            $title,
            $category->value
        );
    }
}
