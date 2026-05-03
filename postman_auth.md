# 🔐 Autentikasi & Pengguna – Panduan Endpoint

> **Base URL:** `{{url}}/api/v1`

---

## Tentang Token per Role

| Folder | Token yang Digunakan |
|--------|----------------------|
| Publik (Login, Register, dll.) | Tidak perlu token |
| Pengguna yang Sudah Login | `{{access_token_student}}` / `{{access_token_admin}}` / `{{access_token_instructor}}` |
| Admin (Manajemen Pengguna) | `{{access_token_admin}}` |
| Superadmin (Hapus, Bulk Delete) | `{{access_token_superadmin}}` |

---

## Alur Autentikasi

```
=== PUBLIK — AUTENTIKASI DASAR ===
1. Registrasi akun baru (Asesi)            → POST   /auth/register
2. Login (semua role)                       → POST   /auth/login
3. Refresh access token                     → POST   /auth/refresh
4. Logout                                   → POST   /auth/logout

=== PUBLIK — GOOGLE OAUTH ===
5. Dapatkan URL redirect Google             → GET    /auth/google/redirect
6. Callback setelah login Google            → GET    /auth/google/callback

=== AUTENTIKASI LANJUTAN ===
7. Set password (akun Google tanpa pw)     → POST   /auth/set-password
8. Set username (akun Google tanpa un)     → POST   /auth/set-username

=== VERIFIKASI EMAIL ===
9. Verifikasi email dengan token           → POST   /auth/email/verify
10. Kirim ulang email verifikasi            → POST   /auth/email/verify/send

=== RESET KATA SANDI ===
11. Kirim email lupa kata sandi             → POST   /auth/password/forgot
12. Konfirmasi token lupa kata sandi        → POST   /auth/password/forgot/confirm
13. Reset kata sandi dengan token           → POST   /auth/password/reset
14. Ganti kata sandi (user login)           → POST   /auth/password/change

=== PROFIL PENGGUNA (Login Wajib) ===
15. Lihat profil saya                       → GET    /profile
16. Perbarui profil saya                    → PUT    /profile
17. Unggah foto profil                      → POST   /profile/avatar
18. Hapus foto profil                       → DELETE /profile/avatar
19. Ganti kata sandi via profil             → PUT    /profile/password
20. Lihat pengaturan privasi                → GET    /profile/privacy
21. Perbarui pengaturan privasi             → PUT    /profile/privacy
22. Lihat riwayat aktivitas saya            → GET    /profile/activities
23. Minta perubahan email                   → POST   /profile/email/change
24. Verifikasi perubahan email              → POST   /profile/email/change/verify
25. Minta penghapusan akun                  → POST   /profile/account/delete/request
26. Konfirmasi penghapusan akun             → POST   /profile/account/delete/confirm
27. Pulihkan akun yang dihapus              → POST   /profile/account/restore

=== PROFIL PUBLIK & MENTION ===
28. Lihat profil publik pengguna lain       → GET    /users/:user_id/profile
29. Cari pengguna untuk @mention            → GET    /courses/:course_slug/users/mentions

=== MANAJEMEN PENGGUNA (Admin/Instruktur) ===
30. Daftar semua pengguna                   → GET    /users
31. Detail pengguna                         → GET    /users/:user_id
32. Kursus yang diikuti pengguna            → GET    /users/:user_id/enrolled-course
33. Skema yang dipegang instruktur          → GET    /users/:user_id/assigned-schemes
34. Riwayat aktivitas terbaru pengguna      → GET    /user/:user_id/latest-activity
35. Buat pengguna baru                      → POST   /users
36. Perbarui data pengguna                  → PUT    /users/:user_id
37. Reset kata sandi pengguna (Admin)       → PUT    /users/:user_id/reset-password
38. Hapus pengguna (soft delete)            → DELETE /users/:user_id

=== OPERASI MASSAL (Admin) ===
39. Ekspor data pengguna                    → POST   /users/bulk/export
40. Aktifkan banyak pengguna sekaligus      → POST   /users/bulk/activate
41. Nonaktifkan banyak pengguna sekaligus   → POST   /users/bulk/deactivate
42. Hapus banyak pengguna sekaligus         → DELETE /users/bulk/delete
```

---

## ── PUBLIK — AUTENTIKASI DASAR ──

---

## 1. [POST] Registrasi Akun Baru (Asesi)

**Endpoint:**
```
POST {{url}}/api/v1/auth/register
```

**Authorization:** Tidak diperlukan

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `name` | string | ✅ | Nama lengkap (maks. 255 karakter) |
| `username` | string | ✅ | Username unik, min. 3 karakter, hanya huruf/angka/`_`/`.`/`-` |
| `email` | string | ✅ | Alamat email unik |
| `password` | string | ✅ | Min. 8 karakter, harus ada huruf besar, kecil, angka, dan simbol |
| `password_confirmation` | string | ✅ | Konfirmasi password, harus sama dengan `password` |

**Contoh Body:**
```json
{
  "name": "Budi Santoso",
  "username": "budi.santoso",
  "email": "budi@example.com",
  "password": "P@ssw0rd!",
  "password_confirmation": "P@ssw0rd!"
}
```

**Contoh Respons (201 Created):**
```json
{
  "success": true,
  "message": "Registrasi berhasil.",
  "data": {
    "user": {
      "id": 101,
      "name": "Budi Santoso",
      "email": "budi@example.com",
      "username": "budi.santoso",
      "avatar_url": null,
      "status": "Active",
      "email_verified_at": null,
      "roles": [{ "name": "Student" }]
    },
    "access_token": "eyJ0eXAiOiJKV1Qi...",
    "refresh_token": "def50200...",
    "expires_in": 3600
  },
  "meta": null,
  "errors": null
}
```

---

## 2. [POST] Login (Semua Role)

**Endpoint:**
```
POST {{url}}/api/v1/auth/login
```

**Authorization:** Tidak diperlukan

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `login` | string | ✅ | Email atau username |
| `password` | string | ✅ | Kata sandi (min. 8 karakter) |

**Contoh Body:**
```json
{
  "login": "budi@example.com",
  "password": "P@ssw0rd!"
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "user": {
      "id": 101,
      "name": "Budi Santoso",
      "email": "budi@example.com",
      "username": "budi.santoso",
      "avatar_url": null,
      "status": "Active",
      "email_verified_at": "2026-04-01T10:00:00.000000Z",
      "roles": [{ "name": "Student" }]
    },
    "access_token": "eyJ0eXAiOiJKV1Qi...",
    "refresh_token": "def50200...",
    "expires_in": 3600
  },
  "meta": null,
  "errors": null
}
```

> **Test Script (tab Tests di Postman):** Tempel script berikut agar token tersimpan otomatis ke environment variable.
```javascript
let response = pm.response.json();
if (response && response.data && response.data.access_token) {
    let token = response.data.access_token;
    let roleName = response.data.user.roles[0].name.toLowerCase();
    if (roleName === 'superadmin') {
        pm.environment.set("access_token_superadmin", token);
    } else if (roleName === 'admin') {
        pm.environment.set("access_token_admin", token);
    } else if (roleName === 'instructor') {
        pm.environment.set("access_token_instructor", token);
    } else {
        pm.environment.set("access_token_student", token);
    }
    console.log("Token " + roleName + " berhasil diperbarui!");
}
```

---

## 3. [POST] Refresh Access Token

**Endpoint:**
```
POST {{url}}/api/v1/auth/refresh
```

**Authorization:** Bearer (boleh token yang sudah expired)

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `refresh_token` | string | ❌ | Refresh token yang diperoleh saat login. Jika kosong, server akan mencari di cookie. |

**Contoh Body:**
```json
{
  "refresh_token": "def50200..."
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Token berhasil diperbarui.",
  "data": {
    "user": { "id": 101, "name": "Budi Santoso" },
    "access_token": "eyJ0eXAiOiJKV1Qi...",
    "refresh_token": "def50200...",
    "expires_in": 3600
  },
  "meta": null,
  "errors": null
}
```

---

## 4. [POST] Logout

**Endpoint:**
```
POST {{url}}/api/v1/auth/logout
```

**Authorization:** Bearer `{{access_token_student}}`

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `refresh_token` | string | ❌ | Refresh token yang ingin di-invalidate. Jika kosong, hanya access token yang di-logout. |

**Contoh Body:**
```json
{
  "refresh_token": "def50200..."
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Logout berhasil.",
  "data": [],
  "meta": null,
  "errors": null
}
```

---

## ── GOOGLE OAUTH ──

---

## 5. [GET] Dapatkan URL Redirect Google

**Endpoint:**
```
GET {{url}}/api/v1/auth/google/redirect
```

**Authorization:** Tidak diperlukan

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "redirect_url": "https://accounts.google.com/o/oauth2/auth?client_id=..."
  },
  "meta": null,
  "errors": null
}
```

> Arahkan pengguna ke `redirect_url` untuk memulai proses login dengan Google.

---

## 6. [GET] Callback Google OAuth

**Endpoint:**
```
GET {{url}}/api/v1/auth/google/callback
```

**Authorization:** Tidak diperlukan

> Endpoint ini dipanggil otomatis oleh Google setelah pengguna menyetujui akses. Server akan me-redirect ke frontend dengan parameter:

| Parameter | Keterangan |
|-----------|------------|
| `access_token` | JWT access token |
| `refresh_token` | Refresh token |
| `expires_in` | Durasi token dalam detik |
| `needs_username` | `true` jika akun baru dan belum punya username |

---

## ── AUTENTIKASI LANJUTAN ──

---

## 7. [POST] Set Password (Akun Google)

**Endpoint:**
```
POST {{url}}/api/v1/auth/set-password
```

**Authorization:** Bearer `{{access_token_student}}`

> Digunakan oleh akun yang mendaftar via Google dan belum memiliki password.

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `password` | string | ✅ | Min. 8 karakter |
| `password_confirmation` | string | ✅ | Harus sama dengan `password` |

**Contoh Body:**
```json
{
  "password": "P@ssw0rd!",
  "password_confirmation": "P@ssw0rd!"
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Password berhasil ditetapkan.",
  "data": {
    "id": 101,
    "name": "Budi Santoso",
    "email": "budi@gmail.com"
  },
  "meta": null,
  "errors": null
}
```

---

## 8. [POST] Set Username (Akun Google)

**Endpoint:**
```
POST {{url}}/api/v1/auth/set-username
```

**Authorization:** Bearer `{{access_token_student}}`

> Digunakan oleh akun yang mendaftar via Google dan belum memiliki username.

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `username` | string | ✅ | Min. 3, maks. 255 karakter. Hanya huruf, angka, `_`, `.`, `-` |

**Contoh Body:**
```json
{
  "username": "budi.santoso"
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Username berhasil ditetapkan.",
  "data": {
    "id": 101,
    "name": "Budi Santoso",
    "username": "budi.santoso"
  },
  "meta": null,
  "errors": null
}
```

---

## ── VERIFIKASI EMAIL ──

---

## 9. [POST] Verifikasi Email dengan Token

**Endpoint:**
```
POST {{url}}/api/v1/auth/email/verify
```

**Authorization:** Tidak diperlukan

> Pengguna mendapat `uuid` dan `token` dari email verifikasi yang dikirim sistem.

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `token` | string | ✅ | Token 16 karakter dari email |
| `uuid` | string | ✅ | UUID dari email verifikasi |

**Contoh Body:**
```json
{
  "token": "AbCdEfGhIjKlMnOp",
  "uuid": "550e8400-e29b-41d4-a716-446655440000"
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Email berhasil diverifikasi.",
  "data": {
    "user": {
      "id": 101,
      "email": "budi@example.com",
      "email_verified_at": "2026-05-03T08:00:00.000000Z"
    },
    "access_token": "eyJ0eXAiOiJKV1Qi...",
    "refresh_token": "def50200...",
    "expires_in": 3600
  },
  "meta": null,
  "errors": null
}
```

---

## 10. [POST] Kirim Ulang Email Verifikasi

**Endpoint:**
```
POST {{url}}/api/v1/auth/email/verify/send
```

**Authorization:** Bearer `{{access_token_student}}`

**Body:** Tidak diperlukan

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Email verifikasi telah dikirim ulang.",
  "data": {
    "uuid": "550e8400-e29b-41d4-a716-446655440000"
  },
  "meta": null,
  "errors": null
}
```

---

## ── RESET KATA SANDI ──

---

## 11. [POST] Kirim Email Lupa Kata Sandi

**Endpoint:**
```
POST {{url}}/api/v1/auth/password/forgot
```

**Authorization:** Tidak diperlukan

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `login` | string | ✅ | Email atau username akun |

**Contoh Body:**
```json
{
  "login": "budi@example.com"
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Jika akun ditemukan, email reset kata sandi telah dikirim.",
  "data": [],
  "meta": null,
  "errors": null
}
```

---

## 12. [POST] Konfirmasi Token Lupa Kata Sandi

**Endpoint:**
```
POST {{url}}/api/v1/auth/password/forgot/confirm
```

**Authorization:** Tidak diperlukan

> Digunakan untuk memvalidasi token reset sebelum menampilkan form kata sandi baru di frontend.

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `token` | string | ✅ | Token reset dari email (min. 32 karakter) |
| `password` | string | ✅ | Kata sandi baru |
| `password_confirmation` | string | ✅ | Konfirmasi kata sandi baru |

**Contoh Body:**
```json
{
  "token": "d1e2a3d4b5c6f7a8b9c0d1e2f3a4b5c6d7e8f9a0b1c2d3e4f5a6b7c8d9e0f1a2",
  "password": "NewP@ss1!",
  "password_confirmation": "NewP@ss1!"
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Token valid.",
  "data": [],
  "meta": null,
  "errors": null
}
```

---

## 13. [POST] Reset Kata Sandi dengan Token

**Endpoint:**
```
POST {{url}}/api/v1/auth/password/reset
```

**Authorization:** Tidak diperlukan

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `token` | string | ✅ | Token reset dari email (min. 32 karakter) |
| `password` | string | ✅ | Kata sandi baru. Min. 8 karakter, harus ada huruf besar, kecil, angka, simbol |
| `password_confirmation` | string | ✅ | Konfirmasi kata sandi baru |

**Contoh Body:**
```json
{
  "token": "d1e2a3d4b5c6f7a8b9c0d1e2f3a4b5c6d7e8f9a0b1c2d3e4f5a6b7c8d9e0f1a2",
  "password": "NewP@ss1!",
  "password_confirmation": "NewP@ss1!"
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Kata sandi berhasil direset. Silakan login kembali.",
  "data": [],
  "meta": null,
  "errors": null
}
```

---

## 14. [POST] Ganti Kata Sandi (User Login)

**Endpoint:**
```
POST {{url}}/api/v1/auth/password/change
```

**Authorization:** Bearer `{{access_token_student}}`

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `current_password` | string | ✅ | Kata sandi saat ini |
| `new_password` | string | ✅ | Kata sandi baru. Min. 8 karakter, harus ada huruf besar, kecil, angka, simbol |

**Contoh Body:**
```json
{
  "current_password": "P@ssw0rd!",
  "new_password": "NewP@ss1!"
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Kata sandi berhasil diubah.",
  "data": [],
  "meta": null,
  "errors": null
}
```

---

## ── PROFIL PENGGUNA ──

---

## 15. [GET] Lihat Profil Saya

**Endpoint:**
```
GET {{url}}/api/v1/profile
```

**Authorization:** Bearer `{{access_token_student}}`

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "id": 101,
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "username": "budi.santoso",
    "phone": "+6281234567890",
    "bio": "Saya seorang pelajar yang antusias.",
    "location": "Jakarta, Indonesia",
    "avatar_url": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/avatars/budi.jpg",
    "status": "Active",
    "email_verified_at": "2026-04-01T10:00:00.000000Z",
    "created_at": "2026-03-01T08:00:00.000000Z",
    "roles": [{ "name": "Student" }]
  },
  "meta": null,
  "errors": null
}
```

---

## 16. [PUT] Perbarui Profil Saya

**Endpoint:**
```
PUT {{url}}/api/v1/profile
```

**Authorization:** Bearer `{{access_token_student}}`

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `name` | string | ❌ | Nama lengkap (maks. 100 karakter) |
| `phone` | string | ❌ | Nomor telepon (maks. 20 karakter, boleh null) |
| `bio` | string | ❌ | Deskripsi singkat diri (maks. 1000 karakter, boleh null) |
| `location` | string | ❌ | Lokasi (maks. 255 karakter, boleh null) |

**Contoh Body:**
```json
{
  "name": "Budi Santoso Updated",
  "phone": "+6281234567890",
  "bio": "Saya seorang pelajar yang antusias dan bersemangat.",
  "location": "Bandung, Indonesia"
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Profil berhasil diperbarui.",
  "data": {
    "id": 101,
    "name": "Budi Santoso Updated",
    "phone": "+6281234567890",
    "bio": "Saya seorang pelajar yang antusias dan bersemangat.",
    "location": "Bandung, Indonesia"
  },
  "meta": null,
  "errors": null
}
```

---

## 17. [POST] Unggah Foto Profil

**Endpoint:**
```
POST {{url}}/api/v1/profile/avatar
```

**Authorization:** Bearer `{{access_token_student}}`

**Body (multipart/form-data):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `avatar` | file | ✅ | File gambar. Format: jpeg, png, jpg, gif. Maks. 2 MB |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Foto profil berhasil diunggah.",
  "data": {
    "avatar_url": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/avatars/budi-101.jpg"
  },
  "meta": null,
  "errors": null
}
```

---

## 18. [DELETE] Hapus Foto Profil

**Endpoint:**
```
DELETE {{url}}/api/v1/profile/avatar
```

**Authorization:** Bearer `{{access_token_student}}`

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Foto profil berhasil dihapus.",
  "data": null,
  "meta": null,
  "errors": null
}
```

---

## 19. [PUT] Ganti Kata Sandi via Profil

**Endpoint:**
```
PUT {{url}}/api/v1/profile/password
```

**Authorization:** Bearer `{{access_token_student}}`

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `current_password` | string | ✅ | Kata sandi saat ini |
| `new_password` | string | ✅ | Kata sandi baru. Min. 8 karakter, harus ada huruf besar, kecil, angka, simbol |

**Contoh Body:**
```json
{
  "current_password": "P@ssw0rd!",
  "new_password": "NewP@ss1!"
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Kata sandi berhasil diubah.",
  "data": null,
  "meta": null,
  "errors": null
}
```

---

## 20. [GET] Lihat Pengaturan Privasi

**Endpoint:**
```
GET {{url}}/api/v1/profile/privacy
```

**Authorization:** Bearer `{{access_token_student}}`

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "profile_visibility": "public",
    "show_email": false,
    "show_phone": false,
    "show_activity_history": true,
    "show_achievements": true,
    "show_statistics": true,
    "updated_at": "2026-04-01T10:00:00.000000Z"
  },
  "meta": null,
  "errors": null
}
```

---

## 21. [PUT] Perbarui Pengaturan Privasi

**Endpoint:**
```
PUT {{url}}/api/v1/profile/privacy
```

**Authorization:** Bearer `{{access_token_student}}`

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `profile_visibility` | string | ❌ | Visibilitas profil: `public`, `private`, `friends` |
| `show_email` | boolean | ❌ | Tampilkan email ke publik |
| `show_phone` | boolean | ❌ | Tampilkan nomor telepon ke publik |
| `show_activity_history` | boolean | ❌ | Tampilkan riwayat aktivitas |
| `show_achievements` | boolean | ❌ | Tampilkan pencapaian/badge |
| `show_statistics` | boolean | ❌ | Tampilkan statistik belajar |

**Contoh Body:**
```json
{
  "profile_visibility": "private",
  "show_email": false,
  "show_achievements": true
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Pengaturan privasi berhasil diperbarui.",
  "data": {
    "profile_visibility": "private",
    "show_email": false,
    "show_phone": false,
    "show_activity_history": true,
    "show_achievements": true,
    "show_statistics": true,
    "updated_at": "2026-05-03T09:00:00.000000Z"
  },
  "meta": null,
  "errors": null
}
```

---

## 22. [GET] Riwayat Aktivitas Saya

**Endpoint:**
```
GET {{url}}/api/v1/profile/activities
```

**Authorization:** Bearer `{{access_token_student}}`

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `per_page` | `20` | Jumlah data per halaman (default: 20) |
| `filter[type]` | `login` | Filter berdasarkan tipe aktivitas |
| `filter[start_date]` | `2026-04-01` | Filter aktivitas mulai tanggal |
| `filter[end_date]` | `2026-04-30` | Filter aktivitas sampai tanggal |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "id": 500,
      "activity_type": "login",
      "activity_data": {},
      "related_type": null,
      "related_id": null,
      "created_at": "2026-05-03T08:00:00.000000Z",
      "ip_address": "192.168.1.1",
      "location": {
        "city": "Jakarta",
        "region": "DKI Jakarta",
        "country": "Indonesia"
      },
      "device_info": {
        "browser": "Chrome",
        "browser_version": "124.0",
        "platform": "Windows",
        "device": "Desktop",
        "device_type": "desktop"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 95
  },
  "errors": null
}
```

---

## 23. [POST] Minta Perubahan Email

**Endpoint:**
```
POST {{url}}/api/v1/profile/email/change
```

**Authorization:** Bearer `{{access_token_student}}`

> Sistem akan mengirimkan email verifikasi ke alamat email baru. Email lama tetap aktif sampai verifikasi selesai.

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `new_email` | string | ✅ | Alamat email baru (harus unik, belum terdaftar) |

**Contoh Body:**
```json
{
  "new_email": "budi.baru@example.com"
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Email verifikasi telah dikirim ke alamat baru.",
  "data": {
    "uuid": "550e8400-e29b-41d4-a716-446655440001"
  },
  "meta": null,
  "errors": null
}
```

---

## 24. [POST] Verifikasi Perubahan Email

**Endpoint:**
```
POST {{url}}/api/v1/profile/email/change/verify
```

**Authorization:** Bearer `{{access_token_student}}`

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `uuid` | string | ✅ | UUID dari email verifikasi |
| `token` | string | ✅ | Token 16 karakter dari email verifikasi |

**Contoh Body:**
```json
{
  "uuid": "550e8400-e29b-41d4-a716-446655440001",
  "token": "XyZaBcDeFgHiJkLm"
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Email berhasil diubah.",
  "data": [],
  "meta": null,
  "errors": null
}
```

---

## 25. [POST] Minta Penghapusan Akun

**Endpoint:**
```
POST {{url}}/api/v1/profile/account/delete/request
```

**Authorization:** Bearer `{{access_token_student}}`

> Sistem akan mengirimkan kode konfirmasi ke email pengguna. Akun belum dihapus sampai konfirmasi dilakukan.

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `password` | string | ✅ | Kata sandi saat ini untuk konfirmasi identitas |

**Contoh Body:**
```json
{
  "password": "P@ssw0rd!"
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Kode konfirmasi penghapusan akun telah dikirim ke email Anda.",
  "data": {
    "uuid": "550e8400-e29b-41d4-a716-446655440002"
  },
  "meta": null,
  "errors": null
}
```

---

## 26. [POST] Konfirmasi Penghapusan Akun

**Endpoint:**
```
POST {{url}}/api/v1/profile/account/delete/confirm
```

**Authorization:** Bearer `{{access_token_student}}`

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `uuid` | string | ✅ | UUID dari email konfirmasi |
| `token` | string | ✅ | Token 16 karakter dari email konfirmasi |

**Contoh Body:**
```json
{
  "uuid": "550e8400-e29b-41d4-a716-446655440002",
  "token": "PqRsYzAbCdEfGhIj"
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Akun berhasil dihapus.",
  "data": [],
  "meta": null,
  "errors": null
}
```

---

## 27. [POST] Pulihkan Akun yang Dihapus

**Endpoint:**
```
POST {{url}}/api/v1/profile/account/restore
```

**Authorization:** Bearer `{{access_token_student}}`

> Digunakan oleh pengguna yang akunnya baru saja dihapus (soft delete) dan ingin memulihkan kembali. Hanya bisa dalam periode tertentu setelah penghapusan.

**Body:** Tidak diperlukan

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Akun berhasil dipulihkan.",
  "data": null,
  "meta": null,
  "errors": null
}
```

---

## ── PROFIL PUBLIK & MENTION ──

---

## 28. [GET] Lihat Profil Publik Pengguna Lain

**Endpoint:**
```
GET {{url}}/api/v1/users/:user_id/profile
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `user_id` | `101` | ID pengguna yang ingin dilihat profilnya |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "id": 101,
    "name": "Budi Santoso",
    "username": "budi.santoso",
    "avatar_url": null,
    "bio": "Saya seorang pelajar.",
    "location": "Jakarta",
    "roles": [{ "name": "Student" }]
  },
  "meta": null,
  "errors": null
}
```

> Data yang ditampilkan mengikuti pengaturan privasi pengguna tersebut. Field seperti `email` atau `phone` hanya muncul jika pengguna mengizinkan.

---

## 29. [GET] Cari Pengguna untuk @Mention

**Endpoint:**
```
GET {{url}}/api/v1/courses/:course_slug/users/mentions
```

**Authorization:** Bearer `{{access_token_student}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `course_slug` | `analisis-data-untuk-pengambilan-keputusan-7` | Slug kursus tempat diskusi berlangsung |

**Query Params:**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `search` | `bud` | Kata kunci pencarian (min. 1, maks. 50 karakter) — **Wajib** |
| `limit` | `10` | Jumlah hasil (default: 10, maks. 20) |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "id": 101,
      "name": "Budi Santoso",
      "username": "budi.santoso",
      "avatar_url": null
    },
    {
      "id": 102,
      "name": "Budi Prasetyo",
      "username": "budi.prasetyo",
      "avatar_url": "https://levl-buckets.sgp1.cdn.digitaloceanspaces.com/avatars/budi-102.jpg"
    }
  ],
  "meta": null,
  "errors": null
}
```

---

## ── MANAJEMEN PENGGUNA (Admin & Instruktur) ──

---

## 30. [GET] Daftar Semua Pengguna

**Endpoint:**
```
GET {{url}}/api/v1/users
```

**Authorization:** Bearer `{{access_token_admin}}`

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `per_page` | `15` | Jumlah data per halaman (default: 15) |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "id": 101,
      "name": "Budi Santoso",
      "email": "budi@example.com",
      "username": "budi.santoso",
      "avatar_url": null,
      "status": "Active",
      "specialization": null,
      "created_at": "2026-03-01T08:00:00.000000Z",
      "email_verified_at": "2026-04-01T10:00:00.000000Z",
      "role_names": ["Student"]
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  },
  "errors": null
}
```

---

## 31. [GET] Detail Pengguna

**Endpoint:**
```
GET {{url}}/api/v1/users/:user_id
```

**Authorization:** Bearer `{{access_token_admin}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `user_id` | `101` | ID pengguna |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "id": 101,
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "username": "budi.santoso",
    "phone": "+6281234567890",
    "bio": "Saya seorang pelajar.",
    "location": "Jakarta",
    "avatar_url": null,
    "status": "Active",
    "created_at": "2026-03-01T08:00:00.000000Z",
    "email_verified_at": "2026-04-01T10:00:00.000000Z",
    "roles": [{ "name": "Student" }],
    "last_login_at": "2026-05-03T08:00:00.000000Z"
  },
  "meta": null,
  "errors": null
}
```

---

## 32. [GET] Kursus yang Diikuti Pengguna

**Endpoint:**
```
GET {{url}}/api/v1/users/:user_id/enrolled-course
```

**Authorization:** Bearer `{{access_token_admin}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `user_id` | `101` | ID pengguna |

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `per_page` | `15` | Jumlah data per halaman (default: 15) |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "enrollment_id": 5,
      "scheme_name": "Skema Sertifikasi Data Analis",
      "progress_percentage": 75,
      "status": "active",
      "enrolled_at": "2026-03-15T09:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  },
  "errors": null
}
```

---

## 33. [GET] Skema yang Dipegang Instruktur

**Endpoint:**
```
GET {{url}}/api/v1/users/:user_id/assigned-schemes
```

**Authorization:** Bearer `{{access_token_admin}}`

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `user_id` | `5` | ID instruktur |

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `per_page` | `15` | Jumlah data per halaman (default: 15) |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "scheme_id": 1,
      "scheme_name": "Skema Sertifikasi Data Analis",
      "scheme_code": "SKMA-DA-001",
      "students_count": 30,
      "assigned_at": "2026-01-15T09:00:00.000000Z",
      "status": "active"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  },
  "errors": null
}
```

---

## 34. [GET] Riwayat Aktivitas Terbaru Pengguna

**Endpoint:**
```
GET {{url}}/api/v1/user/:user_id/latest-activity
```

**Authorization:** Bearer `{{access_token_admin}}`

> Hanya bisa diakses oleh Admin dan Superadmin.

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `user_id` | `101` | ID pengguna |

**Query Params (opsional):**
| Key | Value Contoh | Deskripsi |
|-----|--------------|-----------|
| `per_page` | `15` | Jumlah data per halaman (default: 15) |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "timestamp": "2026-05-03T08:00:00.000000Z",
      "action_type": "login",
      "description": "Pengguna berhasil login"
    },
    {
      "timestamp": "2026-05-02T15:30:00.000000Z",
      "action_type": "quiz_completed",
      "description": "Menyelesaikan kuis: Statistik Dasar"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
  },
  "errors": null
}
```

---

## 35. [POST] Buat Pengguna Baru (Admin)

**Endpoint:**
```
POST {{url}}/api/v1/users
```

**Authorization:** Bearer `{{access_token_admin}}`

> Hanya bisa diakses oleh Admin dan Superadmin.

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `name` | string | ✅ | Nama lengkap (maks. 255 karakter) |
| `email` | string | ✅ | Alamat email unik |
| `role` | string | ✅ | Role pengguna: `Student`, `Instructor`, `Admin`, `Superadmin` |
| `username` | string | ❌ | Username unik (min. 3 karakter, boleh null — akan digenerate otomatis) |
| `password` | string | ❌ | Kata sandi min. 8 karakter (boleh null — akan dikirim via email) |
| `specialization_id` | integer | ❌ | Wajib jika role = `Instructor`. ID kategori spesialisasi |

**Contoh Body:**
```json
{
  "name": "Sari Dewi",
  "email": "sari@example.com",
  "role": "Instructor",
  "username": "sari.dewi",
  "password": null,
  "specialization_id": 3
}
```

**Contoh Respons (201 Created):**
```json
{
  "success": true,
  "message": "Pengguna berhasil dibuat.",
  "data": {
    "id": 102,
    "name": "Sari Dewi",
    "email": "sari@example.com",
    "username": "sari.dewi",
    "status": "Active",
    "roles": [{ "name": "Instructor" }]
  },
  "meta": null,
  "errors": null
}
```

---

## 36. [PUT] Perbarui Data Pengguna (Admin)

**Endpoint:**
```
PUT {{url}}/api/v1/users/:user_id
```

**Authorization:** Bearer `{{access_token_admin}}`

> Hanya bisa diakses oleh Admin dan Superadmin.

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `user_id` | `102` | ID pengguna yang ingin diperbarui |

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `username` | string | ❌ | Username baru (boleh null) |
| `status` | string | ❌ | Status akun: `Active`, `Inactive`, `Banned` |
| `role` | string | ❌ | Role baru: `Student`, `Instructor`, `Admin`, `Superadmin` |
| `password` | string | ❌ | Kata sandi baru (boleh null) |
| `specialization_id` | integer | ❌ | ID kategori spesialisasi (untuk instruktur, boleh null) |

**Contoh Body:**
```json
{
  "status": "Inactive",
  "role": "Student"
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Data pengguna berhasil diperbarui.",
  "data": {
    "id": 102,
    "name": "Sari Dewi",
    "status": "Inactive",
    "roles": [{ "name": "Student" }]
  },
  "meta": null,
  "errors": null
}
```

---

## 37. [PUT] Reset Kata Sandi Pengguna (Admin)

**Endpoint:**
```
PUT {{url}}/api/v1/users/:user_id/reset-password
```

**Authorization:** Bearer `{{access_token_admin}}`

> Hanya bisa diakses oleh Admin dan Superadmin. Admin menetapkan kata sandi baru langsung tanpa perlu kata sandi lama.

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `user_id` | `101` | ID pengguna |

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `password` | string | ✅ | Kata sandi baru. Min. 8 karakter, harus ada huruf besar, kecil, angka, simbol |
| `password_confirmation` | string | ✅ | Konfirmasi kata sandi baru |

**Contoh Body:**
```json
{
  "password": "AdminReset@1!",
  "password_confirmation": "AdminReset@1!"
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Kata sandi pengguna berhasil direset.",
  "data": {
    "id": 101,
    "name": "Budi Santoso",
    "email": "budi@example.com"
  },
  "meta": null,
  "errors": null
}
```

---

## 38. [DELETE] Hapus Pengguna (Soft Delete)

**Endpoint:**
```
DELETE {{url}}/api/v1/users/:user_id
```

**Authorization:** Bearer `{{access_token_superadmin}}`

> Hanya bisa diakses oleh Superadmin. Penghapusan bersifat soft delete — data masih ada di database dan bisa dipulihkan.

**Path Variables:**
| Key | Value | Deskripsi |
|-----|-------|-----------|
| `user_id` | `101` | ID pengguna yang ingin dihapus |

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Pengguna berhasil dihapus.",
  "data": null,
  "meta": null,
  "errors": null
}
```

---

## ── OPERASI MASSAL (Admin) ──

---

## 39. [POST] Ekspor Data Pengguna

**Endpoint:**
```
POST {{url}}/api/v1/users/bulk/export
```

**Authorization:** Bearer `{{access_token_admin}}`

> Proses ekspor dijalankan di background (queue). Hasil akan dikirim ke email yang ditentukan.

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `email` | string | ❌ | Email tujuan pengiriman file ekspor |
| `user_ids` | array | ❌ | Daftar ID pengguna spesifik yang diekspor. Jika kosong, ekspor semua. |
| `filter[status]` | string | ❌ | Filter status: `Active`, `Inactive`, `Banned` |
| `filter[role]` | string | ❌ | Filter role: `Student`, `Instructor`, `Admin` |
| `search` | string | ❌ | Cari pengguna berdasarkan nama/email |

**Contoh Body:**
```json
{
  "email": "admin@levl.id",
  "filter": {
    "status": "Active",
    "role": "Student"
  }
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Ekspor data sedang diproses. File akan dikirim ke email Anda.",
  "data": null,
  "meta": null,
  "errors": null
}
```

---

## 40. [POST] Aktifkan Banyak Pengguna Sekaligus

**Endpoint:**
```
POST {{url}}/api/v1/users/bulk/activate
```

**Authorization:** Bearer `{{access_token_admin}}`

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `user_ids` | array | ✅ | Daftar ID pengguna yang ingin diaktifkan (min. 1 data) |

**Contoh Body:**
```json
{
  "user_ids": [101, 102, 103]
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Pengguna berhasil diaktifkan.",
  "data": {
    "updated": 3
  },
  "meta": null,
  "errors": null
}
```

---

## 41. [POST] Nonaktifkan Banyak Pengguna Sekaligus

**Endpoint:**
```
POST {{url}}/api/v1/users/bulk/deactivate
```

**Authorization:** Bearer `{{access_token_admin}}`

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `user_ids` | array | ✅ | Daftar ID pengguna yang ingin dinonaktifkan (min. 1 data) |

**Contoh Body:**
```json
{
  "user_ids": [104, 105]
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Pengguna berhasil dinonaktifkan.",
  "data": {
    "updated": 2
  },
  "meta": null,
  "errors": null
}
```

---

## 42. [DELETE] Hapus Banyak Pengguna Sekaligus

**Endpoint:**
```
DELETE {{url}}/api/v1/users/bulk/delete
```

**Authorization:** Bearer `{{access_token_superadmin}}`

> Hanya bisa diakses oleh Superadmin. Penghapusan bersifat soft delete.

**Body (JSON):**
| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `user_ids` | array | ✅ | Daftar ID pengguna yang ingin dihapus (min. 1 data) |

**Contoh Body:**
```json
{
  "user_ids": [106, 107, 108]
}
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Pengguna berhasil dihapus.",
  "data": {
    "deleted": 3
  },
  "meta": null,
  "errors": null
}
```

---

## Catatan Penting

> **Rate Limiting:** Endpoint autentikasi (`/auth/*`) dibatasi 10 permintaan per menit. Endpoint API umum dibatasi 60 permintaan per menit.

> **Password Policy:** Semua kata sandi baru harus memenuhi syarat: minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol. Sistem juga memvalidasi apakah kata sandi pernah bocor dalam pelanggaran data (via Have I Been Pwned).

> **Akun Google OAuth:** Pengguna yang mendaftar via Google tidak memiliki password secara default. Gunakan endpoint `/auth/set-password` untuk menetapkan password pertama kali. Jika `needs_username: true` di callback, arahkan ke `/auth/set-username` sebelum melanjutkan.

> **Soft Delete:** Penghapusan pengguna tidak benar-benar menghapus data dari database. Pengguna yang dihapus masih bisa dipulihkan oleh Superadmin melalui modul Trash, atau oleh pengguna itu sendiri melalui `/profile/account/restore` dalam periode tertentu.

> **Middleware `RestrictDeletedUserAccess`:** Pengguna yang sudah di-soft-delete tidak bisa mengakses endpoint yang memerlukan autentikasi, kecuali `/profile/account/restore`.
