<?php

namespace Modules\Content\Database\Seeders;

use App\Support\SeederDate;
use Illuminate\Database\Seeder;
use Modules\Auth\Models\User;
use Modules\Common\Models\Category;
use Modules\Content\Models\Announcement;
use Modules\Content\Models\News;
use Modules\Schemes\Models\Course;

class ContentSeeder extends Seeder
{
    public function run(): void
    {

        $categories = [
            ['name' => 'Teknologi', 'value' => 'teknologi', 'scope' => 'news', 'status' => 'active'],
            ['name' => 'Pendidikan', 'value' => 'pendidikan', 'scope' => 'news', 'status' => 'active'],
            ['name' => 'Pengumuman Umum', 'value' => 'pengumuman-umum', 'scope' => 'news', 'status' => 'active'],
            ['name' => 'Event', 'value' => 'event', 'scope' => 'news', 'status' => 'active'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['value' => $category['value'], 'scope' => 'news'],
                $category
            );
        }

        $admin = User::whereHas('roles', function ($q) {
            $q->where('name', 'Admin');
        })->first();

        if (! $admin) {
            $this->command->warn('No admin user found. Skipping content seeding.');

            return;
        }

        $announcements = [
            [
                'title' => 'Selamat Datang di Platform LMS',
                'slug' => 'selamat-datang-di-platform-lms',
                'content' => 'Selamat datang di platform Learning Management System kami. Kami berkomitmen untuk memberikan pengalaman belajar terbaik.',
                'status' => 'published',
                'target_type' => 'all',
                'priority' => 'high',
                'published_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            ],
            [
                'title' => 'Pemeliharaan Sistem Terjadwal',
                'slug' => 'pemeliharaan-sistem-terjadwal',
                'content' => 'Sistem akan menjalani pemeliharaan rutin pada hari Minggu, 10 Desember 2025 pukul 02:00 - 06:00 WIB.',
                'status' => 'published',
                'target_type' => 'all',
                'priority' => 'normal',
                'published_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            ],
            [
                'title' => 'Pengumuman untuk Instruktur',
                'slug' => 'pengumuman-untuk-instruktur',
                'content' => 'Mohon semua instruktur untuk mengupdate materi kursus sebelum akhir bulan.',
                'status' => 'published',
                'target_type' => 'role',
                'target_value' => 'instructor',
                'priority' => 'normal',
                'published_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            ],
        ];

        foreach ($announcements as $announcementData) {
            Announcement::firstOrCreate(
                ['slug' => $announcementData['slug']],
                array_merge($announcementData, ['author_id' => $admin->id])
            );
        }

        $newsArticles = [
            [
                'title' => 'Platform LMS Meluncurkan Fitur Baru',
                'slug' => 'platform-lms-meluncurkan-fitur-baru',
                'excerpt' => 'Kami dengan bangga mengumumkan peluncuran fitur-fitur baru yang akan meningkatkan pengalaman belajar Anda.',
                'content' => 'Platform LMS kami terus berkembang dengan menambahkan fitur-fitur inovatif. Fitur terbaru termasuk sistem notifikasi yang lebih baik, dashboard yang diperbarui, dan integrasi dengan berbagai tools pembelajaran.',
                'status' => 'published',
                'is_featured' => true,
                'published_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            ],
            [
                'title' => 'Tips Belajar Efektif Online',
                'slug' => 'tips-belajar-efektif-online',
                'excerpt' => 'Pelajari cara memaksimalkan pembelajaran online Anda dengan tips-tips praktis ini.',
                'content' => 'Belajar online memerlukan disiplin dan strategi yang tepat. Berikut adalah beberapa tips untuk membantu Anda belajar lebih efektif: 1) Buat jadwal belajar yang konsisten, 2) Siapkan ruang belajar yang nyaman, 3) Aktif berpartisipasi dalam diskusi, 4) Manfaatkan semua resources yang tersedia.',
                'status' => 'published',
                'is_featured' => false,
                'published_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            ],
            [
                'title' => 'Webinar: Masa Depan Pendidikan Digital',
                'slug' => 'webinar-masa-depan-pendidikan-digital',
                'excerpt' => 'Bergabunglah dengan webinar kami tentang tren terbaru dalam pendidikan digital.',
                'content' => 'Kami mengundang Anda untuk mengikuti webinar eksklusif tentang masa depan pendidikan digital. Webinar ini akan membahas tren teknologi terkini, metodologi pembelajaran inovatif, dan bagaimana platform LMS dapat membantu mencapai tujuan pembelajaran Anda.',
                'status' => 'published',
                'is_featured' => true,
                'published_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            ],
        ];

        $categoryIds = Category::where('scope', 'news')->pluck('id')->toArray();

        foreach ($newsArticles as $newsData) {
            $newsData['is_featured'] = $this->pgsqlBool((bool) ($newsData['is_featured'] ?? false));

            $news = News::firstOrCreate(
                ['slug' => $newsData['slug']],
                array_merge($newsData, ['author_id' => $admin->id])
            );

            if ($news->wasRecentlyCreated && ! empty($categoryIds)) {
                $randomCategories = array_rand(array_flip($categoryIds), min(2, count($categoryIds)));
                $news->categories()->sync(is_array($randomCategories) ? $randomCategories : [$randomCategories]);
            }
        }

        $course = Course::query()
            ->where('status', 'published')
            ->inRandomOrder()
            ->first();
        if ($course) {
            Announcement::firstOrCreate(
                ['slug' => 'pengumuman-kursus-'.\Illuminate\Support\Str::slug($course->title)],
                [
                    'author_id' => $admin->id,
                    'course_id' => $course->id,
                    'title' => 'Pengumuman Kursus: '.$course->title,
                    'content' => 'Ini adalah pengumuman khusus untuk kursus '.$course->title.'. Mohon perhatikan jadwal dan deadline tugas.',
                    'status' => 'published',
                    'target_type' => 'course',
                    'priority' => 'normal',
                    'published_at' => SeederDate::randomPastDateTimeBetween(1, 180),
                ]
            );
        }

        $this->command->info('Content seeded successfully!');
    }

    private function pgsqlBool(bool $value): \Illuminate\Contracts\Database\Query\Expression
    {
        return \DB::raw($value ? 'true' : 'false');
    }
}
