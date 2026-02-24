# Search Module

Modul **Search** bertanggung jawab untuk menangani fitur pencarian terpusat (Global Search) dan pencatatan riwayat pencarian (Search History) pada aplikasi. Modul ini dirancang secara modular dan berkomunikasi dengan modul lain (Auth, Schemes, Forums) melalui antarmuka *Service Contracts*.

## Endpoint Pencarian Global

Pencarian utama kini dipusatkan pada satu endpoint global yang mencari data secara serentak ke berbagai entitas, alih-alih menggunakan endpoint terpisah (seperti `search/courses` yang sebelumnya sudah dihapus).

- **Route**: `GET /api/v1/search`
- **Controller**: `SearchController@globalSearch`
- **Tujuan**: Mengambil data hasil pencarian dari User, Course, serta Forum dan membatasinya hingga 5 rekaman (records) per kategori pencarian.

## Bagaimana Cara Search History Disimpan?

Penyimpanan riwayat pencarian (Search History) dikelola secara otomatis ketika proses pencarian global (`/api/v1/search`) terjadi. Alurnya adalah sebagai berikut:

1. **Permintaan Diterima**: Klien memanggil endpoint `/api/v1/search?q=keyword`.
2. **Eksekusi Pencarian**: `SearchService` memanggil masing-masing `searchGlobal` dari modul Auth, Schemes, dan Forums.
3. **Pencatatan Histori**: Jika pengguna yang melakukan pencarian **sedang login (authenticated)** dan *query* pencariannya **tidak kosong**, maka histori akan dicatat.

Proses pencatatan histori terjadi langsung di dalam `SearchController@globalSearch`:

```php
// Jika user login dan query tidak kosong
if (auth()->check() && ! empty(trim($query))) {
    // Menghitung total hasil pencarian
    $totalResults = $results['users']->count() + $results['courses']->count() + $results['forums']->count();
    
    // Menyimpan history melalui SearchService
    $this->searchService->saveSearchHistory(auth()->user(), $query, [], $totalResults);
}
```

Mekanisme penyimpanan lebih detail di level Repository:
- `SearchService::saveSearchHistory` menggunakan `SearchHistoryRepository::create()` untuk meng-insert rekaman baru ke dalam database tabel `search_histories`. Data yang disimpan meliputi `user_id`, `query` pencarian, `filters` (jika ada), dan `results_count` (jumlah hasil yang ditemukan).

## Manajemen Riwayat Pencarian

Pengguna dapat melihat dan mengelola riwayat pencariannya sendiri melalui endpoint berikut:

- **Melihat Riwayat**: `GET /api/v1/search/history`
- **Menghapus Riwayat**: `DELETE /api/v1/search/history` (dapat menghapus semua riwayat atau ID spesifik dengan parameter `id`).

Kedua endpoint ini dilindungi oleh *middleware* `auth:api` dan mengakses `SearchHistoryRepository` untuk melakukan operasi databasenya.
