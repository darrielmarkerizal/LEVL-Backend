# Email Templates - Module Mail

Dokumentasi sistem template email yang konsisten untuk Module Mail.

## Struktur Komponen

### 1. Layout Utama

#### `email-layout.blade.php`
Layout utama untuk semua email dengan header, body, dan footer konsisten.

**Props:**
- `title` (string): Judul halaman email
- `lang` (string, default: 'id'): Bahasa email
- `footer` (slot): Konten footer (optional)

**Contoh Penggunaan:**
```blade
<x-mail::email-layout title="Verifikasi Email">
    <h1>Verifikasi Email Anda</h1>
    <p>Konten email...</p>
    
    <x-slot name="footer">
        <p>Footer kustom</p>
    </x-slot>
</x-mail::email-layout>
```

### 2. Komponen Button

#### `button.blade.php`
Tombol primary (hitam).

**Props:**
- `url` (string): URL tujuan
- `text` (string): Teks tombol

**Contoh:**
```blade
<x-mail::button :url="$loginUrl" text="Masuk Sekarang" />
```

#### `button-secondary.blade.php`
Tombol secondary (abu-abu).

**Props:**
- `url` (string): URL tujuan
- `text` (string): Teks tombol

**Contoh:**
```blade
<x-mail::button-secondary :url="$courseUrl" text="Lihat Course" />
```

#### `button-danger.blade.php`
Tombol danger (merah).

**Props:**
- `url` (string): URL tujuan
- `text` (string): Teks tombol

**Contoh:**
```blade
<x-mail::button-danger :url="$deleteUrl" text="Hapus Akun" />
```

#### `button-success.blade.php`
Tombol success (hijau).

**Props:**
- `url` (string): URL tujuan
- `text` (string): Teks tombol

**Contoh:**
```blade
<x-mail::button-success :url="$completeUrl" text="Selesai" />
```

### 3. Komponen Informasi

#### `info-box.blade.php`
Box informasi dengan warna berbeda sesuai tipe.

**Props:**
- `type` (string): Tipe box - `info` | `warning` | `success` | `danger`

**Contoh:**
```blade
<x-mail::info-box type="warning">
    <strong>Penting:</strong> Link ini berlaku 60 menit.
</x-mail::info-box>
```

#### `code-box.blade.php`
Box untuk menampilkan kode/kredensial.

**Props:**
- `label` (string): Label box
- `value` (string): Nilai yang ditampilkan
- `fontSize` (string, optional): Ukuran font (default: '20px')
- `letterSpacing` (string, optional): Jarak antar huruf (default: '1px')

**Contoh:**
```blade
<x-mail::code-box label="Email" :value="$user->email" />
<x-mail::code-box 
    label="Kode OTP" 
    :value="$otp" 
    fontSize="24px" 
    letterSpacing="4px" 
/>
```

#### `course-box.blade.php`
Box untuk informasi course.

**Props:**
- `title` (string): Judul course
- `code` (string, optional): Kode course

**Contoh:**
```blade
<x-mail::course-box :title="$course->title" :code="$course->code" />
```

#### `info-container.blade.php`
Container untuk multiple info rows.

**Contoh:**
```blade
<x-mail::info-container>
    <div class="info-row">
        <div class="info-label">Course</div>
        <div class="info-value">{{ $course->title }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Peserta</div>
        <div class="info-value">{{ $student->name }}</div>
    </div>
</x-mail::info-container>
```

#### `status-box.blade.php`
Badge status dengan warna.

**Props:**
- `status` (string): Status - `pending` | `active` | `completed` | `cancelled` | `declined`

**Contoh:**
```blade
<x-mail::status-box status="active">Terdaftar Aktif</x-mail::status-box>
```

### 4. Komponen Utility

#### `divider.blade.php`
Garis pemisah horizontal.

**Contoh:**
```blade
<x-mail::divider />
```

#### `url-box.blade.php`
Box untuk menampilkan URL sebagai fallback.

**Props:**
- `url` (string): URL yang ditampilkan
- `label` (string, optional): Label custom

**Contoh:**
```blade
<x-mail::url-box :url="$verifyUrl" />
```

## Konfigurasi

### Logo/App Name
Logo email menggunakan `config('app.name', 'Levl')`. Ubah di `.env`:
```env
APP_NAME="Nama Aplikasi Anda"
```

## Template Email yang Tersedia

### Auth
- ✅ `auth/credentials.blade.php` - Kredensial akun baru
- ✅ `auth/verify.blade.php` - Verifikasi email
- ✅ `auth/reset.blade.php` - Reset password
- ✅ `auth/change-email-verify.blade.php` - Verifikasi perubahan email
- ✅ `auth/account-deletion-verify.blade.php` - Konfirmasi hapus akun
- `auth/users-export.blade.php` - Export users (menggunakan Laravel default)

### Enrollments
- ✅ `enrollments/student-enrollment-active.blade.php` - Enrollment berhasil
- ✅ `enrollments/student-enrollment-pending.blade.php` - Enrollment pending
- ✅ `enrollments/student-enrollment-approved.blade.php` - Enrollment disetujui
- ✅ `enrollments/student-enrollment-declined.blade.php` - Enrollment ditolak
- ✅ `enrollments/admin-enrollment-notification.blade.php` - Notifikasi admin

### Learning
- ✅ `learning/assignment-published.blade.php` - Assignment baru

### Schemes
- ✅ `schemes/course-completed.blade.php` - Course selesai (custom layout dengan celebration)

## Best Practices

1. **Konsistensi**: Selalu gunakan komponen yang sudah ada
2. **Responsif**: Semua komponen sudah responsive-ready
3. **Accessibility**: Gunakan semantic HTML dan alt text
4. **Testing**: Test di berbagai email client (Gmail, Outlook, etc.)
5. **Inline Styles**: Komponen menggunakan inline styles untuk kompatibilitas

## Menambahkan Email Baru

Contoh template email baru:

```blade
<x-mail::email-layout title="Judul Email">
    <h1>Heading Email</h1>
    
    <p>Halo {{ $user->name }},</p>
    <p>Konten email Anda...</p>

    <x-mail::info-box type="info">
        Informasi penting
    </x-mail::info-box>

    <x-mail::button :url="$actionUrl" text="Tombol Aksi" />

    <x-mail::divider />

    <x-mail::url-box :url="$actionUrl" />

    <x-slot name="footer">
        <p>Footer custom jika diperlukan</p>
    </x-slot>
</x-mail::email-layout>
```

## Color Palette

- **Primary**: `#1a1a1a` (Hitam)
- **Success**: `#059669` (Hijau)
- **Warning**: `#fbbf24` (Kuning)
- **Danger**: `#ef4444` (Merah)
- **Info**: `#3b82f6` (Biru)
- **Text**: `#404040` (Abu gelap)
- **Text Light**: `#737373` (Abu muda)
- **Background**: `#f5f5f5` (Abu sangat muda)
- **Border**: `#e5e5e5` (Abu border)

## Troubleshooting

### Komponen tidak muncul
- Pastikan prefix `mail::` digunakan: `<x-mail::button ...`
- Cek lokasi file di `Modules/Mail/resources/views/components/`

### Styling tidak konsisten
- Email client kadang override styles
- Gunakan inline styles untuk critical styles
- Test di berbagai client

### Variable tidak tersedia
- Pastikan variable dikirim dari controller/mailable class
- Gunakan `{{ $variable ?? 'default' }}` untuk fallback
