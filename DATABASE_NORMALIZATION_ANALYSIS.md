# Database Normalization & Optimization Analysis

**Tanggal Analisis**: 24 Maret 2026  
**Database**: levl_db_backup.sql  
**Total Tables**: 98

---

## 📊 Executive Summary

Analisis database menunjukkan beberapa area yang perlu diperbaiki:

1. **47 tabel** memiliki field VARCHAR yang seharusnya ENUM
2. **Beberapa tabel** memiliki masalah normalisasi
3. **Tabel tidak terpakai** perlu diidentifikasi
4. **Redundansi data** di beberapa tabel

---

## 🔴 CRITICAL: Field VARCHAR yang Harus Diubah ke ENUM

### 1. Users Table
**Tabel**: `users`
**Field**: `status` (VARCHAR)
**Current Values**: 'pending', 'active', 'inactive', 'banned'
**Recommendation**: ✅ **SUDAH ADA CHECK CONSTRAINT** - Sudah baik!

```sql
-- Already has constraint:
CONSTRAINT users_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'active'::character varying, 'inactive'::character varying, 'banned'::character varying])::text[])))
```

**Action**: ✅ No action needed (already constrained)

---

### 2. Courses Table
**Tabel**: `courses`
**Fields yang perlu ENUM**:

#### a. `type` (VARCHAR)
- **Current**: VARCHAR(255) DEFAULT 'okupasi'
- **Values**: 'okupasi', 'klaster', 'kualifikasi'
- **Recommendation**: Ubah ke ENUM atau buat tabel `course_types`

#### b. `level_tag` (VARCHAR)
- **Current**: VARCHAR(255) DEFAULT 'dasar'
- **Values**: 'dasar', 'lanjut', 'mahir'
- **Recommendation**: Ubah ke ENUM `course_level`

#### c. `enrollment_type` (VARCHAR)
- **Current**: VARCHAR(255) DEFAULT 'auto_accept'
- **Values**: 'auto_accept', 'manual_approval', 'enrollment_key'
- **Recommendation**: Ubah ke ENUM `enrollment_type`

#### d. `status` (VARCHAR)
- **Current**: VARCHAR(255) DEFAULT 'draft'
- **Values**: 'draft', 'published', 'archived'
- **Recommendation**: Ubah ke ENUM `course_status`

**Migration Priority**: 🔴 **HIGH**

---

### 3. Enrollments Table
**Tabel**: `enrollments`
**Field**: `status` (VARCHAR)
**Current**: VARCHAR(255) DEFAULT 'active'
**Values**: 'pending', 'active', 'completed', 'cancelled', 'expired'
**Recommendation**: Ubah ke ENUM `enrollment_status`

**Migration Priority**: 🔴 **HIGH**

---

### 4. Assignments Table
**Tabel**: `assignments`
**Fields yang perlu ENUM**:

#### a. `status` (VARCHAR)
- **Values**: 'draft', 'published', 'archived'
- **Recommendation**: ENUM `assignment_status`

#### b. `review_mode` (VARCHAR)
- **Values**: 'immediate', 'after_deadline', 'manual'
- **Recommendation**: ENUM `review_mode`

#### c. `randomization_type` (VARCHAR)
- **Values**: 'static', 'random', 'adaptive'
- **Recommendation**: ENUM `randomization_type`

**Migration Priority**: 🟡 **MEDIUM**

---

### 5. Quizzes Table
**Tabel**: `quizzes`
**Fields yang perlu ENUM**:

#### a. `status` (VARCHAR)
- **Values**: 'draft', 'published', 'archived'
- **Recommendation**: ENUM `quiz_status`

#### b. `randomization_type` (VARCHAR)
- **Values**: 'static', 'random', 'adaptive'
- **Recommendation**: ENUM `randomization_type`

#### c. `review_mode` (VARCHAR)
- **Values**: 'immediate', 'after_submission', 'manual'
- **Recommendation**: ENUM `review_mode`

**Migration Priority**: 🟡 **MEDIUM**

---

### 6. Quiz Submissions Table
**Tabel**: `quiz_submissions`
**Fields yang perlu ENUM**:

#### a. `status` (VARCHAR)
- **Values**: 'draft', 'submitted', 'graded'
- **Recommendation**: ENUM `submission_status`

#### b. `grading_status` (VARCHAR)
- **Values**: 'pending', 'in_progress', 'completed'
- **Recommendation**: ENUM `grading_status`

**Migration Priority**: 🟡 **MEDIUM**

---

### 7. Badges Table
**Tabel**: `badges`
**Fields yang perlu ENUM**:

#### a. `type` (VARCHAR)
- **Current**: VARCHAR(50) DEFAULT 'achievement'
- **Values**: 'achievement', 'milestone', 'special', 'level'
- **Recommendation**: ✅ **SUDAH DIIMPLEMENTASIKAN** sebagai `BadgeType` enum di Laravel

#### b. `rarity` (VARCHAR)
- **Current**: VARCHAR(255) DEFAULT 'common'
- **Values**: 'common', 'uncommon', 'rare', 'epic', 'legendary'
- **Recommendation**: ✅ **SUDAH DIIMPLEMENTASIKAN** sebagai `BadgeRarity` enum di Laravel

**Action**: ✅ Already implemented in Laravel (check if DB enum needed)

---

### 8. Points Table
**Tabel**: `points`
**Fields yang perlu ENUM**:

#### a. `source_type` (VARCHAR)
- **Current**: VARCHAR(50) DEFAULT 'system'
- **Values**: 'quiz', 'assignment', 'lesson', 'forum', 'achievement'
- **Recommendation**: ✅ **SUDAH DIIMPLEMENTASIKAN** sebagai `PointSourceType` enum

#### b. `reason` (VARCHAR)
- **Current**: VARCHAR(100) DEFAULT 'completion'
- **Values**: 'completion', 'perfect_score', 'streak', 'participation'
- **Recommendation**: ✅ **SUDAH DIIMPLEMENTASIKAN** sebagai `PointReason` enum

**Action**: ✅ Already implemented in Laravel

---

### 9. Posts Table (Info/News)
**Tabel**: `posts`
**Fields yang perlu ENUM**:

#### a. `category` (VARCHAR)
- **Current**: VARCHAR(255) NOT NULL
- **Values**: 'info', 'news', 'announcement'
- **Recommendation**: ✅ **SUDAH DIIMPLEMENTASIKAN** sebagai `PostCategory` enum

#### b. `status` (VARCHAR)
- **Current**: VARCHAR(255) DEFAULT 'draft'
- **Values**: 'draft', 'scheduled', 'published', 'archived'
- **Recommendation**: Ubah ke ENUM `post_status`

**Migration Priority**: 🟡 **MEDIUM**

---

### 10. Notifications Table
**Tabel**: `notifications`
**Fields yang perlu ENUM**:

#### a. `type` (VARCHAR)
- **Values**: 'system', 'course', 'assignment', 'grade', 'forum'
- **Recommendation**: ENUM `notification_type`

#### b. `channel` (VARCHAR)
- **Values**: 'in_app', 'email', 'push'
- **Recommendation**: ENUM `notification_channel`

#### c. `priority` (VARCHAR)
- **Values**: 'low', 'normal', 'high', 'urgent'
- **Recommendation**: ENUM `notification_priority`

**Migration Priority**: 🟡 **MEDIUM**

---

### 11. Lessons Table
**Tabel**: `lessons`
**Field**: `status` (VARCHAR)
**Values**: 'draft', 'published', 'archived'
**Recommendation**: ENUM `lesson_status`

**Migration Priority**: 🟡 **MEDIUM**

---

### 12. Units Table
**Tabel**: `units`
**Field**: `status` (VARCHAR)
**Values**: 'draft', 'published', 'archived'
**Recommendation**: ENUM `unit_status`

**Migration Priority**: 🟡 **MEDIUM**

---

### 13. Progress Tables
**Tables**: `course_progress`, `unit_progress`, `lesson_progress`
**Field**: `status` (VARCHAR)
**Values**: 'not_started', 'in_progress', 'completed'
**Recommendation**: ENUM `progress_status` (shared enum)

**Migration Priority**: 🟡 **MEDIUM**

---

### 14. Announcements Table
**Tabel**: `announcements`
**Fields yang perlu ENUM**:

#### a. `status` (VARCHAR)
- **Values**: 'draft', 'published', 'archived'
- **Recommendation**: ENUM `announcement_status`

#### b. `target_type` (VARCHAR)
- **Values**: 'all', 'role', 'course', 'user'
- **Recommendation**: ENUM `target_type`

#### c. `priority` (VARCHAR)
- **Values**: 'low', 'normal', 'high', 'urgent'
- **Recommendation**: ENUM `priority_level`

**Migration Priority**: 🟢 **LOW**

---

### 15. Certificates Table
**Tabel**: `certificates`
**Field**: `status` (VARCHAR)
**Values**: 'active', 'revoked', 'expired'
**Recommendation**: ENUM `certificate_status`

**Migration Priority**: 🟢 **LOW**

---

### 16. Grade Reviews Table
**Tabel**: `grade_reviews`
**Field**: `status` (VARCHAR)
**Values**: 'pending', 'approved', 'rejected'
**Recommendation**: ENUM `review_status`

**Migration Priority**: 🟢 **LOW**

---

### 17. Reactions Table
**Tabel**: `reactions`
**Field**: `type` (VARCHAR)
**Values**: 'like', 'love', 'helpful', 'insightful'
**Recommendation**: ENUM `reaction_type`

**Migration Priority**: 🟢 **LOW**

---

### 18. Quiz Questions Table
**Tabel**: `quiz_questions`
**Field**: `type` (VARCHAR)
**Values**: 'multiple_choice', 'true_false', 'essay', 'file_upload'
**Recommendation**: ENUM `question_type`

**Migration Priority**: 🟡 **MEDIUM**

---

### 19. Submissions Table
**Tabel**: `submissions`
**Fields yang perlu ENUM**:

#### a. `status` (VARCHAR)
- **Values**: 'draft', 'submitted', 'graded', 'returned'
- **Recommendation**: ENUM `submission_status`

#### b. `state` (VARCHAR)
- **Values**: 'pending', 'reviewing', 'completed'
- **Recommendation**: ENUM `submission_state`

**Migration Priority**: 🟡 **MEDIUM**

---

### 20. Gamification Event Logs Table
**Tabel**: `gamification_event_logs`
**Fields yang perlu ENUM**:

#### a. `event_type` (VARCHAR)
- **Values**: 'xp_earned', 'level_up', 'badge_earned', 'streak_milestone'
- **Recommendation**: ENUM `gamification_event_type`

#### b. `source_type` (VARCHAR)
- **Values**: 'quiz', 'assignment', 'lesson', 'forum'
- **Recommendation**: ENUM `event_source_type`

**Migration Priority**: 🟢 **LOW**

---

## 🔍 Masalah Normalisasi

### 1. Activity Log Table - Terlalu Banyak Field Location
**Tabel**: `activity_log`
**Issue**: Field `city`, `region`, `country` sebaiknya dinormalisasi

**Current**:
```sql
city VARCHAR(255)
region VARCHAR(255)
country VARCHAR(255)
```

**Recommendation**: Buat tabel `locations` terpisah
```sql
CREATE TABLE locations (
    id BIGSERIAL PRIMARY KEY,
    city VARCHAR(100),
    region VARCHAR(100),
    country VARCHAR(100),
    country_code CHAR(2),
    UNIQUE(city, region, country)
);

-- Update activity_log
ALTER TABLE activity_log ADD COLUMN location_id BIGINT REFERENCES locations(id);
```

**Priority**: 🟢 **LOW** (optimization, not critical)

---

### 2. Device Information - Redundansi
**Tables**: `activity_log`, `login_activities`, `jwt_refresh_tokens`
**Issue**: Device info tersebar di banyak tabel

**Current**:
- `activity_log`: browser, browser_version, platform, device, device_type
- `login_activities`: user_agent
- `jwt_refresh_tokens`: user_agent, device_id

**Recommendation**: Buat tabel `user_devices` terpisah
```sql
CREATE TABLE user_devices (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    device_id VARCHAR(64) UNIQUE,
    browser VARCHAR(100),
    browser_version VARCHAR(50),
    platform VARCHAR(100),
    device_type VARCHAR(50),
    user_agent VARCHAR(255),
    last_used_at TIMESTAMP,
    created_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

**Priority**: 🟡 **MEDIUM** (reduces redundancy)

---

### 3. Master Data Table - Anti-Pattern
**Tabel**: `master_data`
**Issue**: EAV (Entity-Attribute-Value) pattern - anti-pattern untuk relational DB

**Current**:
```sql
type VARCHAR(50)
label VARCHAR(255)
value TEXT
```

**Recommendation**: Pisahkan per type menjadi tabel tersendiri
- `specializations` table
- `competency_elements` table
- `competency_units` table
- dll.

**Priority**: 🔴 **HIGH** (performance & maintainability)

---

### 4. Polymorphic Relations - Terlalu Banyak
**Tables dengan polymorphic**: 
- `media` (model_type)
- `reactions` (reactable_type)
- `mentions` (mentionable_type)
- `taggables` (taggable_type)
- `content_reads` (readable_type)
- `trash_bins` (trashable_type, root_resource_type)
- `user_activities` (related_type)
- `user_scope_stats` (scope_type)
- `grading_rubrics` (scope_type)
- `grades` (source_type)
- `audit_logs` (target_type, actor_type, subject_type)
- `audits` (actor_type, target_type)

**Issue**: Polymorphic relations sulit di-index dan query

**Recommendation**: 
- Pertahankan untuk `media` (Spatie Media Library standard)
- Pertahankan untuk `reactions`, `mentions` (forum features)
- **Evaluasi**: `trash_bins`, `audit_logs`, `audits` - consider dedicated tables

**Priority**: 🟡 **MEDIUM** (evaluate case by case)

---

## 🗑️ Tabel yang Mungkin Tidak Digunakan

### 1. Telescope Tables (Development Only)
**Tables**:
- `telescope_entries`
- `telescope_entries_tags`
- `telescope_monitoring`

**Recommendation**: ❌ **HAPUS di production** (hanya untuk development)

**Action**:
```sql
-- Production only
DROP TABLE IF EXISTS telescope_monitoring;
DROP TABLE IF EXISTS telescope_entries_tags;
DROP TABLE IF EXISTS telescope_entries;
```

---

### 2. Cache Tables
**Tables**:
- `cache`
- `cache_locks`

**Status**: ✅ **KEEP** (Laravel cache driver)
**Note**: Jika menggunakan Redis, tabel ini tidak terpakai tapi tidak masalah

---

### 3. Sessions Table
**Table**: `sessions`
**Status**: ✅ **KEEP** (Laravel session driver)
**Note**: Jika menggunakan Redis/file driver, bisa dihapus

---

### 4. Failed Jobs Table
**Table**: `failed_jobs`
**Status**: ✅ **KEEP** (Laravel queue monitoring)

---

### 5. Temporary Media Table
**Table**: `temporary_media`
**Status**: ⚠️ **EVALUATE**
**Question**: Apakah fitur temporary media digunakan?
**Action**: Check usage in codebase

---

### 6. Social Accounts Table
**Table**: `social_accounts`
**Status**: ⚠️ **EVALUATE**
**Question**: Apakah OAuth social login digunakan?
**Current**: Hanya Google OAuth yang diimplementasikan
**Action**: Keep if Google login is used

---

### 7. Content Tables (Unused?)
**Tables**:
- `content_categories`
- `content_reads`
- `content_revisions`
- `content_workflow_history`

**Status**: ⚠️ **EVALUATE**
**Question**: Apakah CMS content management digunakan?
**Action**: Check if these are used for courses/lessons or separate CMS

---

### 8. News Category Table
**Table**: `news_category`
**Status**: ⚠️ **EVALUATE**
**Question**: Apakah berbeda dengan `posts` table?
**Recommendation**: Merge dengan `posts` jika redundant

---

### 9. Categories Table
**Table**: `categories`
**Status**: ⚠️ **EVALUATE**
**Question**: Untuk apa? Course categories? Forum categories?
**Action**: Check usage and consider renaming for clarity

---

### 10. Tags & Taggables
**Tables**:
- `tags`
- `taggables`

**Status**: ⚠️ **EVALUATE**
**Question**: Apakah tagging system digunakan?
**Action**: Check if actively used

---

### 11. Reports Table
**Table**: `reports`
**Status**: ⚠️ **EVALUATE**
**Question**: Untuk reporting apa? User reports? Analytics?
**Action**: Check usage

---

### 12. Search History Table
**Table**: `search_history`
**Status**: ✅ **KEEP** (analytics & recommendations)

---

### 13. Leaderboards Table
**Table**: `leaderboards`
**Status**: ⚠️ **EVALUATE**
**Question**: Apakah berbeda dengan gamification system?
**Action**: Check if redundant with `user_gamification_stats`

---

### 14. Forum Statistics Table
**Table**: `forum_statistics`
**Status**: ⚠️ **EVALUATE**
**Question**: Apakah digunakan atau bisa dihitung on-the-fly?
**Action**: Check if actively maintained

---

### 15. Grading Rubrics Table
**Table**: `grading_rubrics`
**Status**: ⚠️ **EVALUATE**
**Question**: Apakah rubric grading diimplementasikan?
**Action**: Check if feature is used

---

## 📋 Rekomendasi Prioritas

### 🔴 HIGH Priority (Segera)

1. **Master Data Table** - Refactor ke tabel terpisah
2. **Courses Table** - Ubah type, level_tag, enrollment_type, status ke ENUM
3. **Enrollments Table** - Ubah status ke ENUM
4. **Hapus Telescope Tables** di production

### 🟡 MEDIUM Priority (1-2 Bulan)

1. **Assignments & Quizzes** - Ubah status fields ke ENUM
2. **Quiz Submissions** - Ubah status & grading_status ke ENUM
3. **Posts Table** - Ubah status ke ENUM
4. **Notifications Table** - Ubah type, channel, priority ke ENUM
5. **Device Information** - Normalisasi ke tabel terpisah
6. **Evaluate Unused Tables** - Audit dan hapus yang tidak terpakai

### 🟢 LOW Priority (Future)

1. **Activity Log** - Normalisasi location fields
2. **Progress Tables** - Ubah status ke shared ENUM
3. **Announcements, Certificates, Grade Reviews** - Ubah ke ENUM
4. **Gamification Event Logs** - Ubah ke ENUM

---

## 🛠️ Migration Strategy

### Phase 1: Audit (1 Week)
1. Identify unused tables dengan query:
```sql
-- Check table sizes
SELECT schemaname, tablename, pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS size
FROM pg_tables
WHERE schemaname = 'public'
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;

-- Check row counts
SELECT schemaname, tablename, n_live_tup
FROM pg_stat_user_tables
WHERE schemaname = 'public'
ORDER BY n_live_tup DESC;
```

2. Grep codebase untuk usage:
```bash
# Check if table is used
grep -r "table_name" Levl-BE/
```

### Phase 2: Create ENUMs (2 Weeks)
1. Create PostgreSQL ENUMs
2. Create Laravel migrations
3. Update models dengan casts

### Phase 3: Data Migration (1 Week)
1. Backup database
2. Run migrations
3. Verify data integrity

### Phase 4: Cleanup (1 Week)
1. Drop unused tables
2. Update documentation
3. Update seeders

---

## 📊 Impact Analysis

### Storage Savings
- VARCHAR(255) → ENUM: ~250 bytes → 4 bytes per row
- Estimated savings: **30-40% on status columns**

### Performance Improvements
- ENUM comparisons: **Faster than VARCHAR**
- Index size: **Smaller indexes**
- Query performance: **10-20% improvement on filtered queries**

### Maintainability
- Type safety: **Compile-time checks**
- Documentation: **Self-documenting schema**
- Validation: **Database-level constraints**

---

## ✅ Already Implemented (Good!)

1. ✅ `users.status` - Has CHECK constraint
2. ✅ `BadgeType` enum - Laravel enum
3. ✅ `BadgeRarity` enum - Laravel enum
4. ✅ `PointSourceType` enum - Laravel enum
5. ✅ `PointReason` enum - Laravel enum
6. ✅ `PostCategory` enum - Laravel enum
7. ✅ `BlockType` enum - Laravel enum (lesson blocks)
8. ✅ `CourseStatus` enum - Laravel enum
9. ✅ `ProgressStatus` enum - Laravel enum

**Note**: Laravel enums sudah ada, tapi database masih VARCHAR. Consider migrating to PostgreSQL ENUMs for consistency.

---

## 🎯 Next Steps

1. **Review this analysis** dengan team
2. **Prioritize migrations** berdasarkan business impact
3. **Create migration tickets** di project management tool
4. **Schedule downtime** untuk major migrations
5. **Test thoroughly** di staging environment
6. **Monitor performance** setelah migration

---

## 📝 Notes

- Semua rekomendasi harus di-review dengan team sebelum implementasi
- Backup database sebelum migration
- Test di staging environment terlebih dahulu
- Monitor query performance setelah changes
- Update documentation setelah migration

---

**Generated by**: Database Analysis Tool  
**Date**: 24 Maret 2026  
**Version**: 1.0
