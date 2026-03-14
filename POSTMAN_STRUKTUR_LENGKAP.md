# RANCANGAN STRUKTUR POSTMAN LENGKAP - LEVL API
**Versi**: 2.0  
**Tanggal**: 15 Maret 2026  
**Tujuan**: Struktur Postman multiplatform yang mudah dikelola BE dan dipahami FE/Mobile Developer

---

## 🎯 TUJUAN & PRINSIP DESAIN

### Tujuan Utama
1. **Untuk Backend Developer**: Mudah maintain, update, dan test API
2. **Untuk Frontend Developer**: Mudah menemukan endpoint yang dibutuhkan untuk web
3. **Untuk Mobile Developer**: Mudah menemukan endpoint yang dibutuhkan untuk mobile app
4. **Konsistensi**: Penamaan dan struktur yang konsisten di semua platform

### Prinsip Desain
- **Platform-First Organization**: Pisahkan berdasarkan platform (Mobile, Admin, Instruktur)
- **Module-Based Grouping**: Kelompokkan berdasarkan modul/fitur
- **Consistent Naming**: Gunakan format penamaan yang konsisten berbahasa Indonesia
- **Complete Documentation**: Setiap request memiliki dokumentasi lengkap
- **Easy Navigation**: Struktur folder yang intuitif dengan numbering
- **Reusable Variables**: Gunakan environment variables untuk fleksibilitas

---

## 📁 STRUKTUR FOLDER UTAMA

```
Levl API Collection
├── 📱 [1] MOBILE - Aplikasi Student
├── 💻 [2] WEB ADMIN - Dashboard Admin
├── 🎓 [3] WEB INSTRUKTUR - Dashboard Instruktur
├── 🌐 [4] SHARED - API Bersama
└── 📚 [5] DOKUMENTASI & REFERENSI
```

---

## 📱 [1] MOBILE - APLIKASI STUDENT

### Struktur Lengkap
```
📱 [1] MOBILE - Aplikasi Student
│
├── 🔐 1.1 Autentikasi
│   ├── POST [Mobile] Autentikasi - Login
│   ├── POST [Mobile] Autentikasi - Daftar Akun Baru
│   ├── POST [Mobile] Autentikasi - Logout
│   ├── POST [Mobile] Autentikasi - Refresh Token
│   ├── POST [Mobile] Autentikasi - Lupa Password
│   ├── POST [Mobile] Autentikasi - Reset Password
│   ├── POST [Mobile] Autentikasi - Verifikasi Email
│   ├── POST [Mobile] Autentikasi - Kirim Ulang Verifikasi
│   └── GET  [Mobile] Autentikasi - Data User Saat Ini
│
├── 📚 1.2 Pembelajaran
│   │
│   ├── 📖 1.2.1 Kursus
│   │   ├── GET [Mobile] Kursus - Daftar Kursus Saya
│   │   ├── GET [Mobile] Kursus - Detail Kursus
│   │   ├── GET [Mobile] Kursus - Progress Kursus
│   │   └── GET [Mobile] Kursus - Struktur Konten Kursus
│   │
│   ├── 📑 1.2.2 Unit Pembelajaran
│   │   ├── GET [Mobile] Unit - Daftar Unit dalam Kursus
│   │   ├── GET [Mobile] Unit - Detail Unit
│   │   └── GET [Mobile] Unit - Progress Unit
│   │
│   ├── 📝 1.2.3 Materi Pelajaran
│   │   ├── GET  [Mobile] Materi - Daftar Materi dalam Unit
│   │   ├── GET  [Mobile] Materi - Detail Materi
│   │   ├── GET  [Mobile] Materi - Konten Materi
│   │   ├── POST [Mobile] Materi - Tandai Selesai
│   │   ├── POST [Mobile] Materi - Catat Progress
│   │   └── GET  [Mobile] Materi - Materi Selanjutnya
│   │
│   ├── 📋 1.2.4 Tugas
│   │   ├── GET  [Mobile] Tugas - Daftar Tugas Saya
│   │   ├── GET  [Mobile] Tugas - Detail Tugas
│   │   ├── POST [Mobile] Tugas - Submit Tugas
│   │   ├── GET  [Mobile] Tugas - Daftar Submission Saya
│   │   ├── GET  [Mobile] Tugas - Detail Submission
│   │   └── POST [Mobile] Tugas - Upload File
│   │
│   └── ❓ 1.2.5 Kuis
│       ├── GET  [Mobile] Kuis - Daftar Kuis Saya
│       ├── GET  [Mobile] Kuis - Detail Kuis
│       ├── POST [Mobile] Kuis - Mulai Attempt
│       ├── POST [Mobile] Kuis - Submit Jawaban
│       ├── POST [Mobile] Kuis - Submit Kuis
│       ├── GET  [Mobile] Kuis - Hasil Kuis
│       └── GET  [Mobile] Kuis - Riwayat Attempt Saya
│
├── 🎮 1.3 Gamifikasi
│   │
│   ├── 📊 1.3.1 Statistik & Progress
│   │   ├── GET [Mobile] Gamifikasi - Statistik Saya
│   │   ├── GET [Mobile] Gamifikasi - Level Saya
│   │   ├── GET [Mobile] Gamifikasi - Statistik XP Harian
│   │   ├── GET [Mobile] Gamifikasi - Ringkasan
│   │   └── GET [Mobile] Gamifikasi - Milestone
│   │
│   ├── 🏅 1.3.2 Lencana
│   │   ├── GET [Mobile] Lencana - Lencana Saya
│   │   ├── GET [Mobile] Lencana - Detail Lencana
│   │   └── GET [Mobile] Lencana - Lencana Tersedia
│   │
│   ├── 🏆 1.3.3 Leaderboard
│   │   ├── GET [Mobile] Leaderboard - Peringkat Global
│   │   ├── GET [Mobile] Leaderboard - Peringkat Kursus
│   │   ├── GET [Mobile] Leaderboard - Peringkat Saya
│   │   └── GET [Mobile] Leaderboard - Pengguna Sekitar Saya
│   │
│   └── ⭐ 1.3.4 XP & Poin
│       ├── GET [Mobile] XP - Riwayat XP
│       ├── GET [Mobile] XP - Riwayat Poin
│       └── GET [Mobile] XP - Sumber XP
│
├── 💬 1.4 Forum Diskusi
│   │
│   ├── 📌 1.4.1 Thread
│   │   ├── GET    [Mobile] Forum - Daftar Thread
│   │   ├── POST   [Mobile] Forum - Buat Thread Baru
│   │   ├── GET    [Mobile] Forum - Detail Thread
│   │   ├── PUT    [Mobile] Forum - Update Thread
│   │   ├── DELETE [Mobile] Forum - Hapus Thread
│   │   └── GET    [Mobile] Forum - Thread Saya
│   │
│   ├── 💭 1.4.2 Balasan
│   │   ├── GET    [Mobile] Forum - Daftar Balasan
│   │   ├── POST   [Mobile] Forum - Balas Thread
│   │   ├── PUT    [Mobile] Forum - Update Balasan
│   │   └── DELETE [Mobile] Forum - Hapus Balasan
│   │
│   └── ❤️ 1.4.3 Reaksi
│       ├── POST   [Mobile] Forum - Beri Reaksi pada Thread
│       ├── POST   [Mobile] Forum - Beri Reaksi pada Balasan
│       └── DELETE [Mobile] Forum - Hapus Reaksi
│
├── 📊 1.5 Dashboard
│   ├── GET [Mobile] Dashboard - Ringkasan
│   ├── GET [Mobile] Dashboard - Aktivitas Terkini
│   ├── GET [Mobile] Dashboard - Ringkasan Progress
│   ├── GET [Mobile] Dashboard - Deadline Mendatang
│   └── GET [Mobile] Dashboard - Pencapaian Terkini
│
└── 👤 1.6 Profil
    ├── GET    [Mobile] Profil - Data Profil Saya
    ├── PUT    [Mobile] Profil - Update Profil
    ├── POST   [Mobile] Profil - Upload Avatar
    ├── PUT    [Mobile] Profil - Ganti Password
    ├── GET    [Mobile] Profil - Kursus Saya
    └── GET    [Mobile] Profil - Sertifikat Saya
```


---

## 💻 [2] WEB ADMIN - DASHBOARD ADMIN

### Struktur Lengkap
```
💻 [2] WEB ADMIN - Dashboard Admin
│
├── 🔐 2.1 Autentikasi
│   ├── POST [Admin] Autentikasi - Login
│   ├── POST [Admin] Autentikasi - Logout
│   ├── POST [Admin] Autentikasi - Refresh Token
│   └── GET  [Admin] Autentikasi - Data User Saat Ini
│
├── 👥 2.2 Manajemen Pengguna
│   │
│   ├── 👤 2.2.1 Pengguna Umum
│   │   ├── GET    [Admin] Pengguna - Daftar Semua Pengguna
│   │   ├── GET    [Admin] Pengguna - Detail Pengguna
│   │   ├── POST   [Admin] Pengguna - Tambah Pengguna
│   │   ├── PUT    [Admin] Pengguna - Update Pengguna
│   │   ├── DELETE [Admin] Pengguna - Hapus Pengguna
│   │   ├── POST   [Admin] Pengguna - Import Bulk
│   │   └── POST   [Admin] Pengguna - Export Data
│   │
│   ├── 🎓 2.2.2 Partisipan (Student)
│   │   ├── GET    [Admin] Partisipan - Daftar Partisipan
│   │   ├── POST   [Admin] Partisipan - Tambah Partisipan
│   │   ├── GET    [Admin] Partisipan - Detail Partisipan
│   │   ├── PUT    [Admin] Partisipan - Update Partisipan
│   │   ├── DELETE [Admin] Partisipan - Hapus Partisipan
│   │   ├── GET    [Admin] Partisipan - Progress Partisipan
│   │   └── GET    [Admin] Partisipan - Pendaftaran Partisipan
│   │
│   ├── 👨‍🏫 2.2.3 Instruktur
│   │   ├── GET    [Admin] Instruktur - Daftar Instruktur
│   │   ├── POST   [Admin] Instruktur - Tambah Instruktur
│   │   ├── GET    [Admin] Instruktur - Detail Instruktur
│   │   ├── PUT    [Admin] Instruktur - Update Instruktur
│   │   ├── DELETE [Admin] Instruktur - Hapus Instruktur
│   │   ├── GET    [Admin] Instruktur - Kursus Instruktur
│   │   └── GET    [Admin] Instruktur - Statistik Instruktur
│   │
│   ├── 👨‍💼 2.2.4 Admin
│   │   ├── GET    [Admin] Admin - Daftar Admin
│   │   ├── POST   [Admin] Admin - Tambah Admin
│   │   ├── GET    [Admin] Admin - Detail Admin
│   │   ├── PUT    [Admin] Admin - Update Admin
│   │   └── DELETE [Admin] Admin - Hapus Admin
│   │
│   └── 🔑 2.2.5 Role & Permission
│       ├── GET    [Admin] Role - Daftar Role
│       ├── GET    [Admin] Role - Detail Role
│       ├── POST   [Admin] Role - Tambah Role
│       ├── PUT    [Admin] Role - Update Role
│       ├── DELETE [Admin] Role - Hapus Role
│       ├── GET    [Admin] Permission - Daftar Permission
│       ├── POST   [Admin] Pengguna - Assign Role
│       └── POST   [Admin] Pengguna - Assign Permission
│
├── 📖 2.3 Manajemen Skema (Course)
│   │
│   ├── 📚 2.3.1 Skema
│   │   ├── GET    [Admin] Skema - Daftar Semua Skema
│   │   ├── GET    [Admin] Skema - Detail Skema
│   │   ├── POST   [Admin] Skema - Tambah Skema
│   │   ├── PUT    [Admin] Skema - Update Skema
│   │   ├── DELETE [Admin] Skema - Hapus Skema
│   │   ├── POST   [Admin] Skema - Publikasikan Skema
│   │   ├── POST   [Admin] Skema - Batalkan Publikasi
│   │   ├── POST   [Admin] Skema - Duplikasi Skema
│   │   └── GET    [Admin] Skema - Statistik Skema
│   │
│   └── ⚙️ 2.3.2 Pengaturan Skema
│       ├── GET [Admin] Skema - Lihat Pengaturan
│       ├── PUT [Admin] Skema - Update Pengaturan
│       ├── PUT [Admin] Skema - Update Prasyarat
│       └── PUT [Admin] Skema - Update Instruktur
│
├── 📝 2.4 Manajemen Konten
│   │
│   ├── 📑 2.4.1 Unit Kompetensi
│   │   ├── GET    [Admin] Unit Kompetensi - Daftar Unit
│   │   ├── GET    [Admin] Unit Kompetensi - Detail Unit
│   │   ├── POST   [Admin] Unit Kompetensi - Tambah Unit
│   │   ├── PUT    [Admin] Unit Kompetensi - Update Unit
│   │   ├── DELETE [Admin] Unit Kompetensi - Hapus Unit
│   │   ├── POST   [Admin] Unit Kompetensi - Urutkan Unit
│   │   └── POST   [Admin] Unit Kompetensi - Duplikasi Unit
│   │
│   ├── 📄 2.4.2 Elemen Kompetensi (Lesson)
│   │   ├── GET    [Admin] Elemen Kompetensi - Daftar Elemen
│   │   ├── GET    [Admin] Elemen Kompetensi - Detail Elemen
│   │   ├── POST   [Admin] Elemen Kompetensi - Tambah Elemen
│   │   ├── PUT    [Admin] Elemen Kompetensi - Update Elemen
│   │   ├── DELETE [Admin] Elemen Kompetensi - Hapus Elemen
│   │   ├── POST   [Admin] Elemen Kompetensi - Urutkan Elemen
│   │   └── POST   [Admin] Elemen Kompetensi - Duplikasi Elemen
│   │
│   ├── 📋 2.4.3 Tugas
│   │   ├── GET    [Admin] Tugas - Daftar Tugas
│   │   ├── GET    [Admin] Tugas - Detail Tugas
│   │   ├── POST   [Admin] Tugas - Tambah Tugas
│   │   ├── PUT    [Admin] Tugas - Update Tugas
│   │   ├── DELETE [Admin] Tugas - Hapus Tugas
│   │   └── GET    [Admin] Tugas - Daftar Submission
│   │
│   ├── ❓ 2.4.4 Latihan Soal (Quiz)
│   │   ├── GET    [Admin] Latihan Soal - Daftar Latihan
│   │   ├── GET    [Admin] Latihan Soal - Detail Latihan
│   │   ├── POST   [Admin] Latihan Soal - Tambah Latihan
│   │   ├── PUT    [Admin] Latihan Soal - Update Latihan
│   │   ├── DELETE [Admin] Latihan Soal - Hapus Latihan
│   │   ├── POST   [Admin] Latihan Soal - Tambah Soal
│   │   ├── PUT    [Admin] Latihan Soal - Update Soal
│   │   ├── DELETE [Admin] Latihan Soal - Hapus Soal
│   │   └── POST   [Admin] Latihan Soal - Urutkan Soal
│   │
│   └── 📁 2.4.5 Media & File
│       ├── POST   [Admin] Media - Upload Gambar
│       ├── POST   [Admin] Media - Upload Dokumen
│       ├── POST   [Admin] Media - Upload Video
│       ├── DELETE [Admin] Media - Hapus Media
│       └── GET    [Admin] Media - Daftar Media
│
├── 📊 2.5 Laporan & Analitik
│   │
│   ├── 👥 2.5.1 Laporan Pengguna
│   │   ├── GET  [Admin] Laporan - Statistik Pengguna
│   │   ├── GET  [Admin] Laporan - Pertumbuhan Pengguna
│   │   ├── GET  [Admin] Laporan - Pengguna Aktif
│   │   ├── GET  [Admin] Laporan - Engagement Pengguna
│   │   └── POST [Admin] Laporan - Export Laporan Pengguna
│   │
│   ├── 📖 2.5.2 Laporan Skema
│   │   ├── GET  [Admin] Laporan - Statistik Skema
│   │   ├── GET  [Admin] Laporan - Pendaftaran Skema
│   │   ├── GET  [Admin] Laporan - Penyelesaian Skema
│   │   ├── GET  [Admin] Laporan - Skema Populer
│   │   └── POST [Admin] Laporan - Export Laporan Skema
│   │
│   ├── 📚 2.5.3 Laporan Pembelajaran
│   │   ├── GET  [Admin] Laporan - Tingkat Penyelesaian
│   │   ├── GET  [Admin] Laporan - Rata-rata Nilai
│   │   ├── GET  [Admin] Laporan - Waktu Belajar
│   │   ├── GET  [Admin] Laporan - Ringkasan Progress
│   │   └── POST [Admin] Laporan - Export Laporan Pembelajaran
│   │
│   └── 🎮 2.5.4 Laporan Gamifikasi
│       ├── GET [Admin] Laporan - Distribusi XP
│       ├── GET [Admin] Laporan - Pemberian Lencana
│       ├── GET [Admin] Laporan - Statistik Leaderboard
│       └── GET [Admin] Laporan - Metrik Engagement
│
├── 🎯 2.6 Manajemen Pendaftaran
│   │
│   ├── 📝 2.6.1 Pendaftaran
│   │   ├── GET    [Admin] Pendaftaran - Daftar Semua Pendaftaran
│   │   ├── GET    [Admin] Pendaftaran - Detail Pendaftaran
│   │   ├── POST   [Admin] Pendaftaran - Daftarkan Partisipan
│   │   ├── PUT    [Admin] Pendaftaran - Update Pendaftaran
│   │   ├── DELETE [Admin] Pendaftaran - Hapus Pendaftaran
│   │   ├── POST   [Admin] Pendaftaran - Daftar Bulk
│   │   └── POST   [Admin] Pendaftaran - Export Pendaftaran
│   │
│   ├── ✅ 2.6.2 Status Pendaftaran
│   │   ├── PUT [Admin] Pendaftaran - Aktifkan Pendaftaran
│   │   ├── PUT [Admin] Pendaftaran - Suspend Pendaftaran
│   │   ├── PUT [Admin] Pendaftaran - Selesaikan Pendaftaran
│   │   └── PUT [Admin] Pendaftaran - Batalkan Pendaftaran
│   │
│   └── 🔑 2.6.3 Kunci Pendaftaran
│       ├── GET    [Admin] Kunci Pendaftaran - Daftar Kunci
│       ├── POST   [Admin] Kunci Pendaftaran - Generate Kunci
│       ├── DELETE [Admin] Kunci Pendaftaran - Cabut Kunci
│       └── GET    [Admin] Kunci Pendaftaran - Penggunaan Kunci

├── 🎮 2.7 Manajemen Gamifikasi
│   │
│   ├── 🏅 2.7.1 Lencana
│   │   ├── GET    [Admin] Lencana - Daftar Semua Lencana
│   │   ├── GET    [Admin] Lencana - Detail Lencana
│   │   ├── POST   [Admin] Lencana - Tambah Lencana
│   │   ├── PUT    [Admin] Lencana - Update Lencana
│   │   ├── DELETE [Admin] Lencana - Hapus Lencana
│   │   ├── POST   [Admin] Lencana - Upload Icon
│   │   └── GET    [Admin] Lencana - Statistik Lencana
│   │
│   ├── 📋 2.7.2 Aturan Lencana
│   │   ├── GET    [Admin] Aturan Lencana - Daftar Aturan
│   │   ├── GET    [Admin] Aturan Lencana - Detail Aturan
│   │   ├── POST   [Admin] Aturan Lencana - Tambah Aturan
│   │   ├── PUT    [Admin] Aturan Lencana - Update Aturan
│   │   ├── DELETE [Admin] Aturan Lencana - Hapus Aturan
│   │   └── POST   [Admin] Aturan Lencana - Test Aturan
│   │
│   ├── 📊 2.7.3 Level
│   │   ├── GET    [Admin] Level - Daftar Konfigurasi Level
│   │   ├── GET    [Admin] Level - Detail Level
│   │   ├── POST   [Admin] Level - Tambah Level
│   │   ├── PUT    [Admin] Level - Update Level
│   │   ├── DELETE [Admin] Level - Hapus Level
│   │   ├── POST   [Admin] Level - Sinkronisasi Level
│   │   └── GET    [Admin] Level - Statistik Level
│   │
│   ├── ⭐ 2.7.4 Sumber XP
│   │   ├── GET    [Admin] Sumber XP - Daftar Sumber
│   │   ├── GET    [Admin] Sumber XP - Detail Sumber
│   │   ├── POST   [Admin] Sumber XP - Tambah Sumber
│   │   ├── PUT    [Admin] Sumber XP - Update Sumber
│   │   └── DELETE [Admin] Sumber XP - Hapus Sumber
│   │
│   └── 🏆 2.7.5 Leaderboard
│       ├── GET  [Admin] Leaderboard - Peringkat Global
│       ├── GET  [Admin] Leaderboard - Peringkat Skema
│       ├── POST [Admin] Leaderboard - Update Peringkat
│       └── POST [Admin] Leaderboard - Reset Peringkat
│
├── 🗑️ 2.8 Manajemen Sampah
│   ├── GET    [Admin] Sampah - Daftar Item Terhapus
│   ├── GET    [Admin] Sampah - Detail Item
│   ├── POST   [Admin] Sampah - Restore Item
│   ├── DELETE [Admin] Sampah - Hapus Permanen
│   ├── POST   [Admin] Sampah - Restore Bulk
│   ├── DELETE [Admin] Sampah - Kosongkan Sampah
│   └── GET    [Admin] Sampah - Tipe Sumber
│
├── ⚙️ 2.9 Pengaturan Sistem
│   │
│   ├── 🔧 2.9.1 Pengaturan Umum
│   │   ├── GET  [Admin] Pengaturan - Lihat Semua Pengaturan
│   │   ├── PUT  [Admin] Pengaturan - Update Pengaturan
│   │   └── POST [Admin] Pengaturan - Reset ke Default
│   │
│   ├── 📂 2.9.2 Kategori
│   │   ├── GET    [Admin] Kategori - Daftar Kategori
│   │   ├── POST   [Admin] Kategori - Tambah Kategori
│   │   ├── PUT    [Admin] Kategori - Update Kategori
│   │   └── DELETE [Admin] Kategori - Hapus Kategori
│   │
│   ├── 🏷️ 2.9.3 Tag
│   │   ├── GET    [Admin] Tag - Daftar Tag
│   │   ├── POST   [Admin] Tag - Tambah Tag
│   │   ├── PUT    [Admin] Tag - Update Tag
│   │   └── DELETE [Admin] Tag - Hapus Tag
│   │
│   └── 📊 2.9.4 Master Data
│       ├── GET    [Admin] Master Data - Daftar Tipe
│       ├── GET    [Admin] Master Data - Data Tipe
│       ├── POST   [Admin] Master Data - Tambah Item
│       ├── PUT    [Admin] Master Data - Update Item
│       └── DELETE [Admin] Master Data - Hapus Item
│
└── 📋 2.10 Log Aktivitas & Audit
    │
    ├── 📝 2.10.1 Log Aktivitas
    │   ├── GET  [Admin] Log Aktivitas - Daftar Log
    │   ├── GET  [Admin] Log Aktivitas - Detail Log
    │   └── POST [Admin] Log Aktivitas - Export Log
    │
    └── 🔍 2.10.2 Log Audit
        ├── GET  [Admin] Log Audit - Daftar Log
        ├── GET  [Admin] Log Audit - Detail Log
        ├── GET  [Admin] Log Audit - Daftar Aksi
        └── POST [Admin] Log Audit - Export Log
```

---

## 🎓 [3] WEB INSTRUKTUR - DASHBOARD INSTRUKTUR

### Struktur Lengkap
```
🎓 [3] WEB INSTRUKTUR - Dashboard Instruktur
│
├── 🔐 3.1 Autentikasi
│   ├── POST [Instruktur] Autentikasi - Login
│   ├── POST [Instruktur] Autentikasi - Logout
│   ├── POST [Instruktur] Autentikasi - Refresh Token
│   └── GET  [Instruktur] Autentikasi - Data User Saat Ini
│
├── 📖 3.2 Skema Saya
│   │
│   ├── 📚 3.2.1 Daftar & Detail Skema
│   │   ├── GET [Instruktur] Skema - Daftar Skema Saya
│   │   ├── GET [Instruktur] Skema - Detail Skema
│   │   ├── GET [Instruktur] Skema - Statistik Skema
│   │   ├── GET [Instruktur] Skema - Partisipan Terdaftar
│   │   └── GET [Instruktur] Skema - Progress Skema
│   │
│   └── ⚙️ 3.2.2 Pengaturan Skema
│       ├── GET [Instruktur] Skema - Lihat Pengaturan
│       └── PUT [Instruktur] Skema - Update Pengaturan
│
├── 📝 3.3 Pembuatan Konten
│   │
│   ├── 📑 3.3.1 Unit Kompetensi
│   │   ├── GET    [Instruktur] Unit Kompetensi - Daftar Unit
│   │   ├── GET    [Instruktur] Unit Kompetensi - Detail Unit
│   │   ├── POST   [Instruktur] Unit Kompetensi - Tambah Unit
│   │   ├── PUT    [Instruktur] Unit Kompetensi - Update Unit
│   │   ├── DELETE [Instruktur] Unit Kompetensi - Hapus Unit
│   │   └── POST   [Instruktur] Unit Kompetensi - Urutkan Unit
│   │
│   ├── 📄 3.3.2 Elemen Kompetensi
│   │   ├── GET    [Instruktur] Elemen Kompetensi - Daftar Elemen
│   │   ├── GET    [Instruktur] Elemen Kompetensi - Detail Elemen
│   │   ├── POST   [Instruktur] Elemen Kompetensi - Tambah Elemen
│   │   ├── PUT    [Instruktur] Elemen Kompetensi - Update Elemen
│   │   ├── DELETE [Instruktur] Elemen Kompetensi - Hapus Elemen
│   │   ├── POST   [Instruktur] Elemen Kompetensi - Urutkan Elemen
│   │   └── POST   [Instruktur] Elemen Kompetensi - Upload Konten
│   │
│   ├── 📋 3.3.3 Tugas
│   │   ├── GET    [Instruktur] Tugas - Daftar Tugas
│   │   ├── GET    [Instruktur] Tugas - Detail Tugas
│   │   ├── POST   [Instruktur] Tugas - Tambah Tugas
│   │   ├── PUT    [Instruktur] Tugas - Update Tugas
│   │   ├── DELETE [Instruktur] Tugas - Hapus Tugas
│   │   └── GET    [Instruktur] Tugas - Daftar Submission
│   │
│   └── ❓ 3.3.4 Latihan Soal
│       ├── GET    [Instruktur] Latihan Soal - Daftar Latihan
│       ├── GET    [Instruktur] Latihan Soal - Detail Latihan
│       ├── POST   [Instruktur] Latihan Soal - Tambah Latihan
│       ├── PUT    [Instruktur] Latihan Soal - Update Latihan
│       ├── DELETE [Instruktur] Latihan Soal - Hapus Latihan
│       ├── POST   [Instruktur] Latihan Soal - Tambah Soal
│       ├── PUT    [Instruktur] Latihan Soal - Update Soal
│       └── DELETE [Instruktur] Latihan Soal - Hapus Soal
│
├── ✅ 3.4 Penilaian
│   │
│   ├── 📥 3.4.1 Submission
│   │   ├── GET [Instruktur] Penilaian - Daftar Submission
│   │   ├── GET [Instruktur] Penilaian - Detail Submission
│   │   ├── GET [Instruktur] Penilaian - Submission Pending
│   │   └── GET [Instruktur] Penilaian - Submission Dinilai
│   │
│   ├── 📊 3.4.2 Manajemen Nilai
│   │   ├── POST [Instruktur] Penilaian - Beri Nilai
│   │   ├── PUT  [Instruktur] Penilaian - Update Nilai
│   │   ├── POST [Instruktur] Penilaian - Rilis Nilai
│   │   ├── POST [Instruktur] Penilaian - Rilis Bulk
│   │   ├── POST [Instruktur] Penilaian - Tambah Feedback
│   │   └── PUT  [Instruktur] Penilaian - Update Feedback
│   │
│   └── 📈 3.4.3 Laporan Nilai
│       ├── GET  [Instruktur] Penilaian - Distribusi Nilai
│       ├── GET  [Instruktur] Penilaian - Nilai Partisipan
│       └── POST [Instruktur] Penilaian - Export Nilai
│
├── 💬 3.5 Forum
│   │
│   ├── 📌 3.5.1 Manajemen Forum
│   │   ├── GET    [Instruktur] Forum - Daftar Forum Skema
│   │   ├── GET    [Instruktur] Forum - Detail Thread
│   │   ├── POST   [Instruktur] Forum - Balas Thread
│   │   ├── POST   [Instruktur] Forum - Pin Thread
│   │   ├── POST   [Instruktur] Forum - Unpin Thread
│   │   ├── DELETE [Instruktur] Forum - Hapus Thread
│   │   └── DELETE [Instruktur] Forum - Hapus Balasan
│   │
│   └── 🛡️ 3.5.2 Moderasi Forum
│       ├── POST [Instruktur] Forum - Kunci Thread
│       ├── POST [Instruktur] Forum - Buka Kunci Thread
│       ├── POST [Instruktur] Forum - Tandai Terselesaikan
│       ├── GET  [Instruktur] Forum - Konten Dilaporkan
│       └── POST [Instruktur] Forum - Moderasi Konten
│
├── 📊 3.6 Analitik Skema
│   │
│   ├── 📈 3.6.1 Ringkasan
│   │   ├── GET [Instruktur] Analitik - Ringkasan Skema
│   │   ├── GET [Instruktur] Analitik - Tren Pendaftaran
│   │   ├── GET [Instruktur] Analitik - Tingkat Penyelesaian
│   │   └── GET [Instruktur] Analitik - Metrik Engagement
│   │
│   ├── 👥 3.6.2 Analitik Partisipan
│   │   ├── GET [Instruktur] Analitik - Progress Partisipan
│   │   ├── GET [Instruktur] Analitik - Performa Partisipan
│   │   ├── GET [Instruktur] Analitik - Partisipan Berisiko
│   │   └── GET [Instruktur] Analitik - Partisipan Terbaik
│   │
│   ├── 📚 3.6.3 Analitik Konten
│   │   ├── GET [Instruktur] Analitik - Penyelesaian Elemen
│   │   ├── GET [Instruktur] Analitik - Statistik Tugas
│   │   ├── GET [Instruktur] Analitik - Statistik Latihan
│   │   └── GET [Instruktur] Analitik - Waktu Belajar
│   │
│   └── 📥 3.6.4 Export Laporan
│       ├── POST [Instruktur] Analitik - Export Ringkasan
│       ├── POST [Instruktur] Analitik - Export Data Partisipan
│       └── POST [Instruktur] Analitik - Export Nilai
│
└── 👤 3.7 Profil
    ├── GET  [Instruktur] Profil - Data Profil Saya
    ├── PUT  [Instruktur] Profil - Update Profil
    ├── POST [Instruktur] Profil - Upload Avatar
    ├── PUT  [Instruktur] Profil - Ganti Password
    └── GET  [Instruktur] Profil - Statistik Saya
```


---

## 🌐 [4] SHARED - API BERSAMA

### Struktur Lengkap
```
🌐 [4] SHARED - API Bersama
│
├── 🔐 4.1 Autentikasi
│   ├── POST [Shared] Autentikasi - Login
│   ├── POST [Shared] Autentikasi - Register
│   ├── POST [Shared] Autentikasi - Logout
│   ├── POST [Shared] Autentikasi - Refresh Token
│   ├── POST [Shared] Autentikasi - Lupa Password
│   ├── POST [Shared] Autentikasi - Reset Password
│   ├── POST [Shared] Autentikasi - Verifikasi Email
│   ├── POST [Shared] Autentikasi - Kirim Ulang Verifikasi
│   ├── GET  [Shared] Autentikasi - Data User Saat Ini
│   └── PUT  [Shared] Autentikasi - Update Profil
│
├── 👤 4.2 Manajemen Profil
│   ├── GET    [Shared] Profil - Data Profil Saya
│   ├── PUT    [Shared] Profil - Update Profil
│   ├── POST   [Shared] Profil - Upload Avatar
│   ├── DELETE [Shared] Profil - Hapus Avatar
│   ├── PUT    [Shared] Profil - Ganti Password
│   ├── PUT    [Shared] Profil - Update Email
│   ├── PUT    [Shared] Profil - Update Preferensi
│   └── GET    [Shared] Profil - Lihat Preferensi
│
├── 🔔 4.3 Notifikasi
│   │
│   ├── 📬 4.3.1 Daftar Notifikasi
│   │   ├── GET [Shared] Notifikasi - Daftar Notifikasi
│   │   ├── GET [Shared] Notifikasi - Jumlah Belum Dibaca
│   │   └── GET [Shared] Notifikasi - Detail Notifikasi
│   │
│   ├── ✅ 4.3.2 Aksi Notifikasi
│   │   ├── PUT    [Shared] Notifikasi - Tandai Dibaca
│   │   ├── PUT    [Shared] Notifikasi - Tandai Semua Dibaca
│   │   ├── DELETE [Shared] Notifikasi - Hapus Notifikasi
│   │   └── DELETE [Shared] Notifikasi - Hapus Semua
│   │
│   └── ⚙️ 4.3.3 Preferensi Notifikasi
│       ├── GET  [Shared] Notifikasi - Lihat Preferensi
│       ├── PUT  [Shared] Notifikasi - Update Preferensi
│       └── POST [Shared] Notifikasi - Reset Preferensi
│
├── 🔍 4.4 Pencarian
│   │
│   ├── 🔎 4.4.1 Pencarian Global
│   │   ├── GET [Shared] Pencarian - Pencarian Global
│   │   ├── GET [Shared] Pencarian - Autocomplete
│   │   ├── GET [Shared] Pencarian - Cari Skema
│   │   ├── GET [Shared] Pencarian - Cari Pengguna
│   │   └── GET [Shared] Pencarian - Cari Konten
│   │
│   └── 📜 4.4.2 Riwayat Pencarian
│       ├── GET    [Shared] Pencarian - Lihat Riwayat
│       ├── DELETE [Shared] Pencarian - Hapus Riwayat
│       └── DELETE [Shared] Pencarian - Hapus Item Riwayat
│
├── 📁 4.5 Upload Media
│   │
│   ├── 📤 4.5.1 Upload
│   │   ├── POST [Shared] Media - Upload Gambar
│   │   ├── POST [Shared] Media - Upload Dokumen
│   │   ├── POST [Shared] Media - Upload Video
│   │   ├── POST [Shared] Media - Upload Audio
│   │   └── POST [Shared] Media - Upload Bulk
│   │
│   └── 🗂️ 4.5.2 Manajemen Media
│       ├── GET    [Shared] Media - Daftar Media Saya
│       ├── GET    [Shared] Media - Detail Media
│       ├── DELETE [Shared] Media - Hapus Media
│       ├── GET    [Shared] Media - URL Media
│       └── POST   [Shared] Media - Generate Signed URL
│
├── ⚙️ 4.6 Pengaturan Sistem
│   ├── GET [Shared] Pengaturan - Pengaturan Aplikasi
│   ├── GET [Shared] Pengaturan - Konfigurasi Level
│   ├── GET [Shared] Pengaturan - Sumber XP
│   ├── GET [Shared] Pengaturan - Kategori
│   └── GET [Shared] Pengaturan - Tag
│
└── 📊 4.7 Master Data
    ├── GET [Shared] Master Data - Daftar Tipe
    ├── GET [Shared] Master Data - Data Tipe
    ├── GET [Shared] Master Data - Skema
    ├── GET [Shared] Master Data - Partisipan
    └── GET [Shared] Master Data - Instruktur
```

---

## 📚 [5] DOKUMENTASI & REFERENSI

### Struktur Lengkap
```
📚 [5] DOKUMENTASI & REFERENSI
│
├── 📖 5.1 Ringkasan API
│   ├── 📄 Base URL & Versioning
│   ├── 📄 Rate Limiting
│   ├── 📄 Pagination
│   ├── 📄 Filtering & Sorting
│   └── 📄 Including Relations
│
├── 🔑 5.2 Panduan Autentikasi
│   ├── 📄 Alur Login
│   ├── 📄 Manajemen Token
│   ├── 📄 Refresh Token
│   ├── 📄 Proses Logout
│   └── 📄 Reset Password
│
├── 📝 5.3 Format Request/Response
│   ├── 📄 Format Request Standar
│   ├── 📄 Format Response Standar
│   ├── 📄 Struktur Response Sukses
│   ├── 📄 Struktur Response Error
│   ├── 📄 Format Pagination
│   └── 📄 Informasi Meta
│
├── ⚠️ 5.4 Kode Error
│   ├── 📄 HTTP Status Codes
│   ├── 📄 Custom Error Codes
│   ├── 📄 Pesan Error
│   ├── 📄 Validation Errors
│   └── 📄 Panduan Troubleshooting
│
├── 🚀 5.5 Panduan Quick Start
│   ├── 📄 Setup Environment
│   ├── 📄 API Call Pertama
│   ├── 📄 Workflow Umum
│   ├── 📄 Best Practices
│   └── 📄 Tips Testing
│
└── 📋 5.6 Changelog & Updates
    ├── 📄 Version History
    ├── 📄 Breaking Changes
    ├── 📄 New Features
    └── 📄 Deprecated Endpoints
```

---

## 🎯 URUTAN PEMBUATAN COLLECTION

### Fase 1: Setup & Foundation (Hari 1-2)
```
1. Buat Collection utama "Levl API Collection"
2. Setup Environment Variables:
   - {{base_url}} = https://api.levl.id/v1
   - {{auth_token}} = (akan diisi otomatis)
   - {{user_id}} = (akan diisi otomatis)
   - {{student_id}} = (untuk testing)
   - {{instructor_id}} = (untuk testing)
   - {{course_id}} = (untuk testing)
   
3. Buat folder utama [1] sampai [5]
4. Buat dokumentasi di folder [5] DOKUMENTASI & REFERENSI
```

### Fase 2: Shared APIs (Hari 3-4)
```
5. Lengkapi folder [4] SHARED - API Bersama
   - Mulai dari Autentikasi (paling penting)
   - Lanjut Profil, Notifikasi, Pencarian
   - Terakhir Media & Master Data
   
6. Test semua endpoint Shared
7. Pastikan environment variables ter-update otomatis
```

### Fase 3: Mobile App (Hari 5-7)
```
8. Lengkapi folder [1] MOBILE - Aplikasi Student
   - Mulai dari Autentikasi
   - Lanjut Pembelajaran (Kursus, Unit, Materi, Tugas, Kuis)
   - Lanjut Gamifikasi
   - Lanjut Forum
   - Terakhir Dashboard & Profil
   
9. Test flow lengkap student journey
10. Dokumentasikan contoh use case mobile
```

### Fase 4: Admin Dashboard (Hari 8-12)
```
11. Lengkapi folder [2] WEB ADMIN - Dashboard Admin
    - Mulai dari Autentikasi
    - Lanjut Manajemen Pengguna (paling kompleks)
    - Lanjut Manajemen Skema & Konten
    - Lanjut Laporan & Analitik
    - Lanjut Pendaftaran
    - Lanjut Gamifikasi
    - Lanjut Sampah & Pengaturan
    - Terakhir Log Aktivitas
    
12. Test flow lengkap admin operations
13. Dokumentasikan contoh use case admin
```

### Fase 5: Instructor Dashboard (Hari 13-15)
```
14. Lengkapi folder [3] WEB INSTRUKTUR - Dashboard Instruktur
    - Mulai dari Autentikasi
    - Lanjut Skema Saya
    - Lanjut Pembuatan Konten
    - Lanjut Penilaian (paling penting untuk instruktur)
    - Lanjut Forum
    - Lanjut Analitik
    - Terakhir Profil
    
15. Test flow lengkap instructor operations
16. Dokumentasikan contoh use case instructor
```

### Fase 6: Testing & Documentation (Hari 16-18)
```
17. Review semua endpoint
18. Tambahkan Tests untuk setiap request
19. Tambahkan Examples (success & error) untuk setiap request
20. Lengkapi Description untuk setiap request
21. Update dokumentasi di folder [5]
22. Buat Quick Start Guide
23. Buat Video Tutorial (optional)
```

### Fase 7: Finalisasi (Hari 19-20)
```
24. Export Collection
25. Buat dokumentasi deployment
26. Share dengan team
27. Training session untuk FE & Mobile Developer
28. Collect feedback & iterate
```

---

## 📋 TEMPLATE REQUEST STANDAR

### Nama Request
```
[METHOD] [Platform] Module - Action
```

### Contoh Lengkap
```
POST [Admin] Partisipan - Tambah Partisipan
```

### URL Template
```
{{base_url}}/[path]
```

### Headers Template
```
Authorization: Bearer {{auth_token}}
Content-Type: application/json
Accept: application/json
Accept-Language: id
```

### Body Template (POST/PUT)
```json
{
  "field1": "value1",
  "field2": "value2"
}
```

### Description Template
```markdown
## Deskripsi
[Penjelasan singkat endpoint ini]

## Authorization
Bearer Token required / Public

## Path Parameters
- `id` (required): ID dari resource

## Query Parameters
- `page` (optional): Halaman (default: 1)
- `per_page` (optional): Item per halaman (default: 15)
- `search` (optional): Keyword pencarian
- `sort` (optional): Field sorting (default: created_at)
- `direction` (optional): Arah sorting (asc/desc, default: desc)

## Request Body
[Daftar field yang diperlukan]

## Response Success
[Contoh response sukses]

## Response Error
[Contoh response error]
```

### Tests Template
```javascript
// Test: Status code
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

// Test: Response structure
pm.test("Response has data", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('data');
});

// Test: Response time
pm.test("Response time < 500ms", function () {
    pm.expect(pm.response.responseTime).to.be.below(500);
});

// Save variables
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("resource_id", jsonData.data.id);
}
```


---

## 🔧 ENVIRONMENT VARIABLES

### Development Environment
```json
{
  "name": "Levl API - Development",
  "values": [
    {
      "key": "base_url",
      "value": "http://localhost:8000/api/v1",
      "enabled": true
    },
    {
      "key": "auth_token",
      "value": "",
      "enabled": true
    },
    {
      "key": "user_id",
      "value": "",
      "enabled": true
    },
    {
      "key": "student_id",
      "value": "1",
      "enabled": true
    },
    {
      "key": "instructor_id",
      "value": "2",
      "enabled": true
    },
    {
      "key": "admin_id",
      "value": "3",
      "enabled": true
    },
    {
      "key": "course_id",
      "value": "",
      "enabled": true
    },
    {
      "key": "unit_id",
      "value": "",
      "enabled": true
    },
    {
      "key": "lesson_id",
      "value": "",
      "enabled": true
    },
    {
      "key": "assignment_id",
      "value": "",
      "enabled": true
    },
    {
      "key": "quiz_id",
      "value": "",
      "enabled": true
    },
    {
      "key": "badge_id",
      "value": "",
      "enabled": true
    },
    {
      "key": "enrollment_id",
      "value": "",
      "enabled": true
    }
  ]
}
```

### Staging Environment
```json
{
  "name": "Levl API - Staging",
  "values": [
    {
      "key": "base_url",
      "value": "https://staging-api.levl.id/api/v1",
      "enabled": true
    }
    // ... sama seperti development
  ]
}
```

### Production Environment
```json
{
  "name": "Levl API - Production",
  "values": [
    {
      "key": "base_url",
      "value": "https://api.levl.id/api/v1",
      "enabled": true
    }
    // ... sama seperti development
  ]
}
```

---

## 📊 STATISTIK COLLECTION

### Total Endpoints per Platform
```
📱 Mobile Student App:        ~85 endpoints
💻 Web Admin Dashboard:       ~210 endpoints
🎓 Web Instruktur Dashboard:  ~95 endpoints
🌐 Shared Common APIs:        ~55 endpoints
───────────────────────────────────────────
📊 TOTAL:                     ~445 endpoints
```

### Breakdown per Method
```
GET:    ~270 endpoints (60%)
POST:   ~110 endpoints (25%)
PUT:    ~50 endpoints  (11%)
DELETE: ~15 endpoints  (4%)
```

### Breakdown per Module
```
📚 Pembelajaran & Konten:     ~130 endpoints
👥 Manajemen Pengguna:        ~65 endpoints
🎮 Gamifikasi:                ~55 endpoints
✅ Penilaian:                 ~35 endpoints
💬 Forum:                     ~30 endpoints
📊 Laporan & Analitik:        ~45 endpoints
⚙️ Sistem & Pengaturan:       ~50 endpoints
🔐 Autentikasi:               ~15 endpoints
🔔 Notifikasi:                ~10 endpoints
🔍 Pencarian:                 ~10 endpoints
```

---

## ✅ CHECKLIST KUALITAS REQUEST

Setiap request harus memenuhi checklist ini sebelum dianggap selesai:

### Naming & Structure
- [ ] Nama mengikuti format: `[METHOD] [Platform] Module - Action`
- [ ] Platform label benar (Mobile/Admin/Instruktur/Shared)
- [ ] Module name konsisten dengan folder
- [ ] Action verb jelas dan spesifik (berbahasa Indonesia)
- [ ] Folder structure terorganisir dengan baik

### Technical Setup
- [ ] URL menggunakan `{{base_url}}`
- [ ] Method HTTP sudah benar
- [ ] Headers lengkap (Authorization, Content-Type, Accept)
- [ ] Request body ada (untuk POST/PUT)
- [ ] Query parameters terdokumentasi

### Documentation
- [ ] Description lengkap dan jelas
- [ ] Path parameters terdokumentasi
- [ ] Query parameters terdokumentasi
- [ ] Request body terdokumentasi
- [ ] Response examples ada (success & error)
- [ ] Status codes terdokumentasi

### Testing
- [ ] Tests untuk status code
- [ ] Tests untuk response structure
- [ ] Tests untuk response time
- [ ] Environment variables disimpan otomatis
- [ ] Error handling tested

### Examples
- [ ] Minimal 1 success example
- [ ] Minimal 1 error example (validation/auth)
- [ ] Real data examples (bukan placeholder)

---

## 🎨 KONVENSI PENAMAAN BAHASA INDONESIA

### Verb/Action yang Digunakan
```
✅ Daftar          - untuk list/collection
✅ Detail          - untuk single item
✅ Tambah          - untuk create
✅ Update          - untuk update
✅ Hapus           - untuk delete
✅ Upload          - untuk upload file
✅ Download        - untuk download file
✅ Export          - untuk export data
✅ Import          - untuk import data
✅ Kirim           - untuk send
✅ Generate        - untuk generate
✅ Aktifkan        - untuk activate
✅ Nonaktifkan     - untuk deactivate
✅ Publikasikan    - untuk publish
✅ Batalkan        - untuk cancel/unpublish
✅ Tandai          - untuk mark
✅ Rilis           - untuk release
✅ Restore         - untuk restore
✅ Duplikasi       - untuk duplicate
✅ Urutkan         - untuk reorder
✅ Sinkronisasi    - untuk sync
✅ Reset           - untuk reset
✅ Ganti           - untuk change
✅ Lihat           - untuk view/get
✅ Cari            - untuk search
✅ Filter          - untuk filter
```

### Module Names (Bahasa Indonesia)
```
✅ Autentikasi           - Authentication
✅ Pengguna              - Users
✅ Partisipan            - Students
✅ Instruktur            - Instructors
✅ Admin                 - Admins
✅ Skema                 - Courses
✅ Unit Kompetensi       - Units
✅ Elemen Kompetensi     - Lessons
✅ Materi                - Lessons (untuk mobile)
✅ Tugas                 - Assignments
✅ Latihan Soal          - Quizzes
✅ Kuis                  - Quizzes (untuk mobile)
✅ Penilaian             - Grading
✅ Forum                 - Forums
✅ Gamifikasi            - Gamification
✅ Lencana               - Badges
✅ Level                 - Levels
✅ Leaderboard           - Leaderboard
✅ XP                    - Experience Points
✅ Pendaftaran           - Enrollments
✅ Laporan               - Reports
✅ Analitik              - Analytics
✅ Dashboard             - Dashboard
✅ Profil                - Profile
✅ Notifikasi            - Notifications
✅ Pencarian             - Search
✅ Media                 - Media
✅ Sampah                - Trash
✅ Pengaturan            - Settings
✅ Kategori              - Categories
✅ Tag                   - Tags
✅ Master Data           - Master Data
✅ Log Aktivitas         - Activity Logs
✅ Log Audit             - Audit Logs
✅ Role                  - Roles
✅ Permission            - Permissions
```

### Status & State (Bahasa Indonesia)
```
✅ Aktif                 - Active
✅ Nonaktif              - Inactive
✅ Pending               - Pending
✅ Disetujui             - Approved
✅ Ditolak               - Rejected
✅ Selesai               - Completed
✅ Dibatalkan            - Cancelled
✅ Terhapus              - Deleted
✅ Dipublikasikan        - Published
✅ Draft                 - Draft
✅ Terkunci              - Locked
✅ Tersedia              - Available
```

---

## 🚀 BEST PRACTICES

### Untuk Backend Developer
1. **Konsistensi Response**: Gunakan format response yang sama di semua endpoint
2. **Error Handling**: Berikan error message yang jelas dan actionable
3. **Validation**: Validasi input di backend, dokumentasikan di Postman
4. **Performance**: Optimalkan query, gunakan pagination
5. **Security**: Implementasi rate limiting, authentication, authorization
6. **Versioning**: Gunakan API versioning (v1, v2, dst)
7. **Documentation**: Update Postman setiap ada perubahan API

### Untuk Frontend Developer
1. **Environment**: Gunakan environment variables untuk base_url
2. **Token Management**: Simpan token dengan aman, handle refresh token
3. **Error Handling**: Handle semua possible error responses
4. **Loading States**: Implementasi loading state untuk UX yang baik
5. **Caching**: Cache data yang jarang berubah
6. **Pagination**: Implementasi infinite scroll atau pagination
7. **Testing**: Test dengan berbagai skenario (success, error, edge cases)

### Untuk Mobile Developer
1. **Offline Support**: Implementasi offline-first jika memungkinkan
2. **Network Optimization**: Minimize API calls, batch requests
3. **Image Optimization**: Compress images sebelum upload
4. **Token Storage**: Gunakan secure storage untuk token
5. **Push Notifications**: Integrate dengan notification system
6. **Deep Linking**: Support deep linking untuk better UX
7. **Error Messages**: Tampilkan error message yang user-friendly

---

## 📖 CONTOH USE CASE

### Use Case 1: Student Login & Browse Course (Mobile)
```
1. POST [Mobile] Autentikasi - Login
   → Save auth_token & user_id
   
2. GET [Mobile] Dashboard - Ringkasan
   → Lihat overview

3. GET [Mobile] Kursus - Daftar Kursus Saya
   → Lihat enrolled courses
   
4. GET [Mobile] Kursus - Detail Kursus
   → Pilih course, lihat detail
   
5. GET [Mobile] Kursus - Struktur Konten Kursus
   → Lihat units & lessons
   
6. GET [Mobile] Materi - Detail Materi
   → Buka lesson
   
7. POST [Mobile] Materi - Tandai Selesai
   → Mark as complete
   
8. GET [Mobile] Gamifikasi - Statistik Saya
   → Cek XP & level
```

### Use Case 2: Admin Create Course & Enroll Students (Web Admin)
```
1. POST [Admin] Autentikasi - Login
   → Save auth_token
   
2. POST [Admin] Skema - Tambah Skema
   → Create new course
   → Save course_id
   
3. POST [Admin] Unit Kompetensi - Tambah Unit
   → Add unit to course
   → Save unit_id
   
4. POST [Admin] Elemen Kompetensi - Tambah Elemen
   → Add lesson to unit
   
5. POST [Admin] Tugas - Tambah Tugas
   → Add assignment
   
6. POST [Admin] Skema - Publikasikan Skema
   → Publish course
   
7. GET [Admin] Partisipan - Daftar Partisipan
   → Get students list
   
8. POST [Admin] Pendaftaran - Daftar Bulk
   → Enroll multiple students
   
9. GET [Admin] Laporan - Statistik Skema
   → Check course stats
```

### Use Case 3: Instructor Grade Assignment (Web Instruktur)
```
1. POST [Instruktur] Autentikasi - Login
   → Save auth_token
   
2. GET [Instruktur] Skema - Daftar Skema Saya
   → View my courses
   
3. GET [Instruktur] Penilaian - Submission Pending
   → View pending submissions
   
4. GET [Instruktur] Penilaian - Detail Submission
   → Open submission detail
   
5. POST [Instruktur] Penilaian - Beri Nilai
   → Grade the submission
   
6. POST [Instruktur] Penilaian - Tambah Feedback
   → Add feedback
   
7. POST [Instruktur] Penilaian - Rilis Nilai
   → Release grade to student
   
8. GET [Instruktur] Analitik - Distribusi Nilai
   → Check grade distribution
```

---

## 🎯 KESIMPULAN

Struktur Postman ini dirancang dengan prinsip:

1. **Platform-First**: Memudahkan developer menemukan endpoint sesuai platform mereka
2. **Konsistensi**: Penamaan dan struktur yang konsisten di semua endpoint
3. **Dokumentasi Lengkap**: Setiap endpoint terdokumentasi dengan baik
4. **Mudah Maintain**: Struktur yang terorganisir memudahkan update
5. **Developer-Friendly**: Mudah dipahami oleh FE, Mobile, dan BE developer
6. **Bahasa Indonesia**: Menggunakan terminologi yang familiar untuk tim

### Total Estimasi Waktu
- **Setup & Foundation**: 2 hari
- **Shared APIs**: 2 hari
- **Mobile App**: 3 hari
- **Admin Dashboard**: 5 hari
- **Instructor Dashboard**: 3 hari
- **Testing & Documentation**: 3 hari
- **Finalisasi**: 2 hari
- **TOTAL**: 20 hari kerja (4 minggu)

### Next Steps
1. Review struktur ini dengan team
2. Approve struktur dan naming convention
3. Mulai implementasi sesuai urutan fase
4. Regular sync dengan FE & Mobile team
5. Iterate based on feedback

---

**Dokumen ini adalah single source of truth untuk struktur Postman Collection Levl API.**

**Versi**: 2.0  
**Terakhir Update**: 15 Maret 2026  
**Maintainer**: Backend Team
