# 🗄️ Analisa Optimasi Database — Levl LMS

> **Database**: PostgreSQL  
> **Total Tabel**: 98  
> **Tanggal Analisa**: 30 Maret 2026  
> **Sumber**: `levl_db_backup.sql` + source code analysis

---

## Ringkasan Temuan

| Kategori | Jumlah Temuan | Prioritas |
|----------|:---:|:---------:|
| 1. Tabel/Kolom Redundan | 7 | 🔴 Tinggi |
| 2. Tabel Tidak Terpakai / Bisa Di-drop | 8 | 🟡 Sedang |
| 3. Tabel Belum Dinormalisasi | 5 | 🟡 Sedang |
| 4. Kolom String → Enum | 30+ | 🟠 Sedang-Tinggi |

---

## 1. Tabel/Kolom Redundan

### 1.1 🔴 Empat Sistem Audit/Logging yang Tumpang Tindih

Ditemukan **4 tabel** yang fungsinya sangat mirip — mencatat audit trail:

| Tabel | Modul | Fungsi | Aktif Digunakan? |
|-------|-------|--------|:---:|
| `activity_log` | Spatie Activitylog (3rd party) | Mencatat perubahan model (Course, Unit, Lesson, Tag, Enrollment, User) | ✅ Ya |
| `audit_logs` | Common (AuditLog model) | Immutable compliance audit log | ✅ Ya |
| `audits` | Operations (SystemAudit model) | System-level audit logging | ⚠️ Model ada, tapi **tidak pernah dipanggil** di service/controller |
| `profile_audit_logs` | Auth (ProfileAuditLog) | Audit perubahan profil user | ✅ Ya |

> [!WARNING]
> **`audits` tabel kemungkinan besar redundan.** `SystemAudit` model yang menggunakan tabel ini hanya di-define tetapi tidak pernah di-reference dari service, controller, atau listener manapun. Kolom-kolomnya (`action`, `target_type`, `target_id`, `user_id`, `properties`) 100% overlap dengan `audit_logs`.

**Rekomendasi:**
- **Drop tabel `audits`** — fungsinya sudah tercakup oleh `audit_logs`
- Pertimbangkan menggabungkan `profile_audit_logs` ke dalam `audit_logs` dengan menambah kolom `module = 'profile'`
- Tetap pisahkan `activity_log` (Spatie) karena itu library 3rd party

---

### 1.2 🔴 Dual Tag System — `course_tag_pivot` + `taggables` + `tags_json`

Tag untuk Course dikelola **3 cara berbeda** secara bersamaan:

| Mekanisme | Tabel/Kolom | Digunakan? |
|-----------|-------------|:---:|
| Pivot tradisional | `course_tag_pivot` | ✅ Ya (Course ↔ Tag) |
| Polymorphic pivot | `taggables` | ✅ Ya (News ↔ Tag) |
| JSON column | `courses.tags_json` | ✅ Ya (duplikasi daftar tag) |

> [!IMPORTANT]
> `courses.tags_json` menyimpan data yang **redundan** dengan `course_tag_pivot`. Setiap kali tag di-update, kedua tempat harus di-sync (`TagService` + `CourseLifecycleProcessor`). Ini rawan inkonsistensi.

**Rekomendasi:**
- **Hapus kolom `courses.tags_json`** — gunakan sepenuhnya `course_tag_pivot`
- Konsolidasi `course_tag_pivot` dan `taggables` menjadi satu polymorphic `taggables` tabel jika ingin universal (Course + News pakai satu sistem)

---

### 1.3 🟡 `lesson_completions` vs `lesson_progress`

| Tabel | Kolom Penting | Digunakan? |
|-------|--------------|:---:|
| `lesson_completions` | `lesson_id`, `user_id`, `completed_at` | ✅ Ya (LessonCompletionService) |
| `lesson_progress` | `enrollment_id`, `lesson_id`, `status`, `progress_percent`, `completed_at` | ✅ Ya (ProgressionStateProcessor) |

Kedua tabel mencatat status lesson completion. `lesson_completions` hanya boolean "sudah selesai/belum", sedangkan `lesson_progress` mencatat proses bertahap.

**Rekomendasi:**
- **Gabungkan ke `lesson_progress`** — cukup gunakan satu tabel. Kolom `completed_at` di `lesson_progress` sudah ada

---

### 1.4 🟡 `levels` vs `user_gamification_stats` vs `user_scope_stats`

| Tabel | Data Level | Digunakan? |
|-------|-----------|:---:|
| `levels` | `user_id`, `course_id`, `current_level` | ⚠️ Minimal (hanya 1 reference di User model) |
| `user_gamification_stats` | `user_id`, `global_level`, `total_xp` | ✅ Ya |
| `user_scope_stats` | `user_id`, `scope_type`, `scope_id`, `current_level`, `total_xp` | ✅ Ya |

> [!WARNING]
> Tabel `levels` sangat minim penggunaan. Data `global_level` sudah ada di `user_gamification_stats`, dan data per-course level sudah bisa dicakup oleh `user_scope_stats`.

**Rekomendasi:**
- **Drop tabel `levels`** — gunakan `user_gamification_stats.global_level` untuk level global dan `user_scope_stats` untuk level per-course

---

### 1.5 🟡 Kolom Redundan di `user_gamification_stats`

```
completed_challenges integer DEFAULT 0 NOT NULL
```

Kolom `completed_challenges` merujuk ke fitur "Challenge" yang **sudah di-remove** dari sistem (lihat `CHALLENGE_REMOVAL_SUMMARY.md` di root project). Kolom ini selalu 0.

**Rekomendasi:** Drop kolom `completed_challenges`

---

### 1.6 🟡 `audit_logs` — Kolom Duplikat Internal

Tabel `audit_logs` memiliki kolom-kolom yang redundan secara internal:

| Kolom | Duplikat Dengan |
|-------|----------------|
| `target_type` + `target_id` | `subject_type` + `subject_id` |
| `actor_type` + `actor_id` | `user_id` |

**Rekomendasi:** Pilih satu konvensi (misalnya `target_type/target_id` + `user_id`) dan drop yang lain

---

### 1.7 🟡 `answers` vs `quiz_answers`

| Tabel | Parent | Digunakan? |
|-------|--------|:---:|
| `answers` | `submissions` (Assignment) | ✅ Ya |
| `quiz_answers` | `quiz_submissions` (Quiz) | ✅ Ya |

Struktur kedua tabel hampir identik (`content`, `selected_options`, `score`, `is_auto_graded`, `feedback`). Bisa dipertimbangkan untuk digabung dengan polymorphic relation.

**Rekomendasi:** Opsional — bisa dibiarkan terpisah untuk clarity, tapi jika ingin reduce complexity, gabungkan dengan discriminator column

---

## 2. Tabel yang Tidak Terpakai / Bisa Di-drop

### 2.1 🔴 `audits` (SystemAudit)

- Model `SystemAudit` **ada** tapi **tidak pernah dipanggil** di service/controller manapun
- Semua fungsionalitas sudah tercakup oleh `audit_logs` (AuditLog)
- **Aman untuk di-drop**

### 2.2 🟡 `telescope_entries`, `telescope_entries_tags`, `telescope_monitoring`

- 3 tabel milik Laravel Telescope (debugging tool)
- **Hanya untuk development/debugging**, tidak perlu ada di production
- **Rekomendasi:** Drop di production, atau exclude dari backup

### 2.3 🟡 `social_accounts`

- Model `SocialAccount` ada tapi **TIDAK PERNAH dipanggil** dari controller/service manapun
- Tidak ada OAuth/social login flow yang terimplementasi
- **Aman untuk di-drop** jika social login belum direncanakan

### 2.4 🟡 `login_activities`

- Model `LoginActivity` ada tapi **TIDAK PERNAH dipanggil** dari controller/service manapun
- Data login sudah tercatat di `activity_log` (Spatie) via `TracksUserActivity` trait
- **Aman untuk di-drop** — data login sudah redundan

### 2.5 🟡 `notification_templates`

- Model `NotificationTemplate` ada tapi **TIDAK PERNAH dipanggil** dari service manapun
- Notifikasi dikirim langsung via `Notification` model tanpa templating system
- **Aman untuk di-drop** jika tidak ada rencana implementasi template system

### 2.6 🟡 `reports`

- Tabel `reports` tidak memiliki model/controller yang aktif menggunakannya
- Tidak ada reference `Report::` dari route/controller/service manapun (selain Operations module model definition)
- **Aman untuk di-drop**

### 2.7 🟡 `submission_files`

- Tabel ini hanya punya kolom `id`, `submission_id`, `created_at`, `updated_at`
- File attachment untuk submission sudah dikelola via **Spatie Media Library** (`submission_files` media collection)
- Tabel `submission_files` jadi **shell kosong** tanpa data meaningful
- **Aman untuk di-drop**

### 2.8 🟡 `content_workflow_history`

- Digunakan tapi fungsinya **overlap** dengan `content_revisions`
- Pertimbangkan konsolidasi ke satu tabel

---

## 3. Tabel Belum Dinormalisasi

### 3.1 🔴 `courses.tags_json` — JSON Column Menyimpan Relasi

```sql
tags_json json  -- Stores: ["tag1", "tag2", "tag3"]
```

Tag sudah dinormalisasi ke tabel `tags` + `course_tag_pivot`, tetapi masih ada kolom `tags_json` yang menyimpan duplikat data dalam format JSON.

**Masalah:**
- Tidak bisa JOIN/query efisien
- Data harus di-sync manual ke pivot table
- Rawan inkonsistensi

**Rekomendasi:** Drop `courses.tags_json`, gunakan sepenuhnya pivot table

---

### 3.2 🟡 `courses.prereq_json` — Prerequisite Belum Dinormalisasi

```sql
prereq_json json  -- Stores course IDs as JSON array
```

Prerequisite course disimpan sebagai JSON array of IDs, bukan sebagai relasi many-to-many yang proper.

**Rekomendasi:** Buat tabel `course_prerequisites`:
```sql
CREATE TABLE course_prerequisites (
    id bigserial PRIMARY KEY,
    course_id bigint REFERENCES courses(id) ON DELETE CASCADE,
    prerequisite_course_id bigint REFERENCES courses(id) ON DELETE CASCADE,
    created_at timestamp,
    UNIQUE(course_id, prerequisite_course_id)
);
```

---

### 3.3 🟡 `categories` + `content_categories` — Dua Sistem Kategori Terpisah

| Tabel | Digunakan Oleh |
|-------|---------------|
| `categories` | `courses.category_id`, `users.specialization_id` |
| `content_categories` | `news` (via pivot `news_category`) |

Dua tabel kategori yang terpisah tanpa relasi. `categories` melayani Course & User specialization, sementara `content_categories` hanya untuk News.

**Rekomendasi:** Konsolidasi ke satu tabel `categories` dengan kolom `scope` (e.g. `course`, `news`, `specialization`)

---

### 3.4 🟡 `leaderboards` — Denormalisasi Tanpa Proper Calculation

```sql
CREATE TABLE leaderboards (
    id bigint, user_id bigint, course_id bigint, rank integer
);
```

Tabel ini menyimpan **pre-calculated rank** yang harus di-refresh secara periodik. Data ini sebenarnya bisa dihitung real-time dari `user_gamification_stats` atau `user_scope_stats`.

**Rekomendasi:** Pertimbangkan menggunakan materialized view atau computed query daripada tabel terpisah (mengurangi risiko stale data)

---

### 3.5 🟡 `points` — Terlalu Banyak Metadata Denormalisasi

Tabel `points` menyimpan banyak metadata yang denormalisasi:

```sql
old_level integer,            -- Duplikat dari levels/user_gamification_stats
new_level integer,            -- Duplikat dari levels/user_gamification_stats
triggered_level_up boolean,   -- Computed value, bukan data primordial
ip_address inet,              -- Sudah ada di activity_log
user_agent varchar(255)       -- Sudah ada di activity_log
```

**Rekomendasi:** Drop `ip_address`, `user_agent` (sudah ada di activity_log). `old_level` dan `new_level` bisa dipertahankan untuk audit trail

---

## 4. Kolom String yang Harusnya Enum

### Tabel dengan PostgreSQL Enum yang Sudah Benar ✅

Beberapa tabel sudah menggunakan native PostgreSQL enum type:

| Tabel | Kolom | Enum Type |
|-------|-------|-----------|
| `courses` | `type` | `public.course_type` |
| `courses` | `level_tag` | `public.level_tag` |
| `courses` | `enrollment_type` | `public.enrollment_type` |
| `courses` | `status` | `public.course_status` |
| `enrollments` | `status` | `public.enrollment_status` |
| `assignments` | `status` | `public.assignment_status` |
| `assignments` | `review_mode` | `public.review_mode` |
| `users` | `status` | `public.user_status` |

### 🔴 Kolom yang Masih `varchar` + CHECK Constraint (Harus Migrasi ke Enum)

Berikut adalah kolom-kolom yang **sudah punya CHECK constraint** (menandakan nilainya terbatas), tetapi masih disimpan sebagai `character varying` alih-alih native PostgreSQL enum:

#### Prioritas Tinggi (Tabel Utama / Sering Di-query)

| # | Tabel | Kolom | Nilai yang Valid | PHP Enum Sudah Ada? |
|---|-------|-------|-----------------|:---:|
| 1 | `announcements` | `status` | draft, submitted, in_review, approved, rejected, scheduled, published, archived | ❌ (pakai ContentStatus) |
| 2 | `announcements` | `priority` | low, normal, high | ❌ (pakai Priority enum) |
| 3 | `announcements` | `target_type` | all, role, course | ❌ (pakai TargetType enum) |
| 4 | `news` | `status` | draft, submitted, in_review, approved, rejected, scheduled, published, archived | ❌ |
| 5 | `lessons` | `status` | draft, published | ❌ |
| 6 | `lessons` | `content_type` | markdown, video, link | ✅ `ContentType` |
| 7 | `units` | `status` | draft, published | ❌ |
| 8 | `course_progress` | `status` | not_started, in_progress, completed | ✅ `ProgressStatus` |
| 9 | `lesson_progress` | `status` | not_started, in_progress, completed | ✅ `ProgressStatus` |
| 10 | `unit_progress` | `status` | not_started, in_progress, completed | ✅ `ProgressStatus` |
| 11 | `submissions` | `status` | draft, submitted, graded, late | ✅ `SubmissionStatus` |
| 12 | `quiz_submissions` | `status` | draft, submitted, graded, late, missing | ✅ `QuizSubmissionStatus` |
| 13 | `quiz_submissions` | `grading_status` | pending, partially_graded, waiting_for_grading, graded | ✅ `QuizGradingStatus` |
| 14 | `quizzes` | `status` | draft, published, archived | ✅ `QuizStatus` |
| 15 | `quizzes` | `randomization_type` | static, ... | ✅ `RandomizationType` |
| 16 | `quizzes` | `review_mode` | immediate, ... | ✅ `ReviewMode` |
| 17 | `lesson_blocks` | `block_type` | text, image, video, file, link, youtube, drive, embed | ✅ `BlockType` |

#### Prioritas Sedang (Tabel Pendukung)

| # | Tabel | Kolom | Nilai yang Valid | PHP Enum Sudah Ada? |
|---|-------|-------|-----------------|:---:|
| 18 | `badges` | `type` | completion, quality, speed, habit, social, milestone, hidden | ✅ `BadgeType` |
| 19 | `badges` | `rarity` | common, uncommon, rare, epic, legendary | ✅ `BadgeRarity` |
| 20 | `categories` | `status` | active, inactive | ✅ `CategoryStatus` |
| 21 | `certificates` | `status` | active, revoked, expired | ❌ |
| 22 | `grades` | `status` | pending, graded, reviewed | ✅ `GradeStatus` |
| 23 | `grades` | `source_type` | assignment, attempt | ✅ `SourceType` |
| 24 | `grade_reviews` | `status` | pending, approved, rejected | ❌ |
| 25 | `grading_rubrics` | `scope_type` | exercise, assignment | ❌ |
| 26 | `login_activities` | `status` | success, failed | ❌ |
| 27 | `notifications` | `type` | system, assignment, assessment, ... (17 values) | ✅ `NotificationType` |
| 28 | `notifications` | `channel` | in_app, email, push | ✅ `NotificationChannel` |
| 29 | `notifications` | `priority` | low, normal, high | ❌ |
| 30 | `posts` | `category` | announcement, information, gamification, warning, system, award | ✅ `PostCategory` |
| 31 | `posts` | `status` | draft, scheduled, published | ✅ `PostStatus` |
| 32 | `reactions` | `type` | like, helpful, solved | ❌ |
| 33 | `reports` | `type` | activity, assessment, grading, system, custom | ❌ |
| 34 | `user_notifications` | `status` | unread, read | ❌ |
| 35 | `profile_privacy_settings` | `profile_visibility` | public, private, friends_only | ❌ |

> [!IMPORTANT]
> **Dari 35+ kolom yang menggunakan CHECK constraint**, sebagian besar sudah punya PHP Enum di codebase tetapi **database-nya belum dimigrasi** ke PostgreSQL native enum type. Kolom-kolom ini masih `character varying(255)` yang membuang storage dan tidak optimal untuk indexing.

### 🟡 Kolom String yang BELUM Punya CHECK Constraint Sama Sekali

Kolom-kolom ini bahkan **tidak ada validasi di level database**:

| Tabel | Kolom | Nilai yang Ditemukan di Code |
|-------|-------|------------------------------|
| `audit_logs` | `action` | Sudah ada CHECK. Tapi `actor_type` dan `target_type` tidak |
| `points` | `source_type` | system, lesson, assignment, attempt, streak, badge, ... |
| `points` | `reason` | completion, submission, streak, badge_earned, ... |
| `gamification_event_logs` | `event_type` | Berbagai event type tanpa constraint |
| `user_activities` | `activity_type` | enrollment, completion, submission, achievement, badge_earned, certificate_earned |
| `user_event_counters` | `event_type` | Berbagai event type tanpa constraint |
| `user_event_counters` | `window` | lifetime, daily, weekly, monthly |
| `content_revisions` | `content_type` | Class FQCN string (polymorphic) |
| `content_workflow_history` | `from_state`, `to_state` | draft, submitted, in_review, approved, rejected, published |
| `submissions` | `state` | Nullable varchar(30), tanpa constraint |
| `search_history` | `clicked_result_type` | Nullable varchar tanpa constraint |

---

## Ringkasan Aksi Rekomendasi

### 🔴 Prioritas Tinggi (Lakukan Segera)

| # | Aksi | Impact |
|---|------|--------|
| 1 | **Drop tabel `audits`** | Hapus tabel yang tidak pernah dipanggil |
| 2 | **Drop kolom `courses.tags_json`** | Eliminasi data redundan, sumber bug inkonsistensi |
| 3 | **Migrasi 17 kolom utama dari varchar → PostgreSQL enum** | Performance + data integrity |
| 4 | **Drop kolom `user_gamification_stats.completed_challenges`** | Fitur sudah dihapus |

### 🟡 Prioritas Sedang (Sprint Berikutnya)

| # | Aksi | Impact |
|---|------|--------|
| 5 | **Drop tabel `social_accounts`** | Hapus dead code |
| 6 | **Drop tabel `login_activities`** | Data redundan dengan activity_log |
| 7 | **Drop tabel `notification_templates`** | Tidak pernah digunakan |
| 8 | **Drop tabel `reports`** | Tidak pernah digunakan |
| 9 | **Drop tabel `submission_files`** | Shell kosong, Spatie Media Library sudah handle |
| 10 | **Gabungkan `lesson_completions` ke `lesson_progress`** | Kurangi duplikasi |
| 11 | **Drop tabel `levels`** | Redundan dengan user_gamification_stats + user_scope_stats |
| 12 | **Konsolidasi `categories` + `content_categories`** | Satu sistem kategori |
| 13 | **Migrasi `courses.prereq_json` ke pivot table** | Normalisasi proper |

### 🟢 Prioritas Rendah (Nice-to-have)

| # | Aksi | Impact |
|---|------|--------|
| 14 | Drop tabel Telescope di production | Bersihkan tabel dev-only |
| 15 | Konsolidasi `course_tag_pivot` + `taggables` | Satu polymorphic tag system |
| 16 | Clean up `audit_logs` internal redundant columns | Schema hygiene |
| 17 | Migrasi 18+ kolom pendukung dari varchar → enum | Consistency |
| 18 | Tambahkan CHECK constraint pada kolom tanpa validasi | Data integrity |

---

## Estimasi Impact

```
Tabel bisa di-drop:     ~8-10 tabel (dari 98 → ~88)
Kolom bisa di-drop:     ~10 kolom
Kolom migrasi enum:     ~35 kolom  
Storage savings:        Moderate (varchar(255) → enum = ~200 bytes/row savings per kolom)
Query performance:      Improved (enum indexing lebih efisien dari varchar)
Data integrity:         Significantly improved (native enum > CHECK constraint)
```
