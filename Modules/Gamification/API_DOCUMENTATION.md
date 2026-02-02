# Dokumentasi API Gamification

Dokumentasi lengkap untuk semua endpoint di module `Gamification`.

---

## 1. Challenges (Tantangan)

### 1.1 List Challenges (Global)
Mendapatkan daftar semua challenge yang tersedia di sistem. Endpoint ini mendukung filtering dan sorting yang kompleks menggunakan `spatie/laravel-query-builder`.

- **Method**: `GET`
- **URL**: `/api/v1/challenges`

**Query Parameters:**
| Parameter | Tipe | Wajib | Default | Keterangan | Validasi/Contoh Value |
|-----------|------|-------|---------|------------|-----------------------|
| `per_page` | integer | Tidak | 15 | Jumlah item per halaman | `10`, `25`, `50` |
| `page` | integer | Tidak | 1 | Nomor halaman pagination | `1` |
| `filter[type]` | string | Tidak | - | Filter berdasarkan tipe challenge | `daily`, `weekly`, `one_time` |
| `filter[status]` | string | Tidak | - | Filter status aktif challenge | `active`, `inactive` |
| `filter[points_reward]` | integer | Tidak | - | Filter jumlah poin reward | `100` |
| `filter[has_progress]` | boolean | Tidak | - | Filter apakah user memiliki progress (perlu login) | `1` (true), `0` (false) |
| `filter[criteria_type]` | string | Tidak | - | Filter tipe kriteria | `lessons_completed`, `assignments_submitted`, `exercises_completed`, `xp_earned`, `streak_days`, `courses_completed` |
| `sort` | string | Tidak | `-points_reward` | Sorting data | `points_reward`, `-points_reward`, `created_at`, `-created_at`, `type` |

**Contoh Request:**
```http
GET /api/v1/challenges?filter[type]=daily&sort=-created_at&per_page=10
```

---

### 1.2 Detail Challenge
Mendapatkan detail satu challenge spesifik. Jika user login, juga menyertakan progress user terhadap challenge tersebut.

- **Method**: `GET`
- **URL**: `/api/v1/challenges/{challenge}`

**Path Parameters:**
| Parameter | Tipe | Wajib | Keterangan |
|-----------|------|-------|------------|
| `challenge` | integer | Ya | ID dari Challenge |

**Query Parameters:**
Tidak ada.

---

### 1.3 Claim Challenge Reward
Mengklaim hadiah (reward) dari challenge yang sudah diselesaikan targetnya oleh user.

- **Method**: `POST`
- **URL**: `/api/v1/challenges/{challenge}/claim`

**Path Parameters:**
| Parameter | Tipe | Wajib | Keterangan |
|-----------|------|-------|------------|
| `challenge` | integer | Ya | ID dari Challenge yang akan di-claim |

**Request Body (JSON/Form Data):**
Tidak ada body yang diperlukan (Empty Body). Sistem otomatis mendeteksi User ID dari token authentikasi.

---

### 1.4 User Challenges (My Challenges)
Mendapatkan daftar challenge yang sedang diikuti oleh user (Assignment).

- **Method**: `GET`
- **URL**: `/api/v1/user/challenges`

**Query Parameters:**
| Parameter | Tipe | Wajib | Default | Keterangan |
|-----------|------|-------|---------|------------|
| `per_page` | integer | Tidak | 15 | Pagination limit |
| `page` | integer | Tidak | 1 | Halaman pagination |

---

### 1.5 Completed Challenges History
Mendapatkan daftar challenge yang sudah selesai dikerjakan oleh user.

- **Method**: `GET`
- **URL**: `/api/v1/user/challenges/completed`

**Query Parameters:**
| Parameter | Tipe | Wajib | Default | Keterangan |
|-----------|------|-------|---------|------------|
| `per_page` | integer | Tidak | 15 | Pagination limit |
| `page` | integer | Tidak | 1 | Halaman pagination |
| `filter[challenge.title]` | string | Tidak | - | Filter parsial berdasarkan judul challenge |
| `filter[challenge.type]` | string | Tidak | - | Filter exact berdasarkan tipe challenge |
| `filter[search]` | string | Tidak | - | Pencarian global (Meilisearch) pada Judul/Deskripsi Challenge |
| `sort` | string | Tidak | `-completed_date` | Sorting: `completed_date`, `-completed_date`, `points_earned` |

---

## 2. Leaderboards (Peringkat)

### 2.1 Global Leaderboard
Mendapatkan daftar peringkat global user berdasarkan XP.

- **Method**: `GET`
- **URL**: `/api/v1/leaderboards`

**Query Parameters:**
| Parameter | Tipe | Wajib | Default | Keterangan |
|-----------|------|-------|---------|------------|
| `per_page` | integer | Tidak | 10 | Limit per halaman range 1-100 |
| `page` | integer | Tidak | 1 | Nomor halaman |
| `course_slug` | string | Tidak | - | Jika diisi, menampilkan leaderboard spesifik untuk course tersebut |

---

### 2.2 My Rank
Mendapatkan posisi ranking spesifik user yang sedang login beserta statistik surrounding (user di atas dan di bawahnya).

- **Method**: `GET`
- **URL**: `/api/v1/user/rank`

**Query Parameters:**
Tidak ada.

---

## 3. User Gamification Stats

### 3.1 Gamification Summary
Statistik lengkap user: Level, XP, Progress Bar, Badges Count, Streak, dll.

- **Method**: `GET`
- **URL**: `/api/v1/user/gamification-summary`

**Query Parameters:**
Tidak ada.

---

### 3.2 User Badges
Daftar badges (lencana) yang telah didapatkan user.

- **Method**: `GET`
- **URL**: `/api/v1/user/badges`

**Query Parameters:**
Tidak ada.

---

### 3.3 Points History
Riwayat perolehan poin user (log XP). Mendukung filtering dan sorting.

- **Method**: `GET`
- **URL**: `/api/v1/user/points-history`

**Query Parameters:**
| Parameter | Tipe | Wajib | Default | Keterangan | Contoh Value |
|-----------|------|-------|---------|------------|--------------|
| `per_page` | integer | Tidak | 15 | Pagination limit | `20` |
| `page` | integer | Tidak | 1 | Halaman pagination | `1` |
| `filter[source_type]` | string | Tidak | - | Filter sumber poin | `lesson`, `assignment`, `course` |
| `filter[reason]` | string | Tidak | - | Filter alasan poin | `completion`, `submission`, `bonus` |
| `sort` | string | Tidak | `-created_at` | Sorting data | `created_at`, `points`, `source_type` |

---

### 3.4 Milestones
Daftar pencapaian milestone user (berdasarkan total XP).

- **Method**: `GET`
- **URL**: `/api/v1/user/milestones`

**Query Parameters:**
Tidak ada.

---

### 3.5 User Level (Simple)
Info level user saat ini dan progress XP ke level selanjutnya (Versi simplified dari summary).

- **Method**: `GET`
- **URL**: `/api/v1/user/level`

**Query Parameters:**
Tidak ada.

---

### 3.6 Unit Levels (Per Course)
Mendapatkan level user per unit dalam sebuah course tertentu (Gamification Scope: Unit).

- **Method**: `GET`
- **URL**: `/api/v1/user/levels/{slug}`

**Path Parameters:**
| Parameter | Tipe | Wajib | Keterangan |
|-----------|------|-------|------------|
| `slug` | string | Ya | Slug dari Course |

**Query Parameters:**
Tidak ada.
