<?php

declare(strict_types=1);

namespace App\Support;

final class RealisticSeederContent
{
    public const EMAIL_DOMAIN_DEMO = 'demo.levl.id';

    public static function demoEmail(string $localPart): string
    {
        return $localPart.'@'.self::EMAIL_DOMAIN_DEMO;
    }

    public static function indonesianNamePair(int $index): array
    {
        $first = [
            'Ahmad', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fitri', 'Gita', 'Hadi', 'Indah', 'Joko',
            'Kartika', 'Lina', 'Made', 'Nina', 'Oki', 'Putri', 'Rudi', 'Sari', 'Tono', 'Udin',
            'Vera', 'Wawan', 'Yani', 'Zaki', 'Agus', 'Bayu', 'Dian', 'Eka', 'Fajar', 'Hendra',
            'Ika', 'Jaya', 'Kurnia', 'Lukman', 'Maya', 'Nadia', 'Omar', 'Puji', 'Rina', 'Sigit',
        ];
        $last = [
            'Wijaya', 'Santoso', 'Kusuma', 'Pratama', 'Mahendra', 'Permana', 'Gunawan', 'Saputra',
            'Lestari', 'Handayani', 'Sari', 'Wibowo', 'Hidayat', 'Ramadhan', 'Setiawan', 'Nugroho',
            'Purnomo', 'Simanjuntak', 'Siregar', 'Tampubolon', 'Nasution', 'Manurung', 'Siregar',
            'Panjaitan', 'Silitonga', 'Tarigan', 'Sembiring', 'Sinaga', 'Purba', 'Hutapea',
            'Lumban', 'Siahaan', 'Pangaribuan', 'Situmorang', 'Tanjung', 'Marbun', 'Doloksaribu',
            'Hutagalung', 'Sibarani', 'Pasaribu',
        ];

        $fi = $index % count($first);
        $li = intdiv($index, count($first)) % count($last);

        return [$first[$fi], $last[$li]];
    }

    public static function bioForUser(int $userId): string
    {
        $bios = [
            'Peserta program sertifikasi kompetensi, fokus pada persiapan uji kompetensi LSP.',
            'Instruktur bersertifikat dengan pengalaman pelatihan di bidang teknologi informasi.',
            'Administrator platform pembelajaran, mendukung operasional kursus dan peserta.',
            'Profesional yang sedang meningkatkan kompetensi melalui jalur sertifikasi resmi.',
            'Aktif mengikuti materi asynchronous dan diskusi forum kelompok.',
            'Berkeinginan menyelesaikan seluruh unit kompetensi sesuai skema terdaftar.',
        ];

        return $bios[$userId % count($bios)];
    }

    public static function phoneForIndex(int $index): string
    {
        $n = 8100000000 + ($index % 899999999);

        return '+62'.(string) $n;
    }

    public static function assignmentFeedback(int $i): string
    {
        $pool = [
            'Pengerjaan sesuai rubrik. Perbaiki bagian kesimpulan agar lebih spesifik.',
            'Struktur jawaban baik. Tambahkan referensi ke modul unit sebelumnya.',
            'Nilai memenuhi KKM. Pertahankan format penulisan seperti ini.',
            'Perlu penguatan pada analisis studi kasus. Lihat contoh di materi ajar.',
            'Tepat waktu dan lengkap. Silakan lanjut ke tugas berikutnya.',
        ];

        return $pool[$i % count($pool)];
    }

    public static function gradingReviewReason(int $i): string
    {
        $pool = [
            'Mengajukan peninjauan karena selisih interpretasi rubrik penilaian.',
            'Meminta klarifikasi atas komentar asesor pada bagian teknis.',
            'Terdapat koreksi data identitas pada lembar penilaian.',
        ];

        return $pool[$i % count($pool)];
    }

    public static function gradingReviewResponse(int $i): string
    {
        $pool = [
            'Penilaian telah ditinjau ulang dan disesuaikan dengan rubrik terbaru.',
            'Permohonan diterima; tim asesor akan menghubungi melalui notifikasi.',
            'Data telah diperbaiki sesuai berkas yang diunggah ulang.',
        ];

        return $pool[$i % count($pool)];
    }

    public static function shortSentence(int $i): string
    {
        $pool = [
            'Model ini mendukung skalabilitas layanan pembelajaran jarak jauh.',
            'Integrasi antar modul mengikuti standar keamanan data pendidikan.',
            'Pendekatan ini selaras dengan pedoman penyusunan soal pilihan ganda.',
            'Implementasi mengacu pada dokumen acuan kurikulum nasional terkait.',
            'Evaluasi formatif membantu peserta memantau penguasaan kompetensi.',
        ];

        return $pool[$i % count($pool)];
    }

    public static function paragraph(int $i): string
    {
        $a = self::shortSentence($i);
        $b = self::shortSentence($i + 1);
        $c = self::shortSentence($i + 2);

        return $a.' '.$b.' '.$c;
    }

    public static function assessmentSentence(int $i): string
    {
        return self::shortSentence($i).' '.self::shortSentence($i + 17);
    }

    public static function wordToken(int $i): string
    {
        $pool = [
            'kompetensi', 'sertifikasi', 'asesmen', 'kurikulum', 'instruktur', 'peserta', 'modul',
            'unit', 'rubrik', 'penilaian', 'uji', 'tugas', 'forum', 'diskusi', 'materi', 'video',
            'dokumen', 'laporan', 'studi', 'kasus', 'prosedur', 'standar', 'industri', 'praktik',
            'teori', 'latihan', 'evaluasi', 'formatif', 'sumatif', 'referensi', 'bibliografi',
        ];

        return $pool[$i % count($pool)];
    }

    public static function stableUuid(int $index): string
    {
        $h = md5('levl-seed-uuid-'.$index);

        return substr($h, 0, 8)
            .'-'.substr($h, 8, 4)
            .'-4'.substr($h, 13, 3)
            .'-a'.substr($h, 16, 3)
            .'-'.substr($h, 20, 12);
    }

    public static function ipv4ForIndex(int $index): string
    {
        $i = $index & 0xFFFFFFFF;

        return sprintf(
            '%d.%d.%d.%d',
            10 + ($i % 200),
            ($i >> 8) & 0xFF,
            ($i >> 16) & 0xFF,
            ($i >> 24) & 0xFF
        );
    }

    public static function userAgentForIndex(int $index): string
    {
        $pool = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) Gecko/20100101 Firefox/124.0',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.6099.230 Mobile Safari/537.36',
        ];

        return $pool[$index % count($pool)];
    }

    public static function accountDeletionReason(int $i): string
    {
        $pool = [
            'Tidak lagi membutuhkan akun untuk program saat ini.',
            'Ingin mengurangi jejak data pribadi di platform.',
            'Berpindah ke lembaga pelatihan lain untuk sertifikasi.',
            'Terlalu banyak notifikasi email dari sistem.',
        ];

        return $pool[$i % count($pool)];
    }

    public static function pendingEmailChange(int $userId): string
    {
        return 'ganti.email.'.$userId.'@peserta.'.self::EMAIL_DOMAIN_DEMO;
    }

    public static function activityLogTitle(int $i): string
    {
        return self::shortSentence($i);
    }

    public static function lessonMarkdownSection(int $i): string
    {
        $sections = [
            "## Ringkasan\n\nMateri ini menjembatani teori dan praktik sesuai unit kompetensi.\n\n",
            "## Tujuan Pembelajaran\n\nSetelah mempelajari modul, peserta mampu menerapkan prosedur standar.\n\n",
            "## Aktivitas\n\nKerjakan latihan mandiri dan unggah bukti sesuai instruksi asesor.\n\n",
        ];

        return implode('', array_map(fn ($j) => $sections[$j % count($sections)], range(0, min(2, $i % 3))));
    }
}
