<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class OpenApiGeneratorService
{
    protected array $featureGroups = [
        '01-auth' => [
            'label' => '02 - Autentikasi & Registrasi',
            'description' => 'Fitur autentikasi, registrasi, dan manajemen sesi pengguna. Rate limit: 10 requests per minute untuk endpoint auth (login, register, password reset).',
            'features' => [
                'verifikasi-email' => [
                    'label' => 'Verifikasi Email',
                    'description' => 'Verifikasi alamat email pengguna baru. Sistem mendukung 2 metode verifikasi: (1) OTP Code - menggunakan UUID/token + kode 6 digit, (2) Magic Link - menggunakan token 16 karakter dari link email.',
                    'modules' => ['Auth'],
                    'keywords' => ['email/verify', 'email/verify/by-token', 'email/resend'],
                ],
                'reset-password' => [
                    'label' => 'Reset & Ubah Password',
                    'description' => 'Proses reset password jika lupa atau ubah password.',
                    'modules' => ['Auth'],
                    'keywords' => ['password/reset', 'password/email', 'password/change'],
                ],
                'login-logout' => [
                    'label' => 'Login & Logout',
                    'description' => 'Masuk dan keluar dari aplikasi.',
                    'modules' => ['Auth'],
                    'keywords' => ['login', 'logout'],
                ],
                'registrasi' => [
                    'label' => 'Registrasi Asesi',
                    'description' => 'Pendaftaran akun baru untuk asesi.',
                    'modules' => ['Auth'],
                    'keywords' => ['register'],
                ],
                'oauth' => [
                    'label' => 'Autentikasi Google (OAuth)',
                    'description' => 'Login menggunakan akun Google.',
                    'modules' => ['Auth'],
                    'keywords' => ['google', 'oauth', 'set-username'],
                ],
                'token' => [
                    'label' => 'Manajemen Token Akses',
                    'description' => 'Refresh token dan manajemen sesi.',
                    'modules' => ['Auth'],
                    'keywords' => ['refresh', 'token'],
                ],
            ],
        ],
        '03-forum' => [
            'label' => '03 - Forum Diskusi',
            'description' => 'Forum diskusi untuk tanya jawab dan interaksi antar pengguna.',
            'features' => [
                'forum-skema' => [
                    'label' => 'Forum Skema',
                    'description' => 'Forum diskusi spesifik untuk skema/kelas tertentu.',
                    'modules' => ['Learning', 'Schemes', 'Forums'],
                    'keywords' => ['forum/schemes', 'forum/statistics', 'schemes/forum', 'statistics/me'],
                ],
                'topik' => [
                    'label' => 'Topik & Thread Diskusi',
                    'description' => 'Membuat dan melihat topik diskusi.',
                    'modules' => ['Learning', 'Forums'],
                    'keywords' => ['forum/threads', 'forum/topics', 'threads/search', 'threads/pin', 'threads/close', 'threads/reactions'],
                ],
                'komentar' => [
                    'label' => 'Komentar & Balasan',
                    'description' => 'Memberikan komentar atau balasan pada diskusi.',
                    'modules' => ['Learning', 'Forums'],
                    'keywords' => ['forum/comments', 'forum/replies', 'replies/accept', 'replies/reactions', 'threads/replies'],
                ],
            ],
        ],
        '04-gamifikasi' => [
            'label' => '04 - Gamifikasi & Perkembangan',
            'description' => 'Fitur gamifikasi untuk meningkatkan motivasi belajar.',
            'features' => [
                'progress' => [
                    'label' => 'Progress Kursus',
                    'description' => 'Melacak kemajuan belajar pengguna.',
                    'modules' => ['Learning', 'Enrollments'],
                    'keywords' => ['progress', 'completion'],
                ],
                'poin' => [
                    'label' => 'Poin & Badges',
                    'description' => 'Sistem poin dan lencana penghargaan.',
                    'modules' => ['Gamification'],
                    'keywords' => ['points', 'badges', 'gamification/summary', 'gamification/badges', 'gamification/points-history', 'gamification/achievements'],
                ],
                'level' => [
                    'label' => 'Level & XP',
                    'description' => 'Tingkatan level dan experience points pengguna.',
                    'modules' => ['Gamification'],
                    'keywords' => ['levels', 'xp'],
                ],
                'challenges' => [
                    'label' => 'Challenges',
                    'description' => 'Tantangan harian dan mingguan untuk mendapatkan XP dan badge.',
                    'modules' => ['Gamification'],
                    'keywords' => ['challenges', 'challenges/my', 'challenges/completed', 'challenges/claim'],
                ],
                'leaderboard' => [
                    'label' => 'Leaderboard',
                    'description' => 'Papan peringkat pengguna terbaik.',
                    'modules' => ['Gamification'],
                    'keywords' => ['leaderboard', 'leaderboards', 'leaderboards/my-rank'],
                ],
            ],
        ],
        '05-info' => [
            'label' => '05 - Informasi & Notifikasi',
            'description' => 'Pusat informasi, berita, dan notifikasi sistem.',
            'features' => [
                'berita' => [
                    'label' => 'Berita & Pengumuman',
                    'description' => 'Berita terbaru dan pengumuman penting.',
                    'modules' => ['Content', 'Common', 'Operations'],
                    'keywords' => ['news', 'announcements', 'info', 'content/statistics', 'content/search', 'content/pending', 'content/submit', 'content/approve', 'content/reject', 'courses/announcements', 'pending-review'],
                ],
                'notifikasi-sistem' => [
                    'label' => 'Notifikasi Sistem',
                    'description' => 'Pengaturan dan log notifikasi sistem.',
                    'modules' => ['Notifications'],
                    'keywords' => ['notifications/system'],
                ],
                'notifikasi-inapp' => [
                    'label' => 'Notifikasi In-App',
                    'description' => 'Daftar notifikasi di dalam aplikasi.',
                    'modules' => ['Notifications'],
                    'keywords' => ['notifications', 'notification-preferences'],
                ],
                'pencarian' => [
                    'label' => 'Pencarian',
                    'description' => 'Fitur pencarian kursus dan konten.',
                    'modules' => ['Search'],
                    'keywords' => ['search/courses', 'search/autocomplete', 'search/history'],
                ],
            ],
        ],
        '06-materi' => [
            'label' => '06 - Materi Pembelajaran',
            'description' => 'Konten pembelajaran termasuk unit, lesson, dan media.',
            'features' => [
                'media' => [
                    'label' => 'Media Pembelajaran',
                    'description' => 'Manajemen file media pembelajaran.',
                    'modules' => ['Learning', 'Common'],
                    'keywords' => ['media', 'files'],
                ],
            ],
        ],
        '07-profil' => [
            'label' => '07 - Manajemen Profil',
            'description' => 'Pengaturan akun dan profil pengguna.',
            'features' => [
                'profil' => [
                    'label' => 'Profil Pengguna (Lihat & Update)',
                    'description' => 'Melihat dan mengubah data profil.',
                    'modules' => ['Auth'],
                    'keywords' => ['profile', 'me', 'profile/privacy', 'profile/activities', 'profile/statistics', 'profile/achievements', 'users/profile'],
                ],
                'email' => [
                    'label' => 'Manajemen Email',
                    'description' => 'Pengaturan alamat email.',
                    'modules' => ['Auth'],
                    'keywords' => ['email/update'],
                ],
                'password' => [
                    'label' => 'Manajemen Password',
                    'description' => 'Ubah password akun.',
                    'modules' => ['Auth'],
                    'keywords' => ['password/update', 'profile/password'],
                ],
                'avatar' => [
                    'label' => 'Avatar & Data Pribadi',
                    'description' => 'Upload avatar dan update data pribadi.',
                    'modules' => ['Auth'],
                    'keywords' => ['avatar', 'biodata', 'profile/avatar'],
                ],
                'achievements' => [
                    'label' => 'Pencapaian & Badge',
                    'description' => 'Melihat pencapaian dan mengelola badge.',
                    'modules' => ['Auth', 'Gamification'],
                    'keywords' => ['profile/achievements', 'badges/pin', 'badges/unpin'],
                ],
                'account' => [
                    'label' => 'Manajemen Akun',
                    'description' => 'Hapus dan restore akun pengguna.',
                    'modules' => ['Auth'],
                    'keywords' => ['profile/account', 'account/restore', 'account/delete'],
                ],
            ],
        ],
        '08-Skema' => [
            'label' => '08 - Skema & Materi',
            'description' => 'Manajemen struktur skema dan materi pembelajaran.',
            'features' => [
                'skema' => [
                    'label' => 'Course',
                    'description' => 'Daftar skema dan detail kelas.',
                    'modules' => ['Schemes'],
                    'keywords' => ['schemes', 'courses'],
                ],
                'unit' => [
                    'label' => 'Unit',
                    'description' => 'Unit kompetensi atau modul pembelajaran.',
                    'modules' => ['Schemes', 'Learning'],
                    'keywords' => ['units'],
                ],
                'lesson' => [
                    'label' => 'Lesson',
                    'description' => 'Sesi pembelajaran individual.',
                    'modules' => ['Schemes', 'Learning'],
                    'keywords' => ['lessons'],
                ],
                'blocks' => [
                    'label' => 'Lesson Block',
                    'description' => 'Blok konten dalam lesson (teks, video, dokumen).',
                    'modules' => ['Schemes', 'Learning'],
                    'keywords' => ['blocks', 'materials'],
                ],
            ],
        ],
        '09-kelas' => [
            'label' => '09 - Kelas & Pendaftaran',
            'description' => 'Manajemen pendaftaran kelas dan kategori. Rate limit: 5 requests per minute untuk endpoint enrollment (enroll, cancel, withdraw).',
            'features' => [
                'enrollments' => [
                    'label' => 'Pendaftaran Kelas (Enrollments)',
                    'description' => 'Proses pendaftaran peserta ke kelas.',
                    'modules' => ['Enrollments'],
                    'keywords' => ['enrollments', 'join'],
                ],
                'approval' => [
                    'label' => 'Persetujuan Enroll (Admin & Instruktur)',
                    'description' => 'Persetujuan pendaftaran oleh admin/instruktur.',
                    'modules' => ['Enrollments'],
                    'keywords' => ['enrollments/approve', 'enrollments/reject'],
                ],
                'kategori' => [
                    'label' => 'Kategori Skema',
                    'description' => 'Kategori dan pengelompokan skema.',
                    'modules' => ['Schemes'],
                    'keywords' => ['categories'],
                ],
                'tags' => [
                    'label' => 'Tag Kursus',
                    'description' => 'Label atau tag untuk pencarian kursus.',
                    'modules' => ['Schemes'],
                    'keywords' => ['tags'],
                ],
                'exports' => [
                    'label' => 'Export Data Kelas',
                    'description' => 'Export data pendaftaran dan laporan kelas.',
                    'modules' => ['Enrollments', 'Schemes'],
                    'keywords' => ['exports', 'enrollments-csv', 'reports/completion-rate', 'reports/enrollment-funnel'],
                ],
            ],
        ],
        '10-sistem' => [
            'label' => '10 - Sistem Manajemen',
            'description' => 'Fitur administratif dan pengaturan sistem.',
            'features' => [
                'users' => [
                    'label' => 'Manajemen Pengguna',
                    'description' => 'CRUD pengguna dan manajemen status.',
                    'modules' => ['Auth'],
                    'keywords' => ['users', 'profile', 'updateUserStatus', 'admin/users', 'suspend', 'activate', 'audit-logs'],
                ],
                'master' => [
                    'label' => 'Master Data',
                    'description' => 'Data referensi sistem termasuk enum values untuk status, tipe, dan kategori yang digunakan di seluruh aplikasi.',
                    'modules' => ['Master', 'Common'],
                    'keywords' => ['master', 'master-data', 'provinces', 'cities', 'user-status', 'roles', 'course-status', 'course-types', 'enrollment-types', 'level-tags', 'progression-modes', 'content-types', 'enrollment-status', 'progress-status', 'assignment-status', 'submission-status', 'submission-types', 'content-status', 'priorities', 'target-types', 'challenge-types', 'challenge-assignment-status', 'challenge-criteria-types', 'badge-types', 'point-source-types', 'point-reasons', 'notification-types', 'notification-channels', 'notification-frequencies', 'grade-status', 'grade-source-types', 'category-status'],
                ],
                'roles' => [
                    'label' => 'Role & Hak Akses',
                    'description' => 'Pengaturan role dan permission.',
                    'modules' => ['Roles'],
                    'keywords' => ['roles', 'permissions'],
                ],
                'settings' => [
                    'label' => 'Pengaturan Sistem',
                    'description' => 'Konfigurasi global aplikasi.',
                    'modules' => ['Settings'],
                    'keywords' => ['settings'],
                ],
            ],
        ],
        '11-tugas' => [
            'label' => '11 - Tugas & Latihan',
            'description' => 'Manajemen tugas, latihan soal, dan penilaian.',
            'features' => [
                'bank-soal' => [
                    'label' => 'Bank Soal (PG, Esai, File Upload)',
                    'description' => 'Bank soal umum untuk latihan dan tugas.',
                    'modules' => ['Learning'],
                    'keywords' => ['banks', 'questions'],
                ],
                'tugas' => [
                    'label' => 'Tugas & Latihan Soal',
                    'description' => 'Daftar tugas yang harus dikerjakan.',
                    'modules' => ['Learning'],
                    'keywords' => ['assignments', 'exercises', 'lessons/assignments'],
                ],
                'submission' => [
                    'label' => 'Pengumpulan Jawaban',
                    'description' => 'Submit jawaban tugas atau latihan.',
                    'modules' => ['Learning'],
                    'keywords' => ['submissions', 'answers', 'assignments/submissions'],
                ],
                'penilaian' => [
                    'label' => 'Penilaian Tugas & Latihan',
                    'description' => 'Pemberian nilai dan feedback.',
                    'modules' => ['Learning'],
                    'keywords' => ['grades', 'feedback'],
                ],
            ],
        ],
    ];

    public function generate(): array
    {
        $spec = [
            'openapi' => '3.1.0',
            'info' => [
                'title' => config('app.name', 'Laravel API').' Documentation',
                'version' => '1.0.0',
                'description' => 'Dokumentasi API untuk aplikasi '.
                  config('app.name', 'Laravel API').
                  ' yang diorganisir per fitur sesuai kebutuhan Peserta, Instruktur, dan Admin.',
                'contact' => [
                    'name' => 'API Support',
                ],
            ],
            'servers' => [
                [
                    'url' => config('app.url').'/api',
                    'description' => 'API Server',
                ],
            ],
            'tags' => [],
            'x-tagGroups' => [],
            'paths' => [],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                        'description' => 'JWT Authentication. Gunakan token yang didapat dari endpoint login.',
                    ],
                ],
                'schemas' => [
                    'SuccessResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'success' => [
                                'type' => 'boolean',
                                'example' => true,
                                'description' => 'Indicates if the request was successful',
                            ],
                            'message' => [
                                'type' => 'string',
                                'example' => 'Berhasil',
                                'description' => 'Pesan sukses yang menjelaskan operasi yang dilakukan',
                            ],
                            'data' => [
                                'description' => 'Data response, bisa berupa object, array, atau null',
                                'nullable' => true,
                            ],
                            'meta' => [
                                'type' => 'object',
                                'description' => 'Metadata tambahan (opsional)',
                                'nullable' => true,
                                'additionalProperties' => true,
                            ],
                            'errors' => [
                                'type' => 'null',
                                'description' => 'Always null for success responses',
                            ],
                        ],
                        'required' => ['success', 'message', 'data', 'meta', 'errors'],
                    ],
                    'ErrorResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'success' => [
                                'type' => 'boolean',
                                'example' => false,
                                'description' => 'Always false for error responses',
                            ],
                            'message' => [
                                'type' => 'string',
                                'example' => 'Terjadi kesalahan',
                            ],
                            'data' => [
                                'type' => 'null',
                                'description' => 'Usually null for error responses',
                                'nullable' => true,
                            ],
                            'meta' => [
                                'type' => 'object',
                                'description' => 'Metadata tambahan (opsional)',
                                'nullable' => true,
                                'additionalProperties' => true,
                            ],
                            'errors' => [
                                'description' => 'Detail error (opsional, biasanya untuk validation errors)',
                                'nullable' => true,
                                'oneOf' => [
                                    [
                                        'type' => 'object',
                                        'additionalProperties' => [
                                            'type' => 'array',
                                            'items' => ['type' => 'string'],
                                        ],
                                    ],
                                    ['type' => 'null'],
                                ],
                            ],
                        ],
                        'required' => ['success', 'message', 'data', 'meta', 'errors'],
                    ],
                    'PaginationMeta' => [
                        'type' => 'object',
                        'properties' => [
                            'current_page' => [
                                'type' => 'integer',
                                'description' => 'Halaman saat ini',
                            ],
                            'per_page' => [
                                'type' => 'integer',
                                'description' => 'Jumlah item per halaman',
                            ],
                            'total' => [
                                'type' => 'integer',
                                'description' => 'Total jumlah item',
                            ],
                            'last_page' => [
                                'type' => 'integer',
                                'description' => 'Halaman terakhir',
                            ],
                            'from' => [
                                'type' => 'integer',
                                'nullable' => true,
                                'description' => 'Index item pertama di halaman ini',
                            ],
                            'to' => [
                                'type' => 'integer',
                                'nullable' => true,
                                'description' => 'Index item terakhir di halaman ini',
                            ],
                            'has_next' => [
                                'type' => 'boolean',
                                'description' => 'Apakah masih ada halaman berikutnya',
                            ],
                            'has_prev' => [
                                'type' => 'boolean',
                                'description' => 'Apakah ada halaman sebelumnya',
                            ],
                        ],
                        'required' => ['current_page', 'per_page', 'total', 'last_page', 'has_next', 'has_prev'],
                    ],
                    'PaginatedResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'success' => [
                                'type' => 'boolean',
                                'example' => true,
                            ],
                            'message' => [
                                'type' => 'string',
                                'example' => 'Berhasil',
                            ],
                            'data' => [
                                'type' => 'array',
                                'description' => 'Array of items',
                                'items' => ['type' => 'object'],
                            ],
                            'meta' => [
                                'type' => 'object',
                                'properties' => [
                                    'pagination' => [
                                        '$ref' => '#/components/schemas/PaginationMeta',
                                    ],
                                ],
                                'required' => ['pagination'],
                            ],
                            'errors' => [
                                'type' => 'null',
                            ],
                        ],
                        'required' => ['success', 'message', 'data', 'meta', 'errors'],
                    ],
                    // Auth Enums
                    'UserStatus' => $this->enumToSchema(\Modules\Auth\Enums\UserStatus::class, 'Status pengguna'),

                    // Schemes Enums
                    'CourseStatus' => $this->enumToSchema(\Modules\Schemes\Enums\CourseStatus::class, 'Status kursus'),
                    'CourseType' => $this->enumToSchema(\Modules\Schemes\Enums\CourseType::class, 'Tipe kursus'),
                    'EnrollmentType' => $this->enumToSchema(\Modules\Schemes\Enums\EnrollmentType::class, 'Tipe pendaftaran kursus'),
                    'LevelTag' => $this->enumToSchema(\Modules\Schemes\Enums\LevelTag::class, 'Level kesulitan'),
                    'ProgressionMode' => $this->enumToSchema(\Modules\Schemes\Enums\ProgressionMode::class, 'Mode progres pembelajaran'),
                    'ContentType' => $this->enumToSchema(\Modules\Schemes\Enums\ContentType::class, 'Tipe konten lesson'),

                    // Enrollments Enums
                    'EnrollmentStatus' => $this->enumToSchema(\Modules\Enrollments\Enums\EnrollmentStatus::class, 'Status pendaftaran'),
                    'ProgressStatus' => $this->enumToSchema(\Modules\Enrollments\Enums\ProgressStatus::class, 'Status progres pembelajaran'),

                    // Learning Enums
                    'AssignmentStatus' => $this->enumToSchema(\Modules\Learning\Enums\AssignmentStatus::class, 'Status tugas'),
                    'SubmissionStatus' => $this->enumToSchema(\Modules\Learning\Enums\SubmissionStatus::class, 'Status pengumpulan'),
                    'SubmissionType' => $this->enumToSchema(\Modules\Learning\Enums\SubmissionType::class, 'Tipe pengumpulan'),

                    // Content Enums
                    'ContentStatus' => $this->enumToSchema(\Modules\Content\Enums\ContentStatus::class, 'Status konten berita/pengumuman'),
                    'Priority' => $this->enumToSchema(\Modules\Content\Enums\Priority::class, 'Prioritas'),
                    'TargetType' => $this->enumToSchema(\Modules\Content\Enums\TargetType::class, 'Tipe target audiens'),

                    // Gamification Enums
                    'ChallengeType' => $this->enumToSchema(\Modules\Gamification\Enums\ChallengeType::class, 'Tipe tantangan'),
                    'ChallengeAssignmentStatus' => $this->enumToSchema(\Modules\Gamification\Enums\ChallengeAssignmentStatus::class, 'Status tantangan user'),
                    'ChallengeCriteriaType' => $this->enumToSchema(\Modules\Gamification\Enums\ChallengeCriteriaType::class, 'Tipe kriteria tantangan'),
                    'BadgeType' => $this->enumToSchema(\Modules\Gamification\Enums\BadgeType::class, 'Tipe badge'),
                    'PointSourceType' => $this->enumToSchema(\Modules\Gamification\Enums\PointSourceType::class, 'Sumber poin'),
                    'PointReason' => $this->enumToSchema(\Modules\Gamification\Enums\PointReason::class, 'Alasan pemberian poin'),

                    // Notifications Enums
                    'NotificationType' => $this->enumToSchema(\Modules\Notifications\Enums\NotificationType::class, 'Tipe notifikasi'),
                    'NotificationChannel' => $this->enumToSchema(\Modules\Notifications\Enums\NotificationChannel::class, 'Channel notifikasi'),
                    'NotificationFrequency' => $this->enumToSchema(\Modules\Notifications\Enums\NotificationFrequency::class, 'Frekuensi notifikasi'),

                    // Grading Enums
                    'GradeStatus' => $this->enumToSchema(\Modules\Grading\Enums\GradeStatus::class, 'Status nilai'),
                    'GradeSourceType' => $this->enumToSchema(\Modules\Grading\Enums\SourceType::class, 'Sumber nilai'),

                    // Common Enums
                    'CategoryStatus' => $this->enumToSchema(\Modules\Common\Enums\CategoryStatus::class, 'Status kategori'),

                    'RateLimitError' => [
                        'type' => 'object',
                        'properties' => [
                            'success' => [
                                'type' => 'boolean',
                                'example' => false,
                            ],
                            'message' => [
                                'type' => 'string',
                                'example' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
                            ],
                            'data' => [
                                'type' => 'null',
                            ],
                            'meta' => [
                                'type' => 'object',
                                'properties' => [
                                    'retry_after' => [
                                        'type' => 'integer',
                                        'description' => 'Waktu dalam detik sebelum dapat mencoba lagi',
                                        'example' => 60,
                                    ],
                                ],
                            ],
                            'errors' => [
                                'type' => 'null',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // Generate tags & nested groups
        foreach ($this->featureGroups as $groupKey => $groupConfig) {
            $tagNames = [];
            foreach ($groupConfig['features'] as $featureKey => $featureConfig) {
                $tagName = $featureConfig['label'];
                $spec['tags'][] = [
                    'name' => $tagName,
                    'description' => $featureConfig['description'],
                    'x-tagGroup' => $groupConfig['label'],
                    'x-tagPath' => "{$groupKey}/{$featureKey}",
                ];
                $tagNames[] = $tagName;
            }

            $spec['x-tagGroups'][] = [
                'name' => $groupConfig['label'],
                'tags' => $tagNames,
                'description' => $groupConfig['description'],
            ];
        }

        // Process routes
        $routes = Route::getRoutes();
        foreach ($routes as $route) {
            if (! $route->getName() || ! str_starts_with($route->uri(), 'api/')) {
                continue;
            }

            $this->processRoute($route, $spec);
        }

        return $spec;
    }

    protected function processRoute($route, array &$spec): void
    {
        $uri = str_replace('api/', '', $route->uri());
        $httpMethod = strtolower($route->methods()[0] ?? 'get');
        $action = $route->getAction();

        if (! isset($action['controller'])) {
            return;
        }

        $controllerParts = explode('@', $action['controller']);
        $controllerClass = $controllerParts[0];
        $controllerMethod = $controllerParts[1] ?? '__invoke';

        if (! class_exists($controllerClass)) {
            return;
        }

        $featureInfo = $this->getFeatureInfo($uri, $controllerClass);
        if (! $featureInfo) {
            return;
        }

        $pathItem = $this->buildPathItem(
            $route,
            $controllerClass,
            $controllerMethod,
            $featureInfo,
            $uri,
        );

        if (! isset($spec['paths']["/{$uri}"])) {
            $spec['paths']["/{$uri}"] = [];
        }

        $spec['paths']["/{$uri}"][$httpMethod] = $pathItem;
    }

    protected function getFeatureInfo(string $uri, string $controllerClass): ?array
    {
        $module = $this->extractModuleFromController($controllerClass);
        $uriLower = strtolower($uri);

        foreach ($this->featureGroups as $groupKey => $groupConfig) {
            // Iterate in reverse to check most specific features first (e.g. Blocks before Course)
            foreach (array_reverse($groupConfig['features'], true) as $featureKey => $featureConfig) {
                if ($this->matchesFeature($module, $uriLower, $featureConfig)) {
                    return [
                        'groupKey' => $groupKey,
                        'groupLabel' => $groupConfig['label'],
                        'featureKey' => $featureKey,
                        'tag' => $featureConfig['label'],
                    ];
                }
            }
        }

        return $this->getFallbackFeature();
    }

    protected function matchesFeature(?string $module, string $uriLower, array $featureConfig): bool
    {
        $moduleMatch =
          empty($featureConfig['modules']) ||
          ($module !== null && in_array($module, $featureConfig['modules'], true));

        if (! $moduleMatch) {
            return false;
        }

        $keywords = $featureConfig['keywords'] ?? [];
        if (empty($keywords)) {
            return true;
        }

        return $this->uriMatchesKeywords($uriLower, $keywords);
    }

    protected function extractModuleFromController(string $controllerClass): ?string
    {
        if (preg_match('/Modules\\\\(.*?)\\\\Http\\\\Controllers/', $controllerClass, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function uriMatchesKeywords(string $uriLower, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if ($keyword === '') {
                continue;
            }
            if (str_contains($uriLower, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    protected function getFallbackFeature(): array
    {
        $groupKey = 'umum';
        $featureKey = 'general';

        $group = $this->featureGroups[$groupKey] ?? [
            'label' => 'Umum',
            'features' => [
                $featureKey => [
                    'label' => 'Endpoint Lainnya',
                ],
            ],
        ];

        $feature = $group['features'][$featureKey] ?? ['label' => 'Endpoint Lainnya'];

        return [
            'groupKey' => $groupKey,
            'groupLabel' => $group['label'],
            'featureKey' => $featureKey,
            'tag' => $feature['label'],
        ];
    }

    protected function buildPathItem(
        $route,
        string $controllerClass,
        string $method,
        array $featureInfo,
        string $uri,
    ): array {
        $reflection = new ReflectionClass($controllerClass);

        try {
            $methodReflection = $reflection->getMethod($method);
        } catch (\ReflectionException $e) {
            // If method doesn't exist, create a dummy reflection
            $methodReflection = null;
        }

        $httpMethod = strtolower($route->methods()[0] ?? 'get');
        $summary = $this->getSummary($methodReflection, $uri, $httpMethod);
        $description = $this->getDescription($methodReflection, $uri, $httpMethod);

        $pathItem = [
            'tags' => [$featureInfo['tag']],
            'summary' => $summary,
            'description' => $description,
            'operationId' => $route->getName() ?:
              Str::camel($httpMethod.'_'.str_replace(['/', '{', '}'], ['_', '', ''], $uri)),
        ];

        // Security
        try {
            $middleware = $route->gatherMiddleware();
            $hasAuth = in_array('auth:api', $middleware) ||
                in_array('auth:sanctum', $middleware) ||
                in_array('auth', $middleware) ||
                in_array('jwt.auth', $middleware) ||
                in_array('jwt.verify', $middleware);

            if ($hasAuth) {
                $pathItem['security'] = [['bearerAuth' => []]];
            }
        } catch (\Throwable $e) {
            // Some controllers may not be instantiable during generation
            // Default to requiring auth for safety
            $pathItem['security'] = [['bearerAuth' => []]];
        }

        // Parameters
        $parameters = $this->extractParameters($uri);

        // Add standard query params for GET list endpoints
        // List endpoints are GET requests that don't have required path parameters (like {id})
        // or are explicitly named 'index', 'list', 'search', etc.
        $isListEndpoint =
          $httpMethod === 'get' &&
          ($method === 'index' ||
            $method === 'list' ||
            $method === 'search' ||
            str_starts_with($method, 'list') ||
            str_starts_with($method, 'search') ||
            str_contains($uri, '/search') ||
            str_contains($uri, '/statistics') ||
            (empty($parameters) && ! str_contains($uri, '{'))); // No path params = likely a list

        if ($isListEndpoint) {
            $parameters[] = [
                'name' => 'page',
                'in' => 'query',
                'description' => 'Nomor halaman (pagination)',
                'schema' => ['type' => 'integer', 'default' => 1],
            ];
            $parameters[] = [
                'name' => 'per_page',
                'in' => 'query',
                'description' => 'Jumlah item per halaman',
                'schema' => ['type' => 'integer', 'default' => 15],
            ];
            $parameters[] = [
                'name' => 'search',
                'in' => 'query',
                'description' => 'Kata kunci pencarian',
                'schema' => ['type' => 'string'],
            ];

            $allowedSorts = $this->getAllowedSorts($reflection, $methodReflection);
            if (! empty($allowedSorts)) {
                $sortDesc = 'Allowed: '.implode(', ', $allowedSorts);
            } else {
                $sortDesc = 'Pengurutan data (contoh: -created_at)';
            }

            $parameters[] = [
                'name' => 'sort',
                'in' => 'query',
                'description' => $sortDesc,
                'schema' => ['type' => 'string'],
            ];

            $allowedFilters = $this->getAllowedFilters($reflection, $methodReflection);

            if (! empty($allowedFilters)) {
                // Explode filters into individual parameters
                foreach ($allowedFilters as $filterKey) {
                    $enumValues = $this->getFilterEnumValues($filterKey, $reflection, $methodReflection);
                    $description = "Filter by {$filterKey}";
                    if ($enumValues) {
                        $description .= " ({$enumValues})";
                    }

                    $parameters[] = [
                        'name' => "filter[{$filterKey}]",
                        'in' => 'query',
                        'description' => $description,
                        'schema' => ['type' => 'string'],
                    ];
                }
            } else {
                // Fallback to generic filter object
                $parameters[] = [
                    'name' => 'filter',
                    'in' => 'query',
                    'description' => 'Filter data (contoh: filter[status]=active)',
                    'style' => 'deepObject',
                    'explode' => true,
                    'schema' => [
                        'type' => 'object',
                        'additionalProperties' => ['type' => 'string'],
                    ],
                ];
            }
        }

        if (! empty($parameters)) {
            $pathItem['parameters'] = $parameters;
        }

        // Request Body
        $requestBody = $this->buildRequestBody($methodReflection, $httpMethod);
        if ($requestBody) {
            $pathItem['requestBody'] = $requestBody;
        }

        // Special handling for refresh token endpoint
        if (str_contains($uri, '/auth/refresh')) {
            $pathItem = $this->enhanceRefreshTokenEndpoint($pathItem, $uri);
        }

        // Responses
        $pathItem['responses'] = $this->buildResponses($httpMethod, $methodReflection, $uri);

        return $pathItem;
    }

    protected function getAllowedSorts(
        ReflectionClass $controllerReflection,
        ?ReflectionMethod $methodReflection,
    ): array {
        // 1. Check docblock
        if ($methodReflection) {
            $docBlock = $methodReflection->getDocComment();
            if ($docBlock && preg_match("/@allowedSorts\s+(.+)/", $docBlock, $matches)) {
                return array_map('trim', explode(',', $matches[1]));
            }
        }

        // 2. Check Repository
        $repoClass = $this->detectRepositoryClass($controllerReflection);
        if ($repoClass) {
            try {
                $repoReflection = new ReflectionClass($repoClass);
                if ($repoReflection->hasProperty('allowedSorts')) {
                    $prop = $repoReflection->getProperty('allowedSorts');
                    $prop->setAccessible(true);

                    return $prop->getDefaultValue();
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        return [];
    }

    protected function getAllowedFilters(
        ReflectionClass $controllerReflection,
        ?ReflectionMethod $methodReflection,
    ): array {
        // 1. Check docblock
        if ($methodReflection) {
            $docBlock = $methodReflection->getDocComment();
            if ($docBlock && preg_match("/@allowedFilters\s+(.+)/", $docBlock, $matches)) {
                return array_map('trim', explode(',', $matches[1]));
            }
        }

        // 2. Check Repository
        $repoClass = $this->detectRepositoryClass($controllerReflection);
        if ($repoClass) {
            try {
                $repoReflection = new ReflectionClass($repoClass);
                if ($repoReflection->hasProperty('allowedFilters')) {
                    $prop = $repoReflection->getProperty('allowedFilters');
                    $prop->setAccessible(true);

                    return $prop->getDefaultValue();
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        return [];
    }

    protected function detectRepositoryClass(ReflectionClass $controllerReflection): ?string
    {
        $constructor = $controllerReflection->getConstructor();
        if (! $constructor) {
            return null;
        }

        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if (! $type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                continue;
            }

            $typeName = $type->getName();
            if (str_ends_with($typeName, 'Repository')) {
                return $typeName;
            }
        }

        return null;
    }

    protected function getFilterEnumValues(
        string $filterKey,
        ReflectionClass $controllerReflection,
        ?ReflectionMethod $methodReflection,
    ): ?string {
        // 1. Check docblock for @filterEnum annotations
        if ($methodReflection) {
            $docBlock = $methodReflection->getDocComment();
            if (
                $docBlock &&
                preg_match(
                    "/@filterEnum\s+".preg_quote($filterKey, '/')."\s+(.+)/",
                    $docBlock,
                    $matches,
                )
            ) {
                return trim($matches[1]);
            }
        }

        // 2. Check Repository for $filterEnums property
        $repoClass = $this->detectRepositoryClass($controllerReflection);
        if ($repoClass) {
            try {
                $repoReflection = new ReflectionClass($repoClass);
                if ($repoReflection->hasProperty('filterEnums')) {
                    $prop = $repoReflection->getProperty('filterEnums');
                    $prop->setAccessible(true);
                    $filterEnums = $prop->getDefaultValue();

                    return $filterEnums[$filterKey] ?? null;
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        return null;
    }

    /**
     * Endpoint-specific summaries for actual documentation
     */
    protected array $summaryOverrides = [
        // Auth Module
        'v1/auth/login' => ['post' => 'Login ke sistem'],
        'v1/auth/register' => ['post' => 'Registrasi akun baru'],
        'v1/auth/logout' => ['post' => 'Logout dari sistem'],
        'v1/auth/refresh' => ['post' => 'Refresh access token'],
        'v1/auth/set-username' => ['post' => 'Set username untuk akun baru'],
        'v1/auth/email/verify' => ['post' => 'Verifikasi email dengan OTP'],
        'v1/auth/email/verify/by-token' => ['post' => 'Verifikasi email dengan magic link'],
        'v1/auth/email/verify/send' => ['post' => 'Kirim ulang email verifikasi'],
        'v1/auth/password/forgot' => ['post' => 'Request reset password'],
        'v1/auth/password/forgot/confirm' => ['post' => 'Konfirmasi kode reset password'],
        'v1/auth/password/reset' => ['post' => 'Reset password dengan token'],
        'v1/auth/google/redirect' => ['get' => 'Redirect ke Google OAuth'],
        'v1/auth/google/callback' => ['get' => 'Callback dari Google OAuth'],
        'v1/auth/instructor' => ['post' => 'Buat akun Instructor baru'],
        'v1/auth/admin' => ['post' => 'Buat akun Admin baru'],
        'v1/auth/super-admin' => ['post' => 'Buat akun Super Admin baru'],
        'v1/auth/credentials/resend' => ['post' => 'Kirim ulang kredensial akun'],
        'v1/auth/users' => ['get' => 'Daftar semua pengguna'],
        'v1/auth/users/{user}' => ['get' => 'Detail pengguna'],
        'v1/auth/users/{user}/status' => ['put' => 'Update status pengguna'],

        // Profile Module
        'v1/profile' => ['get' => 'Lihat profil saya', 'put' => 'Update profil saya'],
        'v1/profile/avatar' => ['post' => 'Upload foto profil', 'delete' => 'Hapus foto profil'],
        'v1/profile/privacy' => ['get' => 'Lihat pengaturan privasi', 'put' => 'Update pengaturan privasi'],
        'v1/profile/activities' => ['get' => 'Riwayat aktivitas saya'],
        'v1/profile/achievements' => ['get' => 'Daftar pencapaian saya'],
        'v1/profile/badges/{badge}/pin' => ['post' => 'Pin badge ke profil'],
        'v1/profile/badges/{badge}/unpin' => ['delete' => 'Unpin badge dari profil'],
        'v1/profile/statistics' => ['get' => 'Statistik pembelajaran saya'],
        'v1/profile/password' => ['put' => 'Ubah password'],
        'v1/profile/account' => ['delete' => 'Nonaktifkan akun'],
        'v1/profile/account/restore' => ['post' => 'Aktifkan kembali akun'],
        'v1/profile/email/verify' => ['post' => 'Verifikasi perubahan email'],
        'v1/profile/email/request' => ['post' => 'Request perubahan email'],
        'v1/users/{user}/profile' => ['get' => 'Lihat profil publik pengguna'],

        // Admin Profile
        'v1/admin/users/{user}/profile' => ['get' => 'Lihat profil pengguna (Admin)', 'put' => 'Update profil pengguna (Admin)'],
        'v1/admin/users/{user}/suspend' => ['post' => 'Suspend akun pengguna'],
        'v1/admin/users/{user}/activate' => ['post' => 'Aktifkan akun pengguna'],
        'v1/admin/users/{user}/audit-logs' => ['get' => 'Log audit aktivitas pengguna'],

        // Courses
        'v1/courses' => ['get' => 'Daftar kursus', 'post' => 'Buat kursus baru'],
        'v1/courses/{course}' => ['get' => 'Detail kursus', 'put' => 'Update kursus', 'delete' => 'Hapus kursus'],
        'v1/courses/{course}/publish' => ['put' => 'Publikasikan kursus'],
        'v1/courses/{course}/unpublish' => ['put' => 'Batalkan publikasi kursus'],
        'v1/courses/{course}/enrollment-key/generate' => ['post' => 'Generate enrollment key baru'],
        'v1/courses/{course}/enrollment-key' => ['put' => 'Update enrollment key', 'delete' => 'Hapus enrollment key'],

        // Units
        'v1/courses/{course}/units' => ['get' => 'Daftar unit dalam kursus', 'post' => 'Buat unit baru'],
        'v1/courses/{course}/units/reorder' => ['put' => 'Ubah urutan unit'],
        'v1/courses/{course}/units/{unit}' => ['get' => 'Detail unit', 'put' => 'Update unit', 'delete' => 'Hapus unit'],
        'v1/courses/{course}/units/{unit}/publish' => ['put' => 'Publikasikan unit'],
        'v1/courses/{course}/units/{unit}/unpublish' => ['put' => 'Batalkan publikasi unit'],

        // Lessons
        'v1/courses/{course}/units/{unit}/lessons' => ['get' => 'Daftar lesson dalam unit', 'post' => 'Buat lesson baru'],
        'v1/courses/{course}/units/{unit}/lessons/{lesson}' => ['get' => 'Detail lesson', 'put' => 'Update lesson', 'delete' => 'Hapus lesson'],
        'v1/courses/{course}/units/{unit}/lessons/{lesson}/publish' => ['put' => 'Publikasikan lesson'],
        'v1/courses/{course}/units/{unit}/lessons/{lesson}/unpublish' => ['put' => 'Batalkan publikasi lesson'],
        'v1/courses/{course}/units/{unit}/lessons/{lesson}/complete' => ['post' => 'Tandai lesson selesai'],

        // Blocks
        'v1/courses/{course}/units/{unit}/lessons/{lesson}/blocks' => ['get' => 'Daftar block dalam lesson', 'post' => 'Buat block baru'],
        'v1/courses/{course}/units/{unit}/lessons/{lesson}/blocks/{block}' => ['get' => 'Detail block', 'put' => 'Update block', 'delete' => 'Hapus block'],

        // Progress
        'v1/courses/{course}/progress' => ['get' => 'Progress kursus saya'],

        // Tags
        'v1/course-tags' => ['get' => 'Daftar tag kursus', 'post' => 'Buat tag baru'],
        'v1/course-tags/{tag}' => ['get' => 'Detail tag', 'put' => 'Update tag', 'delete' => 'Hapus tag'],

        // Enrollments
        'v1/courses/{course}/enrollments' => ['get' => 'Daftar peserta kursus', 'post' => 'Daftar ke kursus'],
        'v1/courses/{course}/enrollment-status' => ['get' => 'Cek status pendaftaran'],
        'v1/courses/{course}/cancel' => ['post' => 'Batalkan pendaftaran'],
        'v1/courses/{course}/withdraw' => ['post' => 'Keluar dari kursus'],
        'v1/enrollments' => ['get' => 'Daftar pendaftaran saya'],
        'v1/courses/enrollments' => ['get' => 'Daftar pendaftaran yang dikelola'],
        'v1/enrollments/{enrollment}/approve' => ['post' => 'Setujui pendaftaran'],
        'v1/enrollments/{enrollment}/decline' => ['post' => 'Tolak pendaftaran'],
        'v1/enrollments/{enrollment}/remove' => ['post' => 'Hapus peserta dari kursus'],

        // Reports
        'v1/courses/{course}/reports/completion-rate' => ['get' => 'Laporan tingkat penyelesaian kursus'],
        'v1/reports/enrollment-funnel' => ['get' => 'Laporan funnel pendaftaran'],
        'v1/courses/{course}/exports/enrollments-csv' => ['get' => 'Export data pendaftaran ke CSV'],

        // Announcements
        'v1/announcements' => ['get' => 'Daftar pengumuman', 'post' => 'Buat pengumuman baru'],
        'v1/announcements/{announcement}' => ['get' => 'Detail pengumuman', 'put' => 'Update pengumuman', 'delete' => 'Hapus pengumuman'],
        'v1/announcements/{announcement}/publish' => ['post' => 'Publikasikan pengumuman'],
        'v1/announcements/{announcement}/schedule' => ['post' => 'Jadwalkan pengumuman'],
        'v1/announcements/{announcement}/read' => ['post' => 'Tandai pengumuman sudah dibaca'],

        // News
        'v1/news' => ['get' => 'Daftar berita', 'post' => 'Buat berita baru'],
        'v1/news/trending' => ['get' => 'Berita trending'],
        'v1/news/{news}' => ['get' => 'Detail berita', 'put' => 'Update berita', 'delete' => 'Hapus berita'],
        'v1/news/{news}/publish' => ['post' => 'Publikasikan berita'],
        'v1/news/{news}/schedule' => ['post' => 'Jadwalkan berita'],

        // Course Announcements
        'v1/courses/{course}/announcements' => ['get' => 'Daftar pengumuman kursus', 'post' => 'Buat pengumuman kursus'],

        // Content Statistics
        'v1/content/statistics' => ['get' => 'Statistik konten'],
        'v1/content/statistics/announcements/{announcement}' => ['get' => 'Statistik pengumuman'],
        'v1/content/statistics/news/{news}' => ['get' => 'Statistik berita'],
        'v1/content/statistics/trending' => ['get' => 'Konten trending'],
        'v1/content/statistics/most-viewed' => ['get' => 'Konten paling banyak dilihat'],
        'v1/content/search' => ['get' => 'Cari konten'],

        // Content Approval
        'v1/content/{type}/{id}/submit' => ['post' => 'Submit konten untuk review'],
        'v1/content/{type}/{id}/approve' => ['post' => 'Setujui konten'],
        'v1/content/{type}/{id}/reject' => ['post' => 'Tolak konten'],
        'v1/content/pending-review' => ['get' => 'Daftar konten menunggu review'],

        // Forum Threads
        'v1/schemes/{scheme}/forum/threads' => ['get' => 'Daftar thread forum', 'post' => 'Buat thread baru'],
        'v1/schemes/{scheme}/forum/threads/search' => ['get' => 'Cari thread forum'],
        'v1/schemes/{scheme}/forum/threads/{thread}' => ['get' => 'Detail thread', 'put' => 'Update thread', 'delete' => 'Hapus thread'],
        'v1/schemes/{scheme}/forum/threads/{thread}/pin' => ['post' => 'Pin thread'],
        'v1/schemes/{scheme}/forum/threads/{thread}/close' => ['post' => 'Tutup thread'],
        'v1/schemes/{scheme}/forum/statistics' => ['get' => 'Statistik forum skema'],
        'v1/schemes/{scheme}/forum/statistics/me' => ['get' => 'Statistik forum saya'],

        // Forum Replies
        'v1/forum/threads/{thread}/replies' => ['post' => 'Balas thread'],
        'v1/forum/replies/{reply}' => ['put' => 'Update balasan', 'delete' => 'Hapus balasan'],
        'v1/forum/replies/{reply}/accept' => ['post' => 'Terima sebagai jawaban terbaik'],

        // Forum Reactions
        'v1/forum/threads/{thread}/reactions' => ['post' => 'Toggle reaksi thread'],
        'v1/forum/replies/{reply}/reactions' => ['post' => 'Toggle reaksi balasan'],

        // Search
        'v1/search/courses' => ['get' => 'Cari kursus'],
        'v1/search/autocomplete' => ['get' => 'Autocomplete pencarian'],
        'v1/search/history' => ['get' => 'Riwayat pencarian', 'delete' => 'Hapus riwayat pencarian'],

        // Notifications
        'v1/notifications' => ['get' => 'Daftar notifikasi'],
        'v1/notifications/{notification}' => ['get' => 'Detail notifikasi', 'put' => 'Update notifikasi', 'delete' => 'Hapus notifikasi'],
        'v1/notification-preferences' => ['get' => 'Preferensi notifikasi', 'put' => 'Update preferensi notifikasi'],
        'v1/notification-preferences/reset' => ['post' => 'Reset preferensi notifikasi'],

        // Categories
        'v1/categories' => ['get' => 'Daftar kategori', 'post' => 'Buat kategori baru'],
        'v1/categories/{category}' => ['get' => 'Detail kategori', 'put' => 'Update kategori', 'delete' => 'Hapus kategori'],

        // Assignments
        'v1/courses/{course}/units/{unit}/lessons/{lesson}/assignments' => ['get' => 'Daftar tugas dalam lesson', 'post' => 'Buat tugas baru'],
        'v1/assignments/{assignment}' => ['get' => 'Detail tugas', 'put' => 'Update tugas', 'delete' => 'Hapus tugas'],
        'v1/assignments/{assignment}/publish' => ['put' => 'Publikasikan tugas'],
        'v1/assignments/{assignment}/unpublish' => ['put' => 'Batalkan publikasi tugas'],

        // Submissions
        'v1/assignments/{assignment}/submissions' => ['get' => 'Daftar pengumpulan tugas', 'post' => 'Kumpulkan tugas'],
        'v1/submissions/{submission}' => ['get' => 'Detail pengumpulan', 'put' => 'Update pengumpulan'],

        // Gamification
        'v1/gamifications' => ['get' => 'Daftar gamifikasi', 'post' => 'Buat gamifikasi baru'],
        'v1/gamifications/{gamification}' => ['get' => 'Detail gamifikasi', 'put' => 'Update gamifikasi', 'delete' => 'Hapus gamifikasi'],
        'v1/gamification/summary' => ['get' => 'Ringkasan gamifikasi user (XP, level, streak, rank)'],
        'v1/gamification/badges' => ['get' => 'Daftar badge yang dimiliki user'],
        'v1/gamification/points-history' => ['get' => 'Riwayat perolehan XP user'],
        'v1/gamification/achievements' => ['get' => 'Pencapaian dan milestone user'],

        // Challenges
        'v1/challenges' => ['get' => 'Daftar challenge aktif'],
        'v1/challenges/my' => ['get' => 'Challenge yang di-assign ke user'],
        'v1/challenges/completed' => ['get' => 'Riwayat challenge yang sudah selesai'],
        'v1/challenges/{challenge}' => ['get' => 'Detail challenge'],
        'v1/challenges/{challenge}/claim' => ['post' => 'Klaim reward challenge yang sudah selesai'],

        // Leaderboards
        'v1/leaderboards' => ['get' => 'Leaderboard global'],
        'v1/leaderboards/my-rank' => ['get' => 'Rank user saat ini dan user sekitarnya'],

        // Grading
        'v1/gradings' => ['get' => 'Daftar penilaian', 'post' => 'Buat penilaian baru'],
        'v1/gradings/{grading}' => ['get' => 'Detail penilaian', 'put' => 'Update penilaian', 'delete' => 'Hapus penilaian'],

        // Operations
        'v1/operations' => ['get' => 'Daftar operasi', 'post' => 'Buat operasi baru'],
        'v1/operations/{operation}' => ['get' => 'Detail operasi', 'put' => 'Update operasi', 'delete' => 'Hapus operasi'],
    ];

    protected array $resourceMap = [
        'auth' => 'Autentikasi',
        'login' => 'Login',
        'register' => 'Registrasi',
        'logout' => 'Logout',
        'me' => 'Profil Saya',
        'users' => 'Pengguna',
        'courses' => 'Kursus',
        'schemes' => 'Skema',
        'enrollments' => 'Pendaftaran',
        'submissions' => 'Pengumpulan Tugas',
        'assignments' => 'Tugas',
        'lessons' => 'Lesson',
        'units' => 'Unit',
        'blocks' => 'Block',
        'media' => 'Media',
        'files' => 'File',
        'notifications' => 'Notifikasi',
        'announcements' => 'Pengumuman',
        'news' => 'Berita',
        'categories' => 'Kategori',
        'tags' => 'Tag',
        'comments' => 'Komentar',
        'replies' => 'Balasan',
        'threads' => 'Thread',
        'topics' => 'Topik',
        'leaderboard' => 'Leaderboard',
        'leaderboards' => 'Leaderboard',
        'challenges' => 'Challenge',
        'gamification' => 'Gamifikasi',
        'points' => 'Poin',
        'badges' => 'Badge',
        'levels' => 'Level',
        'verify' => 'Verifikasi',
        'reset' => 'Reset',
        'change' => 'Ubah',
        'update' => 'Update',
        'delete' => 'Hapus',
        'create' => 'Buat',
        'show' => 'Lihat',
        'index' => 'Daftar',
        'approve' => 'Setujui',
        'reject' => 'Tolak',
        'publish' => 'Publikasi',
        'unpublish' => 'Batalkan Publikasi',
        'grade' => 'Penilaian',
        'feedback' => 'Umpan Balik',
        'answers' => 'Jawaban',
        'questions' => 'Pertanyaan',
        'options' => 'Opsi',
        'exercises' => 'Latihan',
        'attempts' => 'Percobaan',
        'results' => 'Hasil',
        'recommendations' => 'Rekomendasi',
        'schedules' => 'Jadwal',
        'banks' => 'Bank Soal',
        'oauth' => 'OAuth',
        'google' => 'Google',
        'callback' => 'Callback',
        'redirect' => 'Redirect',
        'token' => 'Token',
        'refresh' => 'Refresh',
    ];

    protected function getSummary(?ReflectionMethod $method, string $uri, string $httpMethod): string
    {
        // Priority 0: Check summaryOverrides map first
        $normalizedUri = $this->normalizeUriForLookup($uri);
        if (isset($this->summaryOverrides[$normalizedUri][$httpMethod])) {
            return $this->summaryOverrides[$normalizedUri][$httpMethod];
        }

        if ($method) {
            $docComment = $method->getDocComment();
            if ($docComment && preg_match("/@summary\s+(.+)/", $docComment, $matches)) {
                return trim($matches[1]);
            }
        }

        $resourceName = $this->getResourceName($uri);

        // Special cases for specific actions
        if (in_array(strtolower($resourceName), ['login', 'registrasi', 'logout', 'verifikasi'])) {
            return "Melakukan {$resourceName}";
        }

        $actionMap = [
            'get' => 'Mengambil',
            'post' => 'Membuat',
            'put' => 'Memperbarui',
            'patch' => 'Memperbarui',
            'delete' => 'Menghapus',
        ];

        $action = $actionMap[$httpMethod] ?? 'Mengakses';

        if (str_contains($uri, '{')) {
            return "{$action} detail {$resourceName}";
        }

        if ($httpMethod === 'get') {
            return "Mengambil daftar {$resourceName}";
        }

        return "{$action} {$resourceName}";
    }

    protected function getDescription(
        ?ReflectionMethod $method,
        string $uri,
        string $httpMethod,
    ): string {
        // Priority 1: Check for @description in docblock
        if ($method) {
            $docComment = $method->getDocComment();
            if ($docComment && preg_match("/@description\s+(.+)/s", $docComment, $matches)) {
                $description = trim($matches[1]);
                // Remove "Untuk FE/Mobile", "Untuk UI/UX", "Untuk SA" if present
                $description = preg_replace(
                    '/\s*Untuk\s+(FE\/Mobile|UI\/UX|SA):[^\n]*/i',
                    '',
                    $description,
                );
                // Clean up markdown formatting artifacts from docblock
                $description = preg_replace('/\n\s*\*\s*\n\s*\*/', "\n", $description);
                $description = preg_replace("/\*\*([^*]+)\*\*/", '**$1**', $description);

                return trim($description);
            }
        }

        // Priority 2: Detect special actions from URI
        $uriLower = strtolower($uri);
        $resourceName = $this->getResourceName($uri);

        // Special action patterns
        $specialActions = [
            'login' => 'Melakukan autentikasi pengguna dan mendapatkan access token.',
            'logout' => 'Mengakhiri sesi pengguna dan menghapus token.',
            'register' => 'Mendaftarkan pengguna baru ke dalam sistem.',
            'refresh' => 'Memperbarui access token menggunakan refresh token.',
            'verify' => "Memverifikasi {$resourceName}.",
            'resend' => "Mengirim ulang {$resourceName}.",
            'approve' => "Menyetujui {$resourceName}.",
            'reject' => "Menolak {$resourceName}.",
            'decline' => "Menolak {$resourceName}.",
            'publish' => "Mempublikasikan {$resourceName} agar dapat diakses oleh pengguna.",
            'unpublish' => "Membatalkan publikasi {$resourceName}.",
            'archive' => "Mengarsipkan {$resourceName}.",
            'restore' => "Memulihkan {$resourceName} yang telah dihapus.",
            'remove' => "Menghapus {$resourceName} dari sistem.",
            'assign' => "Menugaskan {$resourceName}.",
            'submit' => "Mengirimkan {$resourceName}.",
            'cancel' => "Membatalkan {$resourceName}.",
            'complete' => "Menyelesaikan {$resourceName}.",
            'start' => "Memulai {$resourceName}.",
            'finish' => "Menyelesaikan {$resourceName}.",
            'join' => "Bergabung ke {$resourceName}.",
            'leave' => "Keluar dari {$resourceName}.",
        ];

        foreach ($specialActions as $action => $description) {
            if (str_contains($uriLower, "/{$action}")) {
                return $description;
            }
        }

        // Priority 3: Generate context-aware description based on HTTP method and pattern
        $hasPathParam = str_contains($uri, '{');

        switch ($httpMethod) {
            case 'get':
                if ($hasPathParam) {
                    return "Mengambil informasi detail dari {$resourceName} berdasarkan ID.";
                } else {
                    return "Mengambil daftar {$resourceName} dengan dukungan pagination, pencarian, sorting, dan filtering.";
                }

            case 'post':
                return "Membuat {$resourceName} baru dengan data yang diberikan.";

            case 'put':
                return "Memperbarui seluruh data {$resourceName} yang sudah ada.";

            case 'patch':
                return "Memperbarui sebagian data {$resourceName} yang sudah ada.";

            case 'delete':
                return "Menghapus {$resourceName} dari sistem secara permanen.";

            default:
                return 'Operasi '.strtoupper($httpMethod)." pada {$resourceName}.";
        }
    }

    protected function getResourceName(string $uri): string
    {
        $parts = explode('/', $uri);
        $resource = end($parts);
        $resource = str_replace(['{', '}', '-'], ['', '', ' '], $resource);

        // Check map for exact match
        if (isset($this->resourceMap[$resource])) {
            return $this->resourceMap[$resource];
        }

        // Check map for individual words if it's a compound name
        $words = explode(' ', $resource);
        $translatedWords = array_map(function ($word) {
            return $this->resourceMap[$word] ?? ucfirst($word);
        }, $words);

        return implode(' ', $translatedWords);
    }

    /**
     * Normalize URI for lookup in override maps
     * Converts actual parameter values to placeholder format
     */
    protected function normalizeUriForLookup(string $uri): string
    {
        // Replace :slug binding with just the parameter name
        $normalized = preg_replace('/\{(\w+):slug\}/', '{$1}', $uri);

        // Ensure consistent format
        return $normalized;
    }

    protected function extractParameters(string $uri): array
    {
        $parameters = [];
        preg_match_all("/\{(\w+):?(\w+)?\}/", $uri, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $paramName = $match[1];
            $paramType = $match[2] ?? 'id';

            $parameters[] = [
                'name' => $paramName,
                'in' => 'path',
                'required' => true,
                'description' => $this->getParameterDescription($paramName, $paramType),
                'schema' => [
                    'type' => $paramType === 'slug' ? 'string' : (is_numeric($paramType) ? 'integer' : 'string'),
                ],
            ];
        }

        return $parameters;
    }

    protected function getParameterDescription(string $name, string $type): string
    {
        $descriptions = [
            'id' => "ID unik dari {$name}",
            'slug' => "Slug dari {$name}",
            'course' => 'Slug atau identifier kursus',
            'unit' => 'Slug atau identifier unit',
            'lesson' => 'Slug atau identifier lesson',
            'assignment' => 'ID assignment',
            'submission' => 'ID submission',
            'exercise' => 'ID exercise',
            'question' => 'ID question',
            'attempt' => 'ID attempt',
            'announcement' => 'ID pengumuman',
            'news' => 'ID atau slug berita',
            'user' => 'ID atau username pengguna',
            'badge' => 'ID badge',
            'scheme' => 'Slug atau identifier skema',
            'type' => 'Tipe konten (announcement, news)',
            'category' => 'ID kategori',
            'tag' => 'ID atau slug tag',
            'thread' => 'ID thread diskusi',
            'comment' => 'ID komentar',
            'reply' => 'ID balasan',
            'block' => 'ID block konten',
            'media' => 'ID media file',
            'notification' => 'ID notifikasi',
        ];

        return $descriptions[$type] ?? ($descriptions[$name] ?? "Parameter {$name}");
    }

    protected function buildRequestBody(?ReflectionMethod $method, string $httpMethod): ?array
    {
        if (! in_array($httpMethod, ['post', 'put', 'patch'])) {
            return null;
        }

        if (! $method) {
            return [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [],
                        ],
                    ],
                ],
            ];
        }

        $parameters = $method->getParameters();
        $requestParam = null;

        foreach ($parameters as $param) {
            $type = $param->getType();
            if (
                $type &&
                class_exists($type->getName()) &&
                is_subclass_of($type->getName(), \Illuminate\Http\Request::class)
            ) {
                $requestParam = $type->getName();
                break;
            }
        }

        if (! $requestParam) {
            return [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [],
                        ],
                    ],
                ],
            ];
        }

        // Try to get validation rules from FormRequest
        if (method_exists($requestParam, 'rules')) {
            try {
                $requestInstance = new $requestParam;
                $rules = $requestInstance->rules();
                $schemaResult = $this->rulesToSchema($rules);
                $schema = $schemaResult['schema'];
                $hasFile = $schemaResult['hasFile'];

                $contentType = $hasFile ? 'multipart/form-data' : 'application/json';

                return [
                    'required' => true,
                    'content' => [
                        $contentType => [
                            'schema' => $schema,
                        ],
                    ],
                ];
            } catch (\Throwable $e) {
                // Some FormRequests may depend on auth user or other runtime context
                // Fall through to default schema
            }
        }

        return [
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                    ],
                ],
            ],
        ];
    }

    protected function rulesToSchema(array $rules): array
    {
        $schema = [
            'type' => 'object',
            'properties' => [],
            'required' => [],
        ];
        $hasFile = false;
        $confirmationFields = [];

        foreach ($rules as $field => $rule) {
            $rulesArray = is_array($rule) ? $rule : explode('|', $rule);
            $fieldSchema = ['type' => 'string'];

            $isRequired = in_array('required', $rulesArray);
            if ($isRequired) {
                $schema['required'][] = $field;
            }

            // Check for 'confirmed' rule - this means we need a {field}_confirmation field
            if (in_array('confirmed', $rulesArray)) {
                $confirmationFields[] = $field;
            }

            if (in_array('integer', $rulesArray) || in_array('numeric', $rulesArray)) {
                $fieldSchema['type'] = 'integer';
            } elseif (in_array('boolean', $rulesArray)) {
                $fieldSchema['type'] = 'boolean';
            } elseif (in_array('array', $rulesArray)) {
                $fieldSchema['type'] = 'array';
            }

            // Check for email format
            if (in_array('email', $rulesArray)) {
                $fieldSchema['format'] = 'email';
            }

            // Check for min/max constraints and enum values
            foreach ($rulesArray as $ruleItem) {
                if (is_string($ruleItem)) {
                    if (preg_match('/^min:(\d+)$/', $ruleItem, $matches)) {
                        if ($fieldSchema['type'] === 'string') {
                            $fieldSchema['minLength'] = (int) $matches[1];
                        } else {
                            $fieldSchema['minimum'] = (int) $matches[1];
                        }
                    }
                    if (preg_match('/^max:(\d+)$/', $ruleItem, $matches)) {
                        if ($fieldSchema['type'] === 'string') {
                            $fieldSchema['maxLength'] = (int) $matches[1];
                        } else {
                            $fieldSchema['maximum'] = (int) $matches[1];
                        }
                    }
                    // Check for regex pattern
                    if (preg_match('/^regex:(.+)$/', $ruleItem, $matches)) {
                        // Convert Laravel regex to OpenAPI pattern (remove delimiters)
                        $pattern = trim($matches[1], '/');
                        $pattern = str_replace(['i', 'm', 's', 'u'], '', $pattern); // Remove flags
                        if (! empty($pattern)) {
                            $fieldSchema['pattern'] = $pattern;
                        }
                    }
                    // Check for unique rule - add to description
                    if (str_starts_with($ruleItem, 'unique:')) {
                        $fieldSchema['description'] = ($fieldSchema['description'] ?? '').' Must be unique.';
                    }
                    // Check for 'in:' rule - enum values
                    if (preg_match('/^in:(.+)$/', $ruleItem, $matches)) {
                        $enumValues = explode(',', $matches[1]);
                        // Try to match with known enum schema
                        $schemaRef = $this->getEnumSchemaRef($enumValues);
                        if ($schemaRef) {
                            // Use $ref to enum schema
                            $fieldSchema = ['$ref' => $schemaRef];
                        } else {
                            // Inline enum values
                            $fieldSchema['enum'] = $enumValues;
                        }
                    }
                }
            }

            // Check for file uploads
            if (
                in_array('file', $rulesArray) ||
                in_array('image', $rulesArray) ||
                $this->hasMimesRule($rulesArray)
            ) {
                $fieldSchema['type'] = 'string';
                $fieldSchema['format'] = 'binary';
                $hasFile = true;

                // Add file constraints to description
                $mimeTypes = $this->extractMimeTypes($rulesArray);
                $maxSize = $this->extractMaxSize($rulesArray);
                $description = [];
                if ($mimeTypes) {
                    $description[] = "Allowed types: {$mimeTypes}";
                }
                if ($maxSize) {
                    $description[] = "Max size: {$maxSize}KB";
                }
                if (! empty($description)) {
                    $fieldSchema['description'] = implode('. ', $description);
                }
            } else {
                // Generate example if not a file
                $example = $this->generateExample($field, $fieldSchema['type']);
                if ($example !== null) {
                    $fieldSchema['example'] = $example;
                }
            }

            if (in_array('nullable', $rulesArray)) {
                $fieldSchema['nullable'] = true;
            }

            $schema['properties'][$field] = $fieldSchema;
        }

        // Add confirmation fields for fields with 'confirmed' rule
        foreach ($confirmationFields as $field) {
            $confirmField = $field.'_confirmation';
            $schema['properties'][$confirmField] = [
                'type' => 'string',
                'description' => "Konfirmasi {$field}. Harus sama dengan field {$field}.",
                'example' => $schema['properties'][$field]['example'] ?? 'password123',
            ];
            // Copy minLength if exists
            if (isset($schema['properties'][$field]['minLength'])) {
                $schema['properties'][$confirmField]['minLength'] = $schema['properties'][$field]['minLength'];
            }
            $schema['required'][] = $confirmField;
        }

        return ['schema' => $schema, 'hasFile' => $hasFile];
    }

    protected function extractMimeTypes(array $rulesArray): ?string
    {
        foreach ($rulesArray as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'mimes:')) {
                return str_replace('mimes:', '', $rule);
            }
        }

        return null;
    }

    protected function extractMaxSize(array $rulesArray): ?string
    {
        foreach ($rulesArray as $rule) {
            if (is_string($rule) && preg_match('/^max:(\d+)$/', $rule, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    protected function generateExample(string $field, string $type): mixed
    {
        if ($type === 'boolean') {
            return true;
        }

        if ($type === 'integer') {
            if (str_contains($field, 'id')) {
                return 1;
            }
            if (str_contains($field, 'age')) {
                return 25;
            }
            if (str_contains($field, 'year')) {
                return 2024;
            }

            return 10;
        }

        if ($type === 'string') {
            if (str_contains($field, 'email')) {
                return 'user@example.com';
            }
            if (str_contains($field, 'password')) {
                return 'password123';
            }
            if (str_contains($field, 'name') || str_contains($field, 'fullname')) {
                return 'John Doe';
            }
            if (str_contains($field, 'username')) {
                return 'johndoe';
            }
            if (str_contains($field, 'phone')) {
                return '081234567890';
            }
            if (str_contains($field, 'address')) {
                return 'Jl. Sudirman No. 1, Jakarta';
            }
            if (str_contains($field, 'date')) {
                return '2024-01-01';
            }
            if (str_contains($field, 'time')) {
                return '12:00:00';
            }
            if (str_contains($field, 'description') || str_contains($field, 'content')) {
                return 'Lorem ipsum dolor sit amet.';
            }
            if (str_contains($field, 'title')) {
                return 'Judul Contoh';
            }
            if (str_contains($field, 'slug')) {
                return 'judul-contoh';
            }
            if (str_contains($field, 'url')) {
                return 'https://example.com';
            }
            if (str_contains($field, 'token')) {
                return 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...';
            }
            if (str_contains($field, 'code')) {
                return 'CODE123';
            }
            if (str_contains($field, 'type')) {
                return 'type_1';
            }
            if (str_contains($field, 'status')) {
                return 'active';
            }
        }

        return null;
    }

    protected function hasMimesRule(array $rules): bool
    {
        foreach ($rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'mimes:')) {
                return true;
            }
        }

        return false;
    }

    protected function buildResponses(
        string $httpMethod,
        ?ReflectionMethod $method,
        string $uri = '',
    ): array {
        $responses = [];

        // Success response
        $statusCode = match ($httpMethod) {
            'post' => 201,
            'delete' => 200,
            default => 200,
        };

        // Determine if this is a list endpoint
        $isListEndpoint = $httpMethod === 'get' && $method && $method->getName() === 'index';

        // Build success response with example
        $successExample = $this->buildSuccessExample($httpMethod, $isListEndpoint, $uri, $method);

        // Use PaginatedResponse schema for list endpoints
        $responseSchema = $isListEndpoint
          ? ['$ref' => '#/components/schemas/PaginatedResponse']
          : ['$ref' => '#/components/schemas/SuccessResponse'];

        $responses[$statusCode] = [
            'description' => $isListEndpoint ? 'Response sukses dengan pagination' : 'Response sukses',
            'content' => [
                'application/json' => [
                    'schema' => $responseSchema,
                    'examples' => [
                        'success' => [
                            'summary' => 'Contoh response sukses',
                            'value' => $successExample,
                        ],
                    ],
                ],
            ],
        ];

        // Error responses with examples
        $responses['400'] = [
            'description' => 'Bad Request - Request tidak valid',
            'content' => [
                'application/json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ErrorResponse',
                    ],
                    'examples' => [
                        'error' => [
                            'summary' => 'Contoh error bad request',
                            'value' => [
                                'success' => false,
                                'message' => 'Request tidak valid',
                                'data' => null,
                                'meta' => null,
                                'errors' => [
                                    'parameter' => ['Parameter yang diberikan tidak sesuai format'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $responses['401'] = [
            'description' => 'Unauthorized - Token tidak valid atau tidak ada',
            'content' => [
                'application/json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ErrorResponse',
                    ],
                    'examples' => [
                        'error' => [
                            'summary' => 'Contoh error unauthorized',
                            'value' => [
                                'success' => false,
                                'message' => 'Token tidak valid atau tidak ada',
                                'data' => null,
                                'meta' => null,
                                'errors' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $responses['403'] = [
            'description' => 'Forbidden - Tidak memiliki akses',
            'content' => [
                'application/json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ErrorResponse',
                    ],
                    'examples' => [
                        'error' => [
                            'summary' => 'Contoh error forbidden',
                            'value' => [
                                'success' => false,
                                'message' => 'Anda tidak memiliki akses untuk melakukan operasi ini',
                                'data' => null,
                                'meta' => null,
                                'errors' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $responses['422'] = [
            'description' => 'Validation Error - Data yang dikirim tidak valid',
            'content' => [
                'application/json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ErrorResponse',
                    ],
                    'examples' => [
                        'error' => [
                            'summary' => 'Contoh validation error',
                            'value' => [
                                'success' => false,
                                'message' => 'Data yang Anda kirim tidak valid. Periksa kembali isian Anda.',
                                'data' => null,
                                'meta' => null,
                                'errors' => [
                                    'name' => ['Field name wajib diisi'],
                                    'email' => ['Format email tidak valid'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $responses['404'] = [
            'description' => 'Not Found - Resource tidak ditemukan',
            'content' => [
                'application/json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ErrorResponse',
                    ],
                    'examples' => [
                        'error' => [
                            'summary' => 'Contoh error not found',
                            'value' => [
                                'success' => false,
                                'message' => 'Resource tidak ditemukan',
                                'data' => null,
                                'meta' => null,
                                'errors' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $responses['429'] = [
            'description' => 'Too Many Requests - Rate limit terlampaui',
            'headers' => [
                'X-RateLimit-Limit' => [
                    'description' => 'Jumlah maksimum request yang diizinkan per periode',
                    'schema' => ['type' => 'integer'],
                ],
                'X-RateLimit-Remaining' => [
                    'description' => 'Jumlah request tersisa dalam periode saat ini',
                    'schema' => ['type' => 'integer'],
                ],
                'Retry-After' => [
                    'description' => 'Waktu dalam detik sebelum dapat mencoba lagi',
                    'schema' => ['type' => 'integer'],
                ],
            ],
            'content' => [
                'application/json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/RateLimitError',
                    ],
                    'examples' => [
                        'error' => [
                            'summary' => 'Contoh error rate limit',
                            'value' => [
                                'success' => false,
                                'message' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
                                'data' => null,
                                'meta' => [
                                    'retry_after' => 60,
                                ],
                                'errors' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $responses['500'] = [
            'description' => 'Internal Server Error - Terjadi kesalahan pada server',
            'content' => [
                'application/json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ErrorResponse',
                    ],
                    'examples' => [
                        'error' => [
                            'summary' => 'Contoh error server',
                            'value' => [
                                'success' => false,
                                'message' => 'Terjadi kesalahan pada server',
                                'data' => null,
                                'meta' => null,
                                'errors' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $responses;
    }

    protected function buildSuccessExample(
        string $httpMethod,
        bool $isListEndpoint,
        ?string $uri = null,
        ?ReflectionMethod $method = null,
    ): array {
        $baseExample = [
            'success' => true,
            'message' => 'Berhasil',
            'data' => null,
            'meta' => null,
            'errors' => null,
        ];

        // Priority 1: Check for @responseExample annotation in docblock
        $docblockExample = $this->parseResponseExampleFromDocblock($method);
        if ($docblockExample !== null) {
            return $docblockExample;
        }

        // Priority 2: URI-based detection for specific endpoints
        if ($uri) {
            $methodName = $method ? $method->getName() : '';

            // Auth endpoints - Register
            if (str_contains($uri, '/auth/register')) {
                $baseExample['message'] = 'Registrasi berhasil. Silakan periksa email Anda untuk verifikasi.';
                $baseExample['data'] = [
                    'user' => [
                        'id' => 1,
                        'name' => 'John Doe',
                        'username' => 'johndoe',
                        'email' => 'user@example.com',
                        'status' => 'pending',
                        'email_verified_at' => null,
                        'created_at' => '2024-01-01T00:00:00.000000Z',
                        'updated_at' => '2024-01-01T00:00:00.000000Z',
                        'roles' => ['Student'],
                    ],
                    'access_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjEsImlhdCI6MTY0MDk2ODAwMCwiZXhwIjoxNjQwOTY4OTAwfQ.example',
                    'refresh_token' => 'abc123def456ghi789jkl012mno345pqr678stu901vwx234yz',
                    'expires_in' => 900,
                    'verification_uuid' => '550e8400-e29b-41d4-a716-446655440000',
                ];

                return $baseExample;
            }

            // Auth endpoints - Login
            if (str_contains($uri, '/auth/login')) {
                $baseExample['message'] = 'Login berhasil.';
                $baseExample['data'] = [
                    'user' => [
                        'id' => 1,
                        'name' => 'John Doe',
                        'username' => 'johndoe',
                        'email' => 'user@example.com',
                        'status' => 'active',
                        'email_verified_at' => '2024-01-01T00:00:00.000000Z',
                        'created_at' => '2024-01-01T00:00:00.000000Z',
                        'updated_at' => '2024-01-01T00:00:00.000000Z',
                        'roles' => ['Student'],
                    ],
                    'access_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjEsImlhdCI6MTY0MDk2ODAwMCwiZXhwIjoxNjQwOTY4OTAwfQ.example',
                    'refresh_token' => 'abc123def456ghi789jkl012mno345pqr678stu901vwx234yz',
                    'expires_in' => 900,
                ];

                return $baseExample;
            }

            // Auth endpoints - Logout
            if (str_contains($uri, '/auth/logout')) {
                $baseExample['message'] = 'Logout berhasil.';
                $baseExample['data'] = [];

                return $baseExample;
            }

            // Auth endpoints - Create Instructor/Admin/SuperAdmin
            if (str_contains($uri, '/auth/instructor') || str_contains($uri, '/auth/admin') || str_contains($uri, '/auth/super-admin')) {
                $role = str_contains($uri, '/instructor') ? 'Instructor' : (str_contains($uri, '/super-admin') ? 'Superadmin' : 'Admin');
                $baseExample['message'] = $role.' berhasil dibuat.';
                $baseExample['data'] = [
                    'user' => [
                        'id' => 1,
                        'name' => 'John Doe',
                        'username' => null,
                        'email' => 'instructor@example.com',
                        'status' => 'pending',
                        'email_verified_at' => null,
                        'created_at' => '2024-01-01T00:00:00.000000Z',
                        'updated_at' => '2024-01-01T00:00:00.000000Z',
                        'roles' => [$role],
                    ],
                ];

                return $baseExample;
            }

            // Auth endpoints - Email verification
            if (str_contains($uri, '/email/verify') && ! str_contains($uri, '/send')) {
                $baseExample['message'] = 'Email Anda berhasil diverifikasi.';
                $baseExample['data'] = [];

                return $baseExample;
            }

            // Auth endpoints - Send email verification
            if (str_contains($uri, '/email/verify/send')) {
                $baseExample['message'] = 'Tautan verifikasi telah dikirim ke email Anda. Berlaku 3 menit dan hanya bisa digunakan sekali.';
                $baseExample['data'] = [
                    'uuid' => '550e8400-e29b-41d4-a716-446655440000',
                ];

                return $baseExample;
            }

            // Auth endpoints - Set username
            if (str_contains($uri, '/set-username')) {
                $baseExample['message'] = 'Username berhasil diatur.';
                $baseExample['data'] = [
                    'user' => [
                        'id' => 1,
                        'name' => 'John Doe',
                        'username' => 'johndoe',
                        'email' => 'user@example.com',
                        'status' => 'active',
                        'email_verified_at' => '2024-01-01T00:00:00.000000Z',
                        'created_at' => '2024-01-01T00:00:00.000000Z',
                        'updated_at' => '2024-01-01T00:00:00.000000Z',
                    ],
                ];

                return $baseExample;
            }

            // Auth endpoints - Password reset
            if (str_contains($uri, '/password/forgot') && ! str_contains($uri, '/confirm')) {
                $baseExample['message'] = 'Link reset password telah dikirim ke email Anda.';
                $baseExample['data'] = [];

                return $baseExample;
            }

            if (str_contains($uri, '/password/forgot/confirm')) {
                $baseExample['message'] = 'Kode verifikasi valid. Silakan reset password Anda.';
                $baseExample['data'] = [
                    'token' => 'reset_token_abc123def456',
                ];

                return $baseExample;
            }

            if (str_contains($uri, '/password/reset')) {
                $baseExample['message'] = 'Password berhasil direset.';
                $baseExample['data'] = [];

                return $baseExample;
            }

            // Google OAuth
            if (str_contains($uri, '/google/redirect')) {
                $baseExample['message'] = 'Redirect to Google OAuth';
                $baseExample['data'] = [
                    'url' => 'https://accounts.google.com/o/oauth2/auth?...',
                ];

                return $baseExample;
            }

            if (str_contains($uri, '/google/callback')) {
                $baseExample['message'] = 'Google OAuth callback';
                $baseExample['data'] = [
                    'redirect_url' => 'http://localhost:3000/auth/callback?access_token=...&refresh_token=...&expires_in=900&provider=google&needs_username=0',
                ];

                return $baseExample;
            }

            // Profile email verify
            if (str_contains($uri, '/profile/email/verify')) {
                $baseExample['message'] = 'Email berhasil diubah dan terverifikasi.';
                $baseExample['data'] = [];

                return $baseExample;
            }

            // Users list (handled by pagination detection, but add fallback)
            if (str_contains($uri, '/auth/users') && $httpMethod === 'get') {
                // This should be caught by isListEndpoint, but add as fallback
                $baseExample['data'] = [
                    [
                        'id' => 1,
                        'name' => 'John Doe',
                        'username' => 'johndoe',
                        'email' => 'user@example.com',
                        'status' => 'active',
                        'roles' => ['Student'],
                        'created_at' => '2024-01-01T00:00:00.000000Z',
                    ],
                ];
                $baseExample['meta'] = [
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => 15,
                        'total' => 100,
                        'last_page' => 7,
                        'from' => 1,
                        'to' => 15,
                        'has_next' => true,
                        'has_prev' => false,
                    ],
                ];

                return $baseExample;
            }
        }

        // Priority 3: Detect data structure from controller method
        $detectedKeys = $this->detectResponseDataStructure($method);

        if (! empty($detectedKeys)) {
            // Build data based on detected keys
            $data = [];
            foreach ($detectedKeys as $key) {
                $data[$key] = $this->generateExampleForKey($key);
            }

            // If only one key and it's _model_, use the data directly
            if (count($detectedKeys) === 1 && $detectedKeys[0] === '_model_') {
                $baseExample['data'] = $data['_model_'];
            } else {
                $baseExample['data'] = $data;
            }

            // Set appropriate message based on HTTP method
            if ($httpMethod === 'post') {
                $baseExample['message'] = 'Data berhasil dibuat';
            } elseif ($httpMethod === 'put' || $httpMethod === 'patch') {
                $baseExample['message'] = 'Data berhasil diperbarui';
            } elseif ($httpMethod === 'delete') {
                $baseExample['message'] = 'Data berhasil dihapus';
            }

            return $baseExample;
        }

        // Priority 3: Special case for refresh token endpoint
        if ($uri && str_contains($uri, '/auth/refresh')) {
            $baseExample['message'] = 'Token akses berhasil diperbarui.';
            $baseExample['data'] = [
                'access_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjEsImlhdCI6MTY0MDk2ODAwMCwiZXhwIjoxNjQwOTY4OTAwfQ.example',
                'refresh_token' => 'new_refresh_token_abc123def456...',
                'expires_in' => 900, // 15 menit dalam detik
            ];

            return $baseExample;
        }

        // Priority 4: Fallback to generic examples based on HTTP method
        if ($httpMethod === 'post') {
            $baseExample['message'] = 'Data berhasil dibuat';
            $baseExample['data'] = [
                'id' => 1,
                'code' => 'RESOURCE-001',
                'title' => 'Resource Title',
                'slug' => 'resource-title',
                'status' => 'active',
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'updated_at' => '2024-01-01T00:00:00.000000Z',
            ];
        } elseif ($httpMethod === 'put' || $httpMethod === 'patch') {
            $baseExample['message'] = 'Data berhasil diperbarui';
            $baseExample['data'] = [
                'id' => 1,
                'code' => 'RESOURCE-001',
                'title' => 'Updated Resource Title',
                'slug' => 'updated-resource-title',
                'status' => 'active',
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'updated_at' => '2024-01-02T00:00:00.000000Z',
            ];
        } elseif ($httpMethod === 'delete') {
            $baseExample['message'] = 'Data berhasil dihapus';
            $baseExample['data'] = [];
        } elseif ($isListEndpoint) {
            // List endpoint with pagination (actual structure from paginateResponse)
            $baseExample['data'] = [
                [
                    'id' => 1,
                    'code' => 'RESOURCE-001',
                    'title' => 'Resource 1',
                    'slug' => 'resource-1',
                    'status' => 'active',
                ],
                [
                    'id' => 2,
                    'code' => 'RESOURCE-002',
                    'title' => 'Resource 2',
                    'slug' => 'resource-2',
                    'status' => 'active',
                ],
            ];
            $baseExample['meta'] = [
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 15,
                    'total' => 100,
                    'last_page' => 7,
                    'from' => 1,
                    'to' => 15,
                    'has_next' => true,
                    'has_prev' => false,
                ],
            ];
        } else {
            // Single resource detail
            $baseExample['data'] = [
                'id' => 1,
                'code' => 'RESOURCE-001',
                'title' => 'Resource Title',
                'slug' => 'resource-title',
                'description' => 'Resource description',
                'status' => 'active',
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'updated_at' => '2024-01-01T00:00:00.000000Z',
            ];
        }

        return $baseExample;
    }

    protected function enhanceRefreshTokenEndpoint(array $pathItem, string $uri): array
    {
        // Add header parameter for X-Refresh-Token (mobile app support)
        if (! isset($pathItem['parameters'])) {
            $pathItem['parameters'] = [];
        }

        $pathItem['parameters'][] = [
            'name' => 'X-Refresh-Token',
            'in' => 'header',
            'required' => false,
            'description' => 'Refresh token untuk mobile app. Alternatif: kirim via cookie atau body.',
            'schema' => [
                'type' => 'string',
                'example' => 'abc123def456ghi789jkl012mno345pqr678stu901vwx234yz',
            ],
        ];

        // Add cookie parameter
        $pathItem['parameters'][] = [
            'name' => 'refresh_token',
            'in' => 'cookie',
            'required' => false,
            'description' => 'Refresh token untuk web app (httpOnly cookie). Alternatif: kirim via header X-Refresh-Token atau body.',
            'schema' => [
                'type' => 'string',
                'example' => 'abc123def456ghi789jkl012mno345pqr678stu901vwx234yz',
            ],
        ];

        // Update request body to show refresh_token is optional
        if (
            isset(
                $pathItem['requestBody']['content']['application/json']['schema']['properties'][
                  'refresh_token'
                ],
            )
        ) {
            $pathItem['requestBody']['content']['application/json']['schema']['properties'][
              'refresh_token'
            ]['description'] =
              'Refresh token (opsional jika dikirim via cookie atau header X-Refresh-Token)';
            // Remove from required if present
            if (isset($pathItem['requestBody']['content']['application/json']['schema']['required'])) {
                $pathItem['requestBody']['content']['application/json']['schema'][
                  'required'
                ] = array_filter(
                    $pathItem['requestBody']['content']['application/json']['schema']['required'],
                    fn ($field) => $field !== 'refresh_token',
                );
            }
        }

        // Clean up description - remove markdown formatting artifacts from docblock
        $description = $pathItem['description'] ?? '';
        // Remove duplicate info if already present from docblock
        $description = preg_replace(
            "/\*\*Durasi Token:\*\*.*?\*\*Response:\*\*.*?\*\//s",
            '',
            $description,
        );
        $description = preg_replace('/\n\s*\*\s*\n\s*\*\s*\*\*Durasi Token:\*\*/s', '', $description);
        $description = trim($description);

        // Add comprehensive description if not already present
        if (! str_contains($description, 'Durasi Token')) {
            $pathItem['description'] =
              $description.
              "\n\n".
              "**Durasi Token:**\n".
              "- Access Token: 15 menit (dapat dikonfigurasi via JWT_TTL)\n".
              "- Refresh Token Idle Expiry: 14 hari (expired jika tidak digunakan selama 14 hari)\n".
              "- Refresh Token Absolute Expiry: 90 hari (expired setelah 90 hari terlepas dari penggunaan)\n\n".
              "**Cara Penggunaan:**\n\n".
              "**Frontend (Web):**\n".
              "- Kirim refresh token via cookie `refresh_token` (httpOnly, secure, sameSite)\n".
              "- Atau via header `X-Refresh-Token`\n".
              "- Atau via body `refresh_token`\n\n".
              "**Mobile App:**\n".
              "- Kirim refresh token via header `X-Refresh-Token` (disarankan)\n".
              "- Atau via body `refresh_token`\n".
              "- Simpan refresh token di secure storage (Keychain/Keystore)\n\n".
              "**Token Rotation:**\n".
              "- Setiap kali refresh, refresh token lama akan di-revoke dan diganti dengan yang baru\n".
              '- Response akan mengembalikan access token baru dan refresh token baru';
        } else {
            $pathItem['description'] = $description;
        }

        return $pathItem;
    }

    /**
     * Parse @responseExample annotation from method docblock
     */
    protected function parseResponseExampleFromDocblock(?ReflectionMethod $method): ?array
    {
        if (! $method) {
            return null;
        }

        $docComment = $method->getDocComment();
        if (! $docComment) {
            return null;
        }

        // Look for @responseExample annotation with JSON
        if (preg_match('/@responseExample\\s+(.+)/s', $docComment, $matches)) {
            $jsonString = trim($matches[1]);
            // Remove trailing docblock markers
            $jsonString = preg_replace('/\\s*\\*\\/.*$/s', '', $jsonString);
            $jsonString = preg_replace('/\\n\\s*\\*\\s*/s', ' ', $jsonString);

            try {
                $decoded = json_decode($jsonString, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            } catch (\Exception $e) {
                // Ignore parsing errors
            }
        }

        return null;
    }

    /**
     * Detect response data structure from controller method source code
     */
    protected function detectResponseDataStructure(?ReflectionMethod $method): array
    {
        if (! $method) {
            return [];
        }

        try {
            $filename = $method->getFileName();
            if (! $filename || ! file_exists($filename)) {
                return [];
            }

            $startLine = $method->getStartLine();
            $endLine = $method->getEndLine();
            $length = $endLine - $startLine + 1;

            $source = file($filename);
            $methodSource = implode('', array_slice($source, $startLine - 1, $length));

            // Detect data keys from return statements
            $dataKeys = [];

            // Pattern 1: $this->success(['key' => ...])
            if (preg_match_all('/\$this->(?:success|created|paginateResponse)\(\s*\[\s*[\'"](\w+)[\'"]\s*=>/', $methodSource, $matches)) {
                $dataKeys = array_merge($dataKeys, $matches[1]);
            }

            // Pattern 2: $this->success($data, ...) where $data = ['key' => ...]
            if (preg_match_all('/\$\w+\s*=\s*\[\s*[\'"](\w+)[\'"]\s*=>/', $methodSource, $matches)) {
                $dataKeys = array_merge($dataKeys, $matches[1]);
            }

            // Pattern 3: return $this->success($user->toArray())
            if (preg_match('/\$this->success\(\s*\$\w+->toArray\(\)/', $methodSource)) {
                // This is a direct model return, use generic user/resource structure
                $dataKeys[] = '_model_';
            }

            return array_unique($dataKeys);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Generate example data for a specific key
     */
    protected function generateExampleForKey(string $key): mixed
    {
        // Special case for direct model returns
        if ($key === '_model_') {
            return [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'user@example.com',
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'updated_at' => '2024-01-01T00:00:00.000000Z',
            ];
        }

        // Authentication related
        if ($key === 'access_token') {
            return 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjEsImlhdCI6MTY0MDk2ODAwMCwiZXhwIjoxNjQwOTY4OTAwfQ.example';
        }
        if ($key === 'refresh_token') {
            return 'abc123def456ghi789jkl012mno345pqr678stu901vwx234yz';
        }
        if ($key === 'expires_in') {
            return 900;
        }
        if ($key === 'token') {
            return 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...';
        }

        // User related
        if (in_array($key, ['user', 'instructor', 'admin', 'student'])) {
            return [
                'id' => 1,
                'name' => 'John Doe',
                'username' => 'johndoe',
                'email' => 'user@example.com',
                'status' => 'active',
                'email_verified_at' => '2024-01-01T00:00:00.000000Z',
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'updated_at' => '2024-01-01T00:00:00.000000Z',
            ];
        }

        // Submission related
        if (in_array($key, ['submission', 'submissions'])) {
            $single = [
                'id' => 1,
                'assignment_id' => 1,
                'user_id' => 1,
                'answer_text' => 'Jawaban submission',
                'status' => 'submitted',
                'score' => null,
                'feedback' => null,
                'submitted_at' => '2024-01-01T00:00:00.000000Z',
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'updated_at' => '2024-01-01T00:00:00.000000Z',
            ];

            return $key === 'submissions' ? [$single] : $single;
        }

        // Assignment related
        if (in_array($key, ['assignment', 'assignments'])) {
            $single = [
                'id' => 1,
                'title' => 'Assignment Title',
                'description' => 'Assignment description',
                'type' => 'essay',
                'due_date' => '2024-12-31T23:59:59.000000Z',
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'updated_at' => '2024-01-01T00:00:00.000000Z',
            ];

            return $key === 'assignments' ? [$single] : $single;
        }

        // Course/Scheme related
        if (in_array($key, ['course', 'courses', 'scheme', 'schemes'])) {
            $single = [
                'id' => 1,
                'code' => 'COURSE-001',
                'title' => 'Course Title',
                'slug' => 'course-title',
                'description' => 'Course description',
                'status' => 'published',
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'updated_at' => '2024-01-01T00:00:00.000000Z',
            ];

            return in_array($key, ['courses', 'schemes']) ? [$single] : $single;
        }

        // Enrollment related
        if (in_array($key, ['enrollment', 'enrollments'])) {
            $single = [
                'id' => 1,
                'user_id' => 1,
                'course_id' => 1,
                'status' => 'active',
                'enrolled_at' => '2024-01-01T00:00:00.000000Z',
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'updated_at' => '2024-01-01T00:00:00.000000Z',
            ];

            return $key === 'enrollments' ? [$single] : $single;
        }

        // UUID/Token related
        if ($key === 'uuid') {
            return '550e8400-e29b-41d4-a716-446655440000';
        }

        // Items (generic list)
        if ($key === 'items') {
            return [
                [
                    'id' => 1,
                    'title' => 'Item 1',
                    'status' => 'active',
                ],
                [
                    'id' => 2,
                    'title' => 'Item 2',
                    'status' => 'active',
                ],
            ];
        }

        // Meta (pagination)
        if ($key === 'meta') {
            return [
                'current_page' => 1,
                'per_page' => 15,
                'total' => 100,
                'last_page' => 7,
                'from' => 1,
                'to' => 15,
                'has_more' => true,
            ];
        }

        // Generic fallback
        return [
            'id' => 1,
            'name' => ucfirst($key),
            'created_at' => '2024-01-01T00:00:00.000000Z',
            'updated_at' => '2024-01-01T00:00:00.000000Z',
        ];
    }

    /**
     * Convert PHP Enum class to OpenAPI schema
     */
    protected function enumToSchema(string $enumClass, string $description): array
    {
        $values = [];
        $labels = [];

        foreach ($enumClass::cases() as $case) {
            $values[] = $case->value;
            $labels[] = "{$case->value}: {$case->label()}";
        }

        return [
            'type' => 'string',
            'enum' => $values,
            'description' => $description.'. Nilai yang tersedia: '.implode(', ', $labels),
        ];
    }

    /**
     * Mapping of enum values to schema references
     * Used to detect enum types from validation rules
     */
    protected function getEnumMapping(): array
    {
        return [
            // Auth
            \Modules\Auth\Enums\UserStatus::class => 'UserStatus',

            // Schemes
            \Modules\Schemes\Enums\CourseStatus::class => 'CourseStatus',
            \Modules\Schemes\Enums\CourseType::class => 'CourseType',
            \Modules\Schemes\Enums\EnrollmentType::class => 'EnrollmentType',
            \Modules\Schemes\Enums\LevelTag::class => 'LevelTag',
            \Modules\Schemes\Enums\ProgressionMode::class => 'ProgressionMode',
            \Modules\Schemes\Enums\ContentType::class => 'ContentType',

            // Enrollments
            \Modules\Enrollments\Enums\EnrollmentStatus::class => 'EnrollmentStatus',
            \Modules\Enrollments\Enums\ProgressStatus::class => 'ProgressStatus',

            // Learning
            \Modules\Learning\Enums\AssignmentStatus::class => 'AssignmentStatus',
            \Modules\Learning\Enums\SubmissionStatus::class => 'SubmissionStatus',
            \Modules\Learning\Enums\SubmissionType::class => 'SubmissionType',

            // Content
            \Modules\Content\Enums\ContentStatus::class => 'ContentStatus',
            \Modules\Content\Enums\Priority::class => 'Priority',
            \Modules\Content\Enums\TargetType::class => 'TargetType',

            // Gamification
            \Modules\Gamification\Enums\ChallengeType::class => 'ChallengeType',
            \Modules\Gamification\Enums\ChallengeAssignmentStatus::class => 'ChallengeAssignmentStatus',
            \Modules\Gamification\Enums\ChallengeCriteriaType::class => 'ChallengeCriteriaType',
            \Modules\Gamification\Enums\BadgeType::class => 'BadgeType',
            \Modules\Gamification\Enums\PointSourceType::class => 'PointSourceType',
            \Modules\Gamification\Enums\PointReason::class => 'PointReason',

            // Notifications
            \Modules\Notifications\Enums\NotificationType::class => 'NotificationType',
            \Modules\Notifications\Enums\NotificationChannel::class => 'NotificationChannel',
            \Modules\Notifications\Enums\NotificationFrequency::class => 'NotificationFrequency',

            // Grading
            \Modules\Grading\Enums\GradeStatus::class => 'GradeStatus',
            \Modules\Grading\Enums\SourceType::class => 'GradeSourceType',

            // Common
            \Modules\Common\Enums\CategoryStatus::class => 'CategoryStatus',
        ];
    }

    /**
     * Get enum schema reference from validation rule values
     */
    protected function getEnumSchemaRef(array $enumValues): ?string
    {
        $enumMapping = $this->getEnumMapping();

        foreach ($enumMapping as $enumClass => $schemaName) {
            $classValues = $enumClass::values();
            sort($classValues);
            sort($enumValues);

            if ($classValues === $enumValues) {
                return '#/components/schemas/'.$schemaName;
            }
        }

        return null;
    }
}
