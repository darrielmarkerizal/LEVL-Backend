# Struktur Koleksi Postman API Levl

Berdasarkan hasil pemindaian struktur *backend* (berbasis Modules), berikut adalah struktur final yang direkomendasikan. Seluruh penamaan menggunakan **Bahasa Indonesia** dan menggunakan format standar `[METHOD] -> Judul Request`.

Struktur ini tetap dikelompokkan secara **Domain-Driven (berdasarkan Modul)** agar selaras dengan arsitektur folder backend Anda, namun dipisah berdasarkan *Role* untuk kemudahan pengujian menggunakan `{{admin_token}}`, `{{instructor_token}}`, dan `{{asesi_token}}`.

---

```text
📁 Koleksi API Levl
 ┃
 ┣ 📁 Autentikasi
 ┃ ┣ 📄 [POST] -> Login (Shared)
 ┃ ┣ 📄 [POST] -> Registrasi Asesi
 ┃ ┣ 📄 [POST] -> Lupa Kata Sandi
 ┃ ┣ 📄 [POST] -> Reset Kata Sandi
 ┃ ┣ 📄 [POST] -> Refresh Token
 ┃ ┗ 📄 [POST] -> Logout (Shared)
 ┃
 ┣ 📁 Data Master & Sistem (Shared & Admin)
 ┃ ┣ 📄 [GET] -> Ambil Semua Tag
 ┃ ┣ 📄 [GET] -> Ambil Data Master (Tipe, Kursus)
 ┃ ┣ 📁 Aktivitas & Audit (Khusus Admin)
 ┃ ┃ ┣ 📄 [GET] -> Daftar Log Aktivitas
 ┃ ┃ ┗ 📄 [GET] -> Daftar Log Audit
 ┃
 ┣ 📁 Dasbor (Shared - Respon otomatis sesuai Role)
 ┃ ┣ 📄 [GET] -> Ringkasan Dasbor Utama
 ┃ ┣ 📄 [GET] -> Pembelajaran Terakhir (Asesi)
 ┃ ┣ 📄 [GET] -> Pencapaian Terakhir (Asesi)
 ┃ ┗ 📄 [GET] -> Rekomendasi Kursus (Asesi)
 ┃
 ┣ 📁 Pendaftaran (Enrollment)
 ┃ ┣ 📁 Admin & Instruktur
 ┃ ┃ ┣ 📄 [GET] -> Daftar Semua Pendaftaran
 ┃ ┃ ┗ 📄 [PUT] -> Perbarui Status Pendaftaran
 ┃ ┗ 📁 Asesi
 ┃   ┣ 📄 [POST] -> Daftar ke Kursus Baru
 ┃   ┗ 📄 [GET] -> Riwayat Pendaftaran Saya
 ┃
 ┣ 📁 Pembelajaran
 ┃ ┣ 📁 Admin & Instruktur [Butuh {{admin_token}} / {{instructor_token}}]
 ┃ ┃ ┣ 📄 [POST] -> Buat Tugas Baru
 ┃ ┃ ┣ 📄 [PUT] -> Perbarui Data Tugas
 ┃ ┃ ┣ 📄 [DELETE] -> Hapus Tugas
 ┃ ┃ ┣ 📄 [PUT] -> Terbitkan / Sembunyikan Tugas (Publish/Unpublish)
 ┃ ┃ ┣ 📄 [POST] -> Buat Kuis Baru
 ┃ ┃ ┣ 📄 [POST] -> Tambah Pertanyaan Kuis
 ┃ ┃ ┣ 📄 [PUT] -> Urutkan Ulang Pertanyaan Kuis
 ┃ ┃ ┗ 📄 [GET] -> Cari Riwayat Pengumpulan Tugas (Submissions)
 ┃ ┣ 📁 Asesi [Butuh {{asesi_token}}]
 ┃ ┃ ┣ 📄 [GET] -> Daftar Tugas Kursus Belum Selesai
 ┃ ┃ ┣ 📄 [POST] -> Simpan Jawaban Tugas Sementara (Draft)
 ┃ ┃ ┣ 📄 [POST] -> Kirim Pengumpulan Tugas (Submit)
 ┃ ┃ ┣ 📄 [POST] -> Mulai Sesi Kuis
 ┃ ┃ ┗ 📄 [POST] -> Kirim Semua Jawaban Kuis (Submit)
 ┃ ┗ 📄 [GET] -> Lihat Detail Tugas / Kuis (Shared)
 ┃
 ┣ 📁 Informasi & Konten
 ┃ ┣ 📁 Admin [Butuh {{admin_token}}]
 ┃ ┃ ┣ 📄 [POST] -> Buat Informasi Baru
 ┃ ┃ ┣ 📄 [PUT] -> Perbarui Informasi
 ┃ ┃ ┣ 📄 [DELETE] -> Hapus Informasi
 ┃ ┃ ┣ 📄 [POST] -> Atur Jadwal Tayang Publikasi (Schedule)
 ┃ ┃ ┣ 📄 [POST] -> Sematkan / Lepas Sematan Informasi (Pin/Unpin)
 ┃ ┃ ┗ 📄 [POST] -> Unggah Media Gambar
 ┃ ┗ 📄 [GET] -> Daftar Informasi Tersedia (Shared)
 ┃
 ┣ 📁 Forum
 ┃ ┣ 📄 [POST] -> Buat Topik Diskusi Baru
 ┃ ┣ 📄 [POST] -> Balas Topik Diskusi
 ┃ ┣ 📄 [GET] -> Daftar Diskusi Aktif
 ┃ ┗ 📄 [DELETE] -> Hapus Diskusi Terpilih (Kewenangan Admin/Instruktur)
 ┃
 ┣ 📁 Gamifikasi
 ┃ ┣ 📁 Admin & Instruktur
 ┃ ┃ ┣ 📄 [GET] -> Daftar Manajemen Lencana (Badge)
 ┃ ┃ ┣ 📄 [GET] -> Daftar Manajemen Tingkatan (Level)
 ┃ ┃ ┗ 📄 [POST] -> Berikan XP / Lencana Manual ke Asesi
 ┃ ┗ 📁 Asesi
 ┃   ┣ 📄 [GET] -> Daftar Lencana Milik Saya
 ┃   ┣ 📄 [GET] -> Status Tingkatan Saat Ini
 ┃   ┗ 📄 [GET] -> Papan Peringkat Poin (Leaderboard)
 ┃
 ┣ 📁 Penilaian (Grading)
 ┃ ┣ 📁 Admin & Instruktur
 ┃ ┃ ┣ 📄 [POST] -> Setel Nilai Pengumpulan Tugas
 ┃ ┃ ┗ 📄 [PUT] -> Perbarui Nilai Akhir
 ┃ ┗ 📁 Asesi
 ┃   ┗ 📄 [GET] -> Lihat Rapor Nilai Akhir Saya
 ┃
 ┣ 📁 Notifikasi
 ┃ ┣ 📄 [GET] -> Daftar Notifikasi Saya (Shared)
 ┃ ┣ 📄 [POST] -> Tandai Semua Sudah Dibaca (Shared)
 ┃ ┣ 📄 [GET] -> Detail Preferensi Notifikasi (Shared)
 ┃ ┣ 📄 [PUT] -> Perbarui Preferensi Notifikasi (Shared)
 ┃ ┗ 📄 [POST] -> Daftarkan Token FCM Perangkat (Shared)
 ┃
 ┣ 📁 Skema Kurikulum
 ┃ ┣ 📁 Admin [Butuh {{admin_token}}]
 ┃ ┃ ┣ 📄 [POST] -> Buat Skema Kurikulum Baru
 ┃ ┃ ┗ 📄 [POST] -> Perbarui Silabus Skema
 ┃ ┗ 📄 [GET] -> Daftar Skema Aktif (Shared / Asesi)
 ┃
 ┣ 📁 Pencarian
 ┃ ┣ 📄 [GET] -> Lakukan Pencarian Global
 ┃ ┣ 📄 [GET] -> Autocomplete Kata Kunci Pencarian
 ┃ ┣ 📄 [GET] -> Riwayat Pencarian Saya
 ┃ ┗ 📄 [DELETE] -> Bersihkan Riwayat Pencarian
 ┃
 ┣ 📁 Operasional
 ┃ ┗ 📁 Admin [Butuh {{admin_token}}]
 ┃   ┗ 📄 [GET] -> Cek Konfigurasi Server / Sistem
 ┃
 ┗ 📁 Tempat Sampah (Khusus Admin)
   ┣ 📄 [GET] -> Daftar Data Terhapus Sementara (Soft Delete)
   ┣ 📄 [POST] -> Pulihkan Data yang Dihapus (Restore)
   ┗ 📄 [DELETE] -> Hapus Data Permanen
```

## Setup Environment Variables (Sangat Disarankan)

Gunakan *Environment Variables* di Postman agar Anda tidak perlu menyibukkan diri dengan *copy-paste* Token setiap kali berganti akun masuk. Sediakan 3 variabel ini di pengaturan _Environment_:

1. `admin_token`
2. `instructor_token`
3. `asesi_token`

Kemudian selipkan instruksi Test Script ini di _endpoint_ **`[POST] -> Login`** pada bagian tab **Tests**:

```javascript
let response = pm.response.json();

if (response && response.data && response.data.access_token) {
    let token = response.data.access_token;
    
    // Sesuaikan variabel 'roleName' dengan struktur kembalian API Anda. 
    // Contoh di bawah ini mengambil dari array role pertama.
    let roleName = response.data.user.roles[0].name;

    if (roleName.toLowerCase() === 'superadmin' || roleName.toLowerCase() === 'admin') {
        pm.environment.set("admin_token", token);
        console.log("Token Admin berhasil diperbarui!");
    } else if (roleName.toLowerCase() === 'instructor') {
        pm.environment.set("instructor_token", token);
        console.log("Token Instruktur berhasil diperbarui!");
    } else if (roleName.toLowerCase() === 'asesi' || roleName.toLowerCase() === 'student') {
        pm.environment.set("asesi_token", token);
        console.log("Token Asesi berhasil diperbarui!");
    }
}
```

## Pendekatan Fitur Otorisasi Otomatis (*Inherit Auth*)

Postman memiliki fitur *Inherit auth from parent*. Daripada memasang otorisasi satu persatu tiap _endpoint_ (request):

1. Klik folder **`Admin & Instruktur`** di dalam Modul (contoh: bagian Pembelajaran), buka tab `Authorization`.
2. Ubah *Tipe* menjadi `Bearer Token`.
3. Di isian Token isikan `{{admin_token}}` (atau `{{instructor_token}}` saat uji coba instruktur).
4. Di *endpoint* / *request* di dalamnya, biarkan opsi Authorization berada di **"Inherit auth from parent"**.
5. Lakukan hal seragam untuk semua folder **Asesi** dengan menyetel nilai ke `{{asesi_token}}`.
