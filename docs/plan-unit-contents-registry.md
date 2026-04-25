# Plan: `unit_contents` Registry Table — Solusi Anti-Collision ID

## Problem Statement

Lesson, Assignment, dan Quiz masing-masing punya auto-increment ID di tabel terpisah.
Ini menyebabkan:

1. **ID collision**: Lesson ID 5, Assignment ID 5, Quiz ID 5 = 3 entitas berbeda
2. **Order collision**: Di unit yang sama, lesson `order=2` dan assignment `order=2` bisa ada bersamaan
3. **Query fragmented**: `PrerequisiteService` harus query 3 tabel, merge manual, lalu `sortBy('order')`
4. **Bug-prone**: Bug sebelumnya terjadi karena quiz ID dipakai untuk query di tabel assignment
5. **`ContentMetadataService::getContentMetadataByIdOnly()`** query 3 tabel by ID, pick yang terbaru updated — **broken by design** jika ID bertabrakan
6. **Logic duplication**: `getNextOrderForUnit()` di-copy-paste di 3 service (`LessonOrderingProcessor`, `AssignmentService`, `QuizService`)
7. **Hardcoded passing threshold** `max_score * 0.6` di `UnitService::getContents()` line 466 — bug yang sama dengan yang sudah difix di `PrerequisiteService`

---

## Existing Code yang Terlibat (Audit Lengkap)

### Content Creation (3x `getNextOrderForUnit` duplikasi)
- `Modules/Schemes/app/Services/Support/LessonOrderingProcessor.php` — `getNextOrderForUnit()` line 43-50
- `Modules/Learning/app/Services/AssignmentService.php` — `getNextOrderForUnit()` line 144-151
- `Modules/Learning/app/Services/QuizService.php` — `getNextOrderForUnit()` line 81-88
- `Modules/Schemes/app/Services/ContentService.php` — delegates ke services di atas
- `Modules/Schemes/app/Services/UnitService.php` — `createContentElement()` line 657-694

### Content Ordering & Listing
- `Modules/Schemes/app/Services/UnitService.php` — `getContentOrder()` line 612-655 (query 3 tabel + merge)
- `Modules/Schemes/app/Services/UnitService.php` — `getContents()` line 294-500+ (query 3 tabel + merge + enrichment)
- `Modules/Schemes/app/Services/UnitService.php` — `reorderContent()` line 696-724 (update 3 tabel dalam transaction)

### Prerequisite Checking
- `Modules/Schemes/app/Services/PrerequisiteService.php` — `getUnitContentBeforeAssignment()`, `getUnitContentBeforeQuiz()`, `getUnitIncompleteness()`, `getUnitContentOrder()`, `isUnitCompleted()`, `getUnitProgress()` — semua query 3 tabel manual

### Content Metadata (ID COLLISION BUG)
- `Modules/Schemes/app/Services/ContentMetadataService.php` — `getContentMetadataByIdOnly()` line 73-163
  - Query Lesson::find($id), Assignment::find($id), Quiz::find($id) — jika >1 ketemu, pick yang terbaru updated
  - **BUG**: Jika lesson ID 5 dan quiz ID 5 ada, bisa return content yang salah

### Reorder
- `Modules/Schemes/app/Http/Controllers/UnitController.php` — `reorderContent()` endpoint sudah ada
- `Modules/Schemes/app/Http/Requests/ReorderUnitContentRequest.php` — validates `content.*.type` + `content.*.id`
- Route: `PUT courses/{course:slug}/units/{unit:slug}/content-order`

### Enrichment
- `Modules/Learning/app/Services/Support/AssignmentEnrichmentService.php` — uses `order` from model
- `Modules/Learning/app/Services/Support/QuizEnrichmentService.php` — uses `order` from model

### Seeders
- `Modules/Learning/database/seeders/ComprehensiveAssessmentSeeder.php` — creates assignments/quizzes with `order`
- `Modules/Learning/database/seeders/SequentialProgressSeeder.php` — queries content by `order`
- `Modules/Schemes/database/seeders/` — creates lessons with `order`

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
| Reorder | Update 3 tabel dalam transaction | Update 1 tabel `unit_contents` |
| Content metadata by ID | Query 3 tabel, pick terbaru (bug) | `UnitContent::find($id)` — unambiguous |
| `getNextOrderForUnit()` | 3x duplikasi di 3 service | 1x di `UnitContentSyncService` |
| ID reference | `type: 'quiz', id: 5` (bisa collision) | `unit_content_id: 42` (globally unique) ATAU `type + id` tetap valid |

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
   // 2. Sort by existing order (tiebreak: lesson > assignment > quiz, lalu by id)
   // 3. Re-assign order berurutan: 1, 2, 3, ... (tanpa gap/collision)
   // 4. Insert ke unit_contents
   // 5. Sync order kembali ke model asli (lessons.order, assignments.order, quizzes.order)
   ```

3. **Model**: `Modules/Schemes/app/Models/UnitContent.php`
   ```php
   class UnitContent extends Model
   {
       protected $fillable = ['unit_id', 'contentable_type', 'contentable_id', 'order'];

       // Morph map: gunakan short alias bukan FQCN
       // Harus register di AppServiceProvider:
       // Relation::enforceMorphMap([
       //     'lesson' => Lesson::class,
       //     'assignment' => Assignment::class,
       //     'quiz' => Quiz::class,
       // ]);

       public function unit(): BelongsTo
       {
           return $this->belongsTo(Unit::class);
       }

       public function contentable(): MorphTo
       {
           return $this->morphTo();
       }

       public function scopeBeforeOrder($query, int $order)
       {
           return $query->where('order', '<', $order);
       }

       public function scopeForUnit($query, int $unitId)
       {
           return $query->where('unit_id', $unitId);
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

5. **Register morph map** di `AppServiceProvider` atau `SchemeServiceProvider`:
   ```php
   Relation::enforceMorphMap([
       'lesson' => \Modules\Schemes\Models\Lesson::class,
       'assignment' => \Modules\Learning\Models\Assignment::class,
       'quiz' => \Modules\Learning\Models\Quiz::class,
   ]);
   ```

### Phase 2: `UnitContentSyncService` (New — Centralized)

Menggantikan 3x `getNextOrderForUnit()` yang duplikat.

```php
class UnitContentSyncService
{
    public function register(string $type, int $contentId, int $unitId, ?int $order = null): UnitContent
    {
        return DB::transaction(function () use ($type, $contentId, $unitId, $order) {
            $order = $order ?? $this->getNextOrder($unitId);
            $uc = UnitContent::create([
                'unit_id' => $unitId,
                'contentable_type' => $type,
                'contentable_id' => $contentId,
                'order' => $order,
            ]);
            // Sync order ke model asli
            $this->syncOrderToModel($type, $contentId, $order);
            return $uc;
        });
    }

    public function reorder(int $unitId, array $contentOrder): array
    {
        // $contentOrder = [['type' => 'lesson', 'id' => 1], ['type' => 'quiz', 'id' => 3], ...]
        return DB::transaction(function () use ($unitId, $contentOrder) {
            // Temporary: set semua order ke negatif dulu (hindari unique constraint)
            UnitContent::where('unit_id', $unitId)
                ->update(['order' => DB::raw('-1 * id')]);

            foreach ($contentOrder as $index => $item) {
                $newOrder = $index + 1;
                UnitContent::where('unit_id', $unitId)
                    ->where('contentable_type', $item['type'])
                    ->where('contentable_id', $item['id'])
                    ->update(['order' => $newOrder]);
                // Sync ke model asli
                $this->syncOrderToModel($item['type'], $item['id'], $newOrder);
            }

            return UnitContent::where('unit_id', $unitId)
                ->orderBy('order')
                ->get()
                ->toArray();
        });
    }

    public function unregister(string $type, int $contentId): void
    {
        $uc = UnitContent::where('contentable_type', $type)
            ->where('contentable_id', $contentId)
            ->first();

        if ($uc) {
            $unitId = $uc->unit_id;
            $uc->delete();
            // Reorder remaining items (close gap)
            $this->reindexUnit($unitId);
        }
    }

    public function getNextOrder(int $unitId): int
    {
        return (UnitContent::where('unit_id', $unitId)->max('order') ?? 0) + 1;
    }

    private function reindexUnit(int $unitId): void
    {
        $items = UnitContent::where('unit_id', $unitId)->orderBy('order')->get();
        // Temporary negative to avoid unique constraint
        UnitContent::where('unit_id', $unitId)->update(['order' => DB::raw('-1 * id')]);
        foreach ($items->values() as $index => $item) {
            $item->update(['order' => $index + 1]);
            $this->syncOrderToModel($item->contentable_type, $item->contentable_id, $index + 1);
        }
    }

    private function syncOrderToModel(string $type, int $id, int $order): void
    {
        match ($type) {
            'lesson' => \Modules\Schemes\Models\Lesson::where('id', $id)->update(['order' => $order]),
            'assignment' => \Modules\Learning\Models\Assignment::where('id', $id)->update(['order' => $order]),
            'quiz' => \Modules\Learning\Models\Quiz::where('id', $id)->update(['order' => $order]),
        };
    }
}
```

### Phase 3: Refactor Services yang Mengelola Content

#### 3a. `LessonOrderingProcessor` — create/delete
- **`create()`**: Setelah create lesson, panggil `UnitContentSyncService::register('lesson', $lesson->id, $unitId)`
- **`delete()`**: Panggil `UnitContentSyncService::unregister('lesson', $lesson->id)` — auto reindex
- **Hapus `getNextOrderForUnit()`** — delegasi ke `UnitContentSyncService::getNextOrder()`

#### 3b. `AssignmentService` — create/delete
- **`create()`**: Setelah create assignment, panggil `UnitContentSyncService::register('assignment', ...)`
- **`delete()`**: Panggil `UnitContentSyncService::unregister('assignment', ...)`
- **Hapus `getNextOrderForUnit()`** — delegasi ke `UnitContentSyncService`

#### 3c. `QuizService` — create/delete
- **`create()`**: Setelah create quiz, panggil `UnitContentSyncService::register('quiz', ...)`
- **`delete()`**: Panggil `UnitContentSyncService::unregister('quiz', ...)`
- **Hapus `getNextOrderForUnit()`** — delegasi ke `UnitContentSyncService`

#### 3d. `ContentService::createContent()` — Tidak perlu ubah
- Sudah delegates ke LessonService/AssignmentService/QuizService yang masing-masing akan memanggil sync

#### 3e. `UnitService::createContentElement()` — Tidak perlu ubah
- Sudah delegates ke services

#### 3f. `UnitService::reorderContent()` — **REFACTOR**
```php
// Sebelum: update 3 tabel manual
// Sesudah: delegate ke UnitContentSyncService::reorder()
public function reorderContent(Unit $unit, array $contentOrder): array
{
    return app(UnitContentSyncService::class)->reorder($unit->id, $contentOrder);
}
```

### Phase 4: Refactor PrerequisiteService

**Methods yang perlu di-refactor:**

| Method | Sebelum | Sesudah |
|---|---|---|
| `getUnitContentBeforeAssignment()` | Query 3 tabel + merge | `UnitContent::forUnit($unitId)->beforeOrder($order)->with('contentable')` |
| `getUnitContentBeforeQuiz()` | Query 3 tabel + merge | Same — digabung jadi `getUnitContentBefore()` |
| `getUnitIncompleteness()` | Query 3 tabel per unit | `UnitContent::forUnit($unitId)->with('contentable')` |
| `getUnitContentOrder()` | Query 3 tabel + merge | `UnitContent::forUnit($unitId)->with('contentable')` |
| `getUnitProgress()` | Query 3 tabel | `UnitContent::forUnit($unitId)->with('contentable')` |
| `isUnitCompleted()` | Query 3 tabel | `UnitContent::forUnit($unitId)->with('contentable')` |
| `checkLessonAccess()` | Query lessons only | `UnitContent::forUnit($unitId)->beforeOrder($lessonOrder)` |

**Refactored code:**
```php
private function getUnitContentBefore(int $unitId, int $currentOrder): Collection
{
    return UnitContent::where('unit_id', $unitId)
        ->where('order', '<', $currentOrder)
        ->with('contentable')
        ->orderBy('order')
        ->get()
        ->filter(fn ($uc) => $uc->contentable !== null) // handle soft-deleted/unpublished
        ->map(fn ($uc) => [
            'type' => $uc->contentable_type,
            'model' => $uc->contentable,
            'order' => $uc->order,
        ]);
}
```

### Phase 5: Refactor `UnitService::getContents()` & `getContentOrder()`

#### 5a. `getContentOrder()` — simplify
```php
public function getContentOrder(Unit $unit): array
{
    return UnitContent::where('unit_id', $unit->id)
        ->with('contentable:id,title,status')
        ->orderBy('order')
        ->get()
        ->map(fn ($uc) => [
            'type' => $uc->contentable_type,
            'id' => $uc->contentable_id,
            'title' => $uc->contentable?->title,
            'order' => $uc->order,
            'status' => $uc->contentable?->status,
        ])
        ->toArray();
}
```

#### 5b. `getContents()` — refactor dari query 3 tabel ke UnitContent
- Query `UnitContent::where('unit_id', $unit->id)->with('contentable')->orderBy('order')`
- Iterate over single collection
- **FIX BUG**: line 466 `$item->max_score * 0.6` → gunakan `$item->passing_grade`

### Phase 6: Fix `ContentMetadataService`

#### 6a. `getContentMetadataByIdOnly()` — **FIX ID COLLISION BUG**

```php
// Sebelum (BROKEN):
$lesson = Lesson::find($contentId);
$assignment = Assignment::find($contentId);
$quiz = Quiz::find($contentId);
// pick yang terbaru updated... SALAH jika ID overlap

// Sesudah (CORRECT):
public function getContentMetadataByIdOnly(int $unitContentId): array
{
    $uc = UnitContent::with('contentable')->findOrFail($unitContentId);
    return $this->getContentMetadata($uc->contentable_id, $uc->contentable_type);
}
```

> ⚠️ **FE BREAKING CHANGE**: Jika FE memanggil endpoint ini tanpa `type`,
> maka FE harus diubah untuk selalu kirim `type` parameter,
> ATAU backend tetap support legacy fallback tapi pakai UnitContent lookup.

#### 6b. `getContentMetadata()` — tidak perlu ubah (sudah terima `type` + `id`)

### Phase 7: Update EnrichmentServices

```php
// AssignmentEnrichmentService & QuizEnrichmentService
// Eager load unitContent pada paginator:
$paginator->load(['unitContent']);

// Gunakan order dari unitContent:
'order' => $item->unitContent?->order ?? $item->order,
```

### Phase 8: Update Seeders

- **`ComprehensiveAssessmentSeeder`**: Setelah insert assignment/quiz, juga insert ke `unit_contents`
- **`SequentialProgressSeeder`**: Query content via `unit_contents` bukan 3 tabel terpisah
- **Lesson seeders**: Setelah insert lesson, juga insert ke `unit_contents`

### Phase 9: (Optional) Remove legacy `order` column

Setelah SEMUA code sudah pakai `unit_contents.order` dan `syncOrderToModel` terbukti reliable:

```php
Schema::table('lessons', fn ($t) => $t->dropColumn('order'));
Schema::table('assignments', fn ($t) => $t->dropColumn('order'));
Schema::table('quizzes', fn ($t) => $t->dropColumn('order'));
```

> ⚠️ Phase ini hanya dilakukan SETELAH semua code sudah 100% migrasi ke `unit_contents`.
> Selama transisi, `syncOrderToModel` menjaga kedua source tetap in-sync.

---

## Edge Cases yang Harus Di-handle

### 1. SoftDeletes
- Assignment dan Quiz menggunakan `SoftDeletes`
- Saat soft-delete: **JANGAN hapus** `unit_contents` entry — biarkan tetap ada
- Saat restore: entry masih ada, order tetap
- Saat force-delete: hapus `unit_contents` entry via `UnitContentSyncService::unregister()`
- Alternative: tambah observer `deleting`/`restoring` events

### 2. PublishedOnlyScope
- `Assignment` dan `Quiz` punya `PublishedOnlyScope` global scope
- Saat `UnitContent::with('contentable')` di-load untuk student:
  - `contentable` bisa `null` jika item tidak published
  - Harus di-handle: `->filter(fn ($uc) => $uc->contentable !== null)`
- Untuk instructor/admin: scope tidak aktif, semua item visible

### 3. Unit Deletion
- `units.onDelete('cascade')` → `unit_contents` juga terhapus (FK cascade)
- Tapi Assignment/Quiz juga cascade delete via `unit_id` FK
- Tidak perlu handling khusus

### 4. Move Content Between Units
- Saat ini belum ada fitur ini
- Jika nanti ada: update `unit_contents.unit_id` + reindex kedua unit
- Service method: `UnitContentSyncService::move($type, $contentId, $newUnitId)`

### 5. UNIQUE constraint conflict saat reorder
- `UNIQUE(unit_id, order)` bisa conflict saat swap order
- Solusi: set order ke negatif dulu (temporary), lalu set ke final value
- Sudah di-handle di `UnitContentSyncService::reorder()` dan `reindexUnit()`

### 6. Concurrent reorder requests
- Gunakan `DB::transaction()` + `SELECT ... FOR UPDATE` pada `unit_contents` rows
- Atau gunakan pessimistic locking: `UnitContent::where('unit_id', $unitId)->lockForUpdate()`

---

## File yang Terdampak (Lengkap)

### BE - File Baru:
| File | Deskripsi |
|---|---|
| `Modules/Schemes/database/migrations/xxxx_create_unit_contents_table.php` | Migration |
| `Modules/Schemes/database/migrations/xxxx_populate_unit_contents_from_existing.php` | Data migration |
| `Modules/Schemes/app/Models/UnitContent.php` | Model |
| `Modules/Schemes/app/Services/UnitContentSyncService.php` | Centralized sync service |

### BE - File yang Diubah:
| File | Perubahan |
|---|---|
| `Modules/Schemes/app/Models/Unit.php` | Tambah `contents()` HasMany relation |
| `Modules/Schemes/app/Models/Lesson.php` | Tambah `unitContent()` MorphOne relation |
| `Modules/Learning/app/Models/Assignment.php` | Tambah `unitContent()` MorphOne relation |
| `Modules/Learning/app/Models/Quiz.php` | Tambah `unitContent()` MorphOne relation |
| `Modules/Schemes/app/Providers/SchemeServiceProvider.php` | Register morph map |
| `Modules/Schemes/app/Services/PrerequisiteService.php` | **Refactor besar** — semua method query via UnitContent |
| `Modules/Schemes/app/Services/UnitService.php` | Refactor `getContents()`, `getContentOrder()`, `reorderContent()` + **fix bug 0.6** |
| `Modules/Schemes/app/Services/ContentMetadataService.php` | **Fix ID collision bug** di `getContentMetadataByIdOnly()` |
| `Modules/Schemes/app/Services/Support/LessonOrderingProcessor.php` | Hapus `getNextOrderForUnit()`, panggil sync service |
| `Modules/Learning/app/Services/AssignmentService.php` | Hapus `getNextOrderForUnit()`, panggil sync service |
| `Modules/Learning/app/Services/QuizService.php` | Hapus `getNextOrderForUnit()`, panggil sync service |
| `Modules/Learning/app/Services/Support/AssignmentEnrichmentService.php` | Gunakan UnitContent order |
| `Modules/Learning/app/Services/Support/QuizEnrichmentService.php` | Gunakan UnitContent order |
| `Modules/Schemes/app/Http/Requests/ReorderUnitContentRequest.php` | Tidak berubah (masih `type + id`) |
| `Modules/Learning/database/seeders/ComprehensiveAssessmentSeeder.php` | Populate unit_contents |
| `Modules/Learning/database/seeders/SequentialProgressSeeder.php` | Query via unit_contents |

### FE - Potential Change:
| File | Perubahan |
|---|---|
| Endpoint `getContentMetadata` tanpa `type` param | Harus kirim `type` atau gunakan `unit_content_id` |
| Drag-and-drop reorder | Bisa pakai `unit_content_id` langsung (lebih simple) |

---

## Immediate Bug Fixes (SEBELUM unit_contents implementation)

Bugs yang sudah difix:
- [x] `PrerequisiteService::isAssignmentPassed()` — hardcoded 0.6 → `passing_grade`
- [x] `QuizEnrichmentService` — query `Submission` → `QuizSubmission`
- [x] `is_completed` semantic — `prerequisiteCheck['accessible']` → `submissionData['is_completed']`

Bug yang BELUM difix (harus segera):
- [ ] `UnitService::getContents()` line 466 — `$item->max_score * 0.6` → `$item->passing_grade`

---

## Estimasi Effort (Updated)

| Phase | Effort | Risk |
|---|---|---|
| Phase 1: Migration & Model | ~2 jam | Low |
| Phase 2: UnitContentSyncService | ~2 jam | Low |
| Phase 3: Refactor Create/Delete Services | ~2 jam | Medium |
| Phase 4: Refactor PrerequisiteService | ~3 jam | Medium (core logic) |
| Phase 5: Refactor UnitService | ~2 jam | Medium |
| Phase 6: Fix ContentMetadataService | ~1 jam | Low |
| Phase 7: Update EnrichmentServices | ~1 jam | Low |
| Phase 8: Update Seeders | ~1 jam | Low |
| Phase 9: Remove legacy order (optional) | ~1 jam | Low |
| **Total** | **~15 jam** | |

---

## Keuntungan

1. **Zero ID collision** — setiap content item punya unique `unit_content_id`
2. **Zero order collision** — `UNIQUE(unit_id, order)` di database level
3. **Single-query prerequisite** — `PrerequisiteService` query 1 tabel bukan 3
4. **Centralized ordering** — reorder cukup update `unit_contents`, bukan 3 tabel
5. **Fix ContentMetadataService bug** — no more ambiguous ID lookup
6. **DRY** — 3x `getNextOrderForUnit()` jadi 1x di `UnitContentSyncService`
7. **Drag-and-drop ready** — reorder endpoint tinggal kirim array of `unit_content_id`
8. **Future-proof** — content type baru (discussion, project) tinggal tambah morph type
9. **Backward compatible** — `syncOrderToModel` menjaga legacy `order` tetap in-sync
