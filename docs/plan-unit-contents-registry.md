# Plan: `unit_contents` Registry Table — Solusi Anti-Collision ID

## Problem Statement

Lesson, Assignment, dan Quiz masing-masing punya auto-increment ID di tabel terpisah.
Ini menyebabkan:

1. **ID collision**: Lesson ID 5, Assignment ID 5, Quiz ID 5 = 3 entitas berbeda
2. **Order collision**: Di unit yang sama, lesson `order=2` dan assignment `order=2` bisa ada bersamaan
3. **Query fragmented**: `PrerequisiteService` harus query 3 tabel, merge manual, lalu `sortBy('order')`
4. **Bug-prone**: Bug sebelumnya terjadi karena quiz ID dipakai untuk query di tabel assignment

---

## Solusi: Tabel `unit_contents` (Polymorphic Registry)

### Schema

```sql
CREATE TABLE unit_contents (
    id              BIGSERIAL PRIMARY KEY,
    unit_id         BIGINT NOT NULL REFERENCES units(id) ON DELETE CASCADE,
    contentable_type VARCHAR(50) NOT NULL,  -- 'lesson', 'assignment', 'quiz'
    contentable_id  BIGINT NOT NULL,
    "order"         INTEGER NOT NULL DEFAULT 0,
    created_at      TIMESTAMP DEFAULT NOW(),
    updated_at      TIMESTAMP DEFAULT NOW(),

    UNIQUE (unit_id, "order"),                              -- satu order per unit
    UNIQUE (contentable_type, contentable_id),              -- satu entry per content item
    INDEX idx_unit_contents_unit_order (unit_id, "order")
);
```

### Apa yang berubah

| Aspek | Sebelum | Sesudah |
|---|---|---|
| Ordering | `order` di masing-masing tabel (bisa collision) | `order` di `unit_contents` (unique per unit) |
| Content lookup | Query 3 tabel + merge | Query 1 tabel `unit_contents` + eager load |
| Prerequisite check | `getUnitContentBeforeAssignment()` manual merge | `UnitContent::where('unit_id', X)->where('order', '<', Y)` |
| ID reference | `type: 'quiz', id: 5` (bisa collision) | `unit_content_id: 42` (globally unique) ATAU `type: 'quiz', id: 5` tetap valid karena dipakai bersama type |

---

## Tahapan Implementasi

### Phase 1: Migration & Model (BE)

**File baru:**

1. **Migration**: `create_unit_contents_table.php`
   ```php
   Schema::create('unit_contents', function (Blueprint $table) {
       $table->id();
       $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
       $table->string('contentable_type', 50); // 'lesson', 'assignment', 'quiz'
       $table->unsignedBigInteger('contentable_id');
       $table->integer('order')->default(0);
       $table->timestamps();

       $table->unique(['unit_id', 'order']);
       $table->unique(['contentable_type', 'contentable_id']);
       $table->index(['unit_id', 'order']);
   });
   ```

2. **Data migration** (seeder/command): Populate `unit_contents` dari data existing
   ```php
   // Untuk setiap unit:
   // 1. Ambil semua lessons, assignments, quizzes
   // 2. Sort by existing order
   // 3. Re-assign order berurutan: 1, 2, 3, ... (tanpa gap/collision)
   // 4. Insert ke unit_contents
   ```

3. **Model**: `Modules/Schemes/app/Models/UnitContent.php`
   ```php
   class UnitContent extends Model
   {
       protected $fillable = ['unit_id', 'contentable_type', 'contentable_id', 'order'];

       public function unit(): BelongsTo
       {
           return $this->belongsTo(Unit::class);
       }

       public function contentable(): MorphTo
       {
           return $this->morphTo();
       }

       // Helper: resolve the actual model
       public function getContentModel(): Model
       {
           return $this->contentable;
       }

       // Scope: get content before a certain order
       public function scopeBeforeOrder($query, int $order)
       {
           return $query->where('order', '<', $order);
       }
   }
   ```

4. **Tambah relasi di model existing:**
   ```php
   // Di Lesson, Assignment, Quiz:
   public function unitContent(): MorphOne
   {
       return $this->morphOne(UnitContent::class, 'contentable');
   }

   // Di Unit:
   public function contents(): HasMany
   {
       return $this->hasMany(UnitContent::class)->orderBy('order');
   }
   ```

### Phase 2: Refactor PrerequisiteService

**Sebelum** (query 3 tabel manual):
```php
private function getUnitContentBeforeAssignment(int $unitId, Assignment $assignment): Collection
{
    $content = collect();
    $lessons = Lesson::where('unit_id', $unitId)->orderBy('order')->get();
    $assignments = Assignment::forUnit($unitId)->where('order', '<', $assignment->order)->get();
    $quizzes = Quiz::forUnit($unitId)->where('order', '<', $assignment->order)->get();
    // ... manual merge + sortBy('order')
}
```

**Sesudah** (query 1 tabel):
```php
private function getUnitContentBefore(int $unitId, int $currentOrder): Collection
{
    return UnitContent::where('unit_id', $unitId)
        ->where('order', '<', $currentOrder)
        ->with('contentable')
        ->orderBy('order')
        ->get()
        ->map(fn ($uc) => [
            'type' => $uc->contentable_type,
            'model' => $uc->contentable,
            'order' => $uc->order,
        ]);
}
```

**Methods yang perlu di-refactor:**
- `checkLessonAccess()` → gunakan `UnitContent` untuk cari order
- `checkAssignmentAccess()` → gunakan `UnitContent::where('order', '<', $assignmentOrder)`
- `checkQuizAccess()` → gunakan `UnitContent::where('order', '<', $quizOrder)`
- `getUnitContentBeforeAssignment()` → diganti `getUnitContentBefore()`
- `getUnitContentBeforeQuiz()` → diganti `getUnitContentBefore()`
- `getUnitIncompleteness()` → query via `UnitContent` bukan 3 tabel
- `getUnitContentOrder()` → query langsung dari `UnitContent`
- `getUnitProgress()` → query via `UnitContent`
- `isUnitCompleted()` → query via `UnitContent`

### Phase 3: Update Create/Update/Delete Logic

Setiap kali lesson/assignment/quiz dibuat, diupdate, atau dihapus, `unit_contents` harus di-sync:

**Service/Observer:**
```php
class UnitContentSyncService
{
    // Dipanggil saat create lesson/assignment/quiz
    public function register(string $type, int $contentId, int $unitId, ?int $order = null): UnitContent
    {
        $order = $order ?? $this->getNextOrder($unitId);
        return UnitContent::create([
            'unit_id' => $unitId,
            'contentable_type' => $type,
            'contentable_id' => $contentId,
            'order' => $order,
        ]);
    }

    // Dipanggil saat reorder
    public function reorder(int $unitId, array $orderedIds): void
    {
        // $orderedIds = [unit_content_id => new_order, ...]
        DB::transaction(function () use ($unitId, $orderedIds) {
            foreach ($orderedIds as $id => $order) {
                UnitContent::where('id', $id)
                    ->where('unit_id', $unitId)
                    ->update(['order' => $order]);
            }
        });
    }

    // Dipanggil saat delete content
    public function unregister(string $type, int $contentId): void
    {
        UnitContent::where('contentable_type', $type)
            ->where('contentable_id', $contentId)
            ->delete();
    }

    private function getNextOrder(int $unitId): int
    {
        return (UnitContent::where('unit_id', $unitId)->max('order') ?? 0) + 1;
    }
}
```

**Yang perlu diupdate:**
- `LessonController@store/update/destroy`
- `AssignmentController@store/update/destroy` (via `AssignmentService`)
- `QuizController@store/update/destroy` (via `QuizService`)

### Phase 4: Update EnrichmentServices

```php
// AssignmentEnrichmentService & QuizEnrichmentService
// Ganti order dari model langsung ke UnitContent order:

$unitContent = UnitContent::where('contentable_type', 'assignment')
    ->where('contentable_id', $assignment->id)
    ->first();

$order = $unitContent?->order ?? $assignment->order; // fallback ke legacy
```

### Phase 5: (Optional) Remove legacy `order` column

Setelah semua service sudah pakai `unit_contents.order`:

```php
// Migration terakhir:
Schema::table('lessons', fn ($t) => $t->dropColumn('order'));
Schema::table('assignments', fn ($t) => $t->dropColumn('order'));
Schema::table('quizzes', fn ($t) => $t->dropColumn('order'));
```

> ⚠️ Phase ini hanya dilakukan SETELAH semua code sudah 100% migrasi ke `unit_contents`.
> Selama transisi, `order` di model tetap di-sync sebagai fallback.

---

## File yang Terdampak

### BE - Harus diubah:
| File | Perubahan |
|---|---|
| `Modules/Schemes/database/migrations/` | New: `create_unit_contents_table.php` |
| `Modules/Schemes/app/Models/UnitContent.php` | **New model** |
| `Modules/Schemes/app/Models/Unit.php` | Tambah `contents()` relation |
| `Modules/Schemes/app/Models/Lesson.php` | Tambah `unitContent()` morphOne |
| `Modules/Learning/app/Models/Assignment.php` | Tambah `unitContent()` morphOne |
| `Modules/Learning/app/Models/Quiz.php` | Tambah `unitContent()` morphOne |
| `Modules/Schemes/app/Services/PrerequisiteService.php` | **Refactor besar** — query via UnitContent |
| `Modules/Schemes/app/Services/UnitContentSyncService.php` | **New service** — sync registry |
| `Modules/Learning/app/Services/Support/AssignmentEnrichmentService.php` | Gunakan UnitContent order |
| `Modules/Learning/app/Services/Support/QuizEnrichmentService.php` | Gunakan UnitContent order |
| `Modules/Schemes/app/Http/Controllers/LessonController.php` | Sync ke UnitContent saat create/delete |
| `Modules/Learning/app/Http/Controllers/AssignmentController.php` | Sync ke UnitContent saat create/delete |
| `Modules/Learning/app/Http/Controllers/QuizController.php` | Sync ke UnitContent saat create/delete |
| `Modules/Learning/database/seeders/ComprehensiveAssessmentSeeder.php` | Populate unit_contents |
| `Modules/Learning/database/seeders/SequentialProgressSeeder.php` | Query via unit_contents |
| `Modules/Schemes/database/seeders/LearningContentSeeder.php` | Populate unit_contents |

### FE - Tidak ada breaking change
- API response tetap sama (`id`, `type`, `order`)
- FE bisa optionally pakai `unit_content_id` kalau BE expose

---

## Estimasi Effort

| Phase | Effort | Risk |
|---|---|---|
| Phase 1: Migration & Model | ~2 jam | Low |
| Phase 2: Refactor PrerequisiteService | ~3 jam | Medium (core logic) |
| Phase 3: Sync logic di Controllers | ~2 jam | Low |
| Phase 4: Update EnrichmentServices | ~1 jam | Low |
| Phase 5: Remove legacy order (optional) | ~1 jam | Low (kalau phase 1-4 sudah stable) |
| **Total** | **~9 jam** | |

---

## Keuntungan

1. **Zero ID collision** — setiap content item punya unique `unit_content_id`
2. **Zero order collision** — `UNIQUE(unit_id, order)` di database level
3. **Single-query prerequisite** — `PrerequisiteService` query 1 tabel bukan 3
4. **Centralized ordering** — reorder cukup update `unit_contents`, tidak perlu update 3 tabel
5. **Future-proof** — kalau ada content type baru (misal "discussion"), tinggal tambah type
6. **No FE breaking change** — API contract tidak berubah
