# Dokumentasi API dengan Scalar

Dokumentasi API menggunakan Scalar dan diorganisir secara nested (folder → subfolder) sesuai kebutuhan Peserta, Instruktur, dan Admin.

## Akses Dokumentasi

- **URL Scalar:** `http://your-domain.com/scalar`
- **URL OpenAPI JSON:** `http://your-domain.com/api-docs/openapi.json`

## Struktur Folder (Nested)

### Peserta
- Registrasi & Login
- Pendaftaran Kelas/Skema
- Pencarian Skema
- Akses Materi Skema
- Pengerjaan Tugas & Latihan Soal
- Poin, Badges, Levels, Leaderboard
- Akses & Edit Profil
- Info & News
- Notifikasi
- Forum Skema
- Pendaftaran Assessment

### Instruktur
- Manajemen Materi
- Edit Profil Instruktur
- Manajemen Bank Soal
- Penilaian Tugas & Latihan

### Admin
- Login Admin
- Manajemen Pengguna
- Manajemen Pendaftaran Kelas & Assessment
- Manajemen Skema
- Manajemen Unit Kompetensi
- Manajemen Materi Pembelajaran
- Manajemen Bank Soal
- Manajemen Tugas & Jawaban
- Manajemen Poin & Badges
- Manajemen Info & News
- Penilaian Tugas & Latihan
- Edit Profil Admin

### Umum
- Endpoint Lainnya

Setiap folder mewakili kebutuhan bisnis (registrasi, pendaftaran, forum, dsb) dan berisi request terkait di dalam Scalar sebagai nested folder.

## Generate OpenAPI Spec

```bash
php artisan openapi:generate
```

- Output default: `storage/api-docs/openapi.json`
- Ganti lokasi: `php artisan openapi:generate --output=path/to/openapi.json`

## Sumber Data Otomatis

OpenAPI akan otomatis terisi dari:

- Routes module (`Modules/*/routes/api.php`)
- Controller & docblock (`@summary`, `@description`)
- Form Request validation rules
- Middleware (untuk security / auth)

## Menulis Deskripsi Endpoint

Gunakan docblock singkat tanpa penjelasan per role:

```php
/**
 * @summary Membuat submission baru
 * @description Endpoint ini digunakan untuk membuat submission baru pada assignment yang dipilih.
 */
public function store(Request $request, Assignment $assignment)
{
    // ...
}
```

## Autentikasi

- Kebanyakan endpoint memakai JWT bearer.
- Login di `/api/v1/auth/login`, ambil `access_token`, kirim via header `Authorization: Bearer {token}`.

## Format Respons Standar

```json
{
  "status": "success",
  "message": "Berhasil",
  "data": {}
}
```

```json
{
  "status": "error",
  "message": "Terjadi kesalahan",
  "errors": {}
}
```

Pagination menambahkan `data.meta` (current_page, per_page, dsb).

## Konfigurasi Scalar

File `config/scalar.php`:

- `path`: lokasi UI Scalar (default `/scalar`)
- `url`: sumber OpenAPI (sudah diarahkan ke `/api-docs/openapi.json`)
- `configuration.groupTags`: aktif agar folder tampil nested

## Cara Mengubah Struktur Folder

- Mapping ada di `app/Services/OpenApiGeneratorService.php` pada properti `$featureGroups`.
- Edit `label`, `description`, `modules`, dan `keywords` untuk memindahkan request ke folder/subfolder lain.
- Jalankan `php artisan openapi:generate` setelah mengubah struktur.

## Tips Penggunaan

- Gunakan search Scalar untuk menemukan endpoint cepat.
- Setiap folder = konteks fitur bisnis, sehingga mudah dikonsumsi FE, Mobile, UI/UX, dan SA.
- Tambahkan `@summary` & `@description` singkat agar request mudah dipahami.
