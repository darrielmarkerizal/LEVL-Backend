<?php

declare(strict_types=1);

namespace Modules\Schemes\Database\Factories;

use App\Support\SeederDate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Schemes\Enums\CourseStatus;
use Modules\Schemes\Enums\CourseType;
use Modules\Schemes\Enums\EnrollmentType;
use Modules\Schemes\Models\Course;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    private static int $sequence = 0;

    public function definition(): array
    {
        self::$sequence++;
        $idx = self::$sequence;

        $courseTitles = [
            'Pelatihan Kompetensi Jaringan Dasar',
            'Sertifikasi Administrasi Perkantoran',
            'Pengelolaan Dokumen dan Arsip Digital',
            'Keselamatan dan Kesehatan Kerja (K3)',
            'Komunikasi Bisnis dan Layanan Prima',
            'Pemrograman Aplikasi untuk Uji Kompetensi',
            'Manajemen Proyek Sesuai Standar Industri',
            'Analisis Data untuk Pengambilan Keputusan',
            'Pemasaran Digital untuk UMKM',
            'Desain Grafis untuk Media Pembelajaran',
            'Keamanan Informasi pada Lingkungan Kerja',
            'Akuntansi Dasar untuk Supervisor',
            'Pengawasan Mutu Produk',
            'Operasional Gudang dan Logistik',
            'Perawatan Mesin Produksi',
            'Pelayanan Pelanggan Berbasis SOP',
            'Kepemimpinan Tim Lapangan',
            'Instruksi Kerja dan Prosedur Baku',
            'Pengendalian Biaya Operasional',
            'Literasi Digital untuk Instruktur',
        ];

        $title = $courseTitles[$idx % count($courseTitles)];
        $code = 'CRS'.str_pad((string) ($idx % 10000), 4, '0', STR_PAD_LEFT);
        $slug = Str::slug($title).'-'.$idx;

        $descriptions = [
            'Kurikulum disusun mengacu pada unit kompetensi skema terdaftar dan kebutuhan industri.',
            'Materi mencakup teori ringkas, demonstrasi, dan latihan yang dapat dibuktikan untuk asesmen.',
            'Setiap modul memiliki capaian yang terukur dan alur belajar yang konsisten.',
            'Pendekatan pembelajaran mengutamakan prosedur standar dan keselamatan kerja.',
            'Studi kasus disesuaikan dengan konteks organisasi peserta dan regulasi terkini.',
        ];

        $statusCycle = [CourseStatus::Published->value, CourseStatus::Draft->value];

        return [
            'code' => $code,
            'title' => $title,
            'slug' => $slug,
            'short_desc' => $descriptions[$idx % count($descriptions)],
            'type' => [CourseType::Okupasi->value, CourseType::Kluster->value][$idx % 2],
            'level_tag' => ['dasar', 'menengah', 'mahir'][$idx % 3],
            'enrollment_type' => [
                EnrollmentType::AutoAccept->value,
                EnrollmentType::KeyBased->value,
                EnrollmentType::Approval->value,
            ][$idx % 3],
            'status' => $statusCycle[$idx % 2],
            'published_at' => ($idx % 5 !== 0) ? SeederDate::randomPastDateTimeBetween(1, 180) : null,
        ];
    }

    public function published(): static
    {
        return $this->state(function (array $attributes) {
            $seed = crc32($attributes['code'] ?? 'course');

            return [
                'status' => CourseStatus::Published->value,
                'published_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            ];
        });
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CourseStatus::Draft->value,
            'published_at' => null,
        ]);
    }

    public function openEnrollment(): static
    {
        return $this->state(fn (array $attributes) => [
            'enrollment_type' => EnrollmentType::AutoAccept->value,
        ]);
    }

    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CourseType::Okupasi->value,
        ]);
    }

    public function hybrid(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CourseType::Kluster->value,
        ]);
    }

    public function beginner(): static
    {
        return $this->state(fn (array $attributes) => [
            'level_tag' => 'dasar',
        ]);
    }

    public function intermediate(): static
    {
        return $this->state(fn (array $attributes) => [
            'level_tag' => 'menengah',
        ]);
    }

    public function advanced(): static
    {
        return $this->state(fn (array $attributes) => [
            'level_tag' => 'mahir',
        ]);
    }
}
