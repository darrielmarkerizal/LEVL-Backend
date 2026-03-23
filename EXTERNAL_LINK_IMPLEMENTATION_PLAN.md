# External Link Implementation Plan
## Lesson Content/Block, Quiz & Assignment

**Tanggal**: 23 Maret 2026  
**Status**: Planning  
**Tujuan**: Menambahkan dukungan link eksternal (Google Drive, YouTube, dll) untuk lesson content/block, quiz, dan assignment

---

## 1. ANALISIS STRUKTUR SAAT INI

### 1.1 Lesson & Lesson Block
**Database**: `lessons` dan `lesson_blocks`

**Lessons Table**:
- `content_type`: enum('markdown', 'video', 'link')
- `content_url`: string (nullable) - sudah ada untuk video/link eksternal
- Sudah mendukung link eksternal di level lesson

**Lesson Blocks Table**:
- `block_type`: enum('text', 'image', 'file', 'embed')
- `content`: longText (nullable)
- `media_url`: string (nullable) - generated dari Spatie Media
- Saat ini hanya mendukung file upload, tidak ada field untuk link eksternal

**Model**: `Modules\Schemes\Models\LessonBlock`
- Menggunakan Spatie MediaLibrary untuk file storage
- Media disimpan di DigitalOcean disk
- Mendukung: image, video, audio, documents

**Validation**: `Modules\Schemes\Http\Requests\LessonBlockRequest`
- Type: text, video, image, file
- Media file required untuk video/image/file
- Max upload: 50MB


### 1.2 Quiz
**Database**: `quizzes`

**Quizzes Table**:
- `title`, `description`: string/text
- `status`: enum('draft', 'published', 'archived')
- Tidak ada field untuk link eksternal
- Menggunakan Spatie Media untuk attachments

**Model**: `Modules\Learning\Models\Quiz`
- Implements HasMedia untuk attachments
- Media collection: 'attachments'
- Accepted types: PDF, Word, Excel, PowerPoint, images

**Validation**: `Modules\Learning\Http\Requests\StoreQuizRequest`
- attachments: array of files
- Tidak ada validasi untuk URL

### 1.3 Assignment
**Database**: `assignments`

**Assignments Table**:
- `title`, `description`: string/text
- `submission_type`: enum('text', 'file', 'mixed')
- `status`: enum('draft', 'published', 'archived')
- Tidak ada field untuk link eksternal
- Menggunakan Spatie Media untuk attachments

**Model**: `Modules\Learning\Models\Assignment`
- Implements HasMedia untuk attachments
- Media collection: 'attachments'
- Accepted types: PDF, Word, Excel, PowerPoint, images, ZIP

**Validation**: `Modules\Learning\Http\Requests\StoreAssignmentRequest`
- attachments: array max 5 files, max 10MB each
- Tidak ada validasi untuk URL

---

## 2. KEBUTUHAN FITUR

### 2.1 Lesson Block - External Links
**Use Cases**:
- Instruktur ingin embed video YouTube
- Instruktur ingin link ke Google Drive file
- Instruktur ingin link ke Google Docs/Sheets
- Instruktur ingin embed Loom video
- Instruktur ingin link ke external resources

**Requirements**:
1. Tambah field `external_url` di lesson_blocks table
2. Tambah block_type baru: 'link', 'youtube', 'drive'
3. Validasi URL format (YouTube, Drive, generic URL)
4. Support untuk embed vs direct link
5. Preview/thumbnail untuk link eksternal
6. Metadata extraction (title, description dari URL)


### 2.2 Quiz - External Links
**Use Cases**:
- Instruktur ingin attach link ke reference material
- Instruktur ingin link ke video tutorial
- Instruktur ingin link ke Google Drive resources
- Student perlu akses ke external resources saat quiz

**Requirements**:
1. Tambah field `external_links` (JSON) di quizzes table
2. Support multiple links per quiz
3. Link metadata: title, url, type (youtube/drive/generic)
4. Display links di quiz detail page
5. Track link clicks (optional analytics)

### 2.3 Assignment - External Links
**Use Cases**:
- Instruktur ingin link ke assignment instructions di Google Docs
- Instruktur ingin link ke reference materials
- Instruktur ingin link ke video tutorial
- Student submit link ke Google Drive untuk submission

**Requirements**:
1. Tambah field `external_links` (JSON) di assignments table
2. Support multiple links per assignment
3. Link metadata: title, url, type, description
4. Allow students to submit Google Drive links
5. Validation untuk submission links

---

## 3. DATABASE CHANGES

### 3.1 Migration: Add External URL to Lesson Blocks
**File**: `database/migrations/YYYY_MM_DD_add_external_url_to_lesson_blocks.php`

```php
Schema::table('lesson_blocks', function (Blueprint $table) {
    $table->string('external_url', 500)->nullable()->after('content');
    $table->string('external_type', 50)->nullable()->after('external_url');
    $table->json('external_metadata')->nullable()->after('external_type');
});
```

**Fields**:
- `external_url`: URL eksternal (YouTube, Drive, dll)
- `external_type`: Type link (youtube, drive, vimeo, loom, generic)
- `external_metadata`: JSON untuk title, thumbnail, duration, dll


### 3.2 Migration: Add External Links to Quizzes
**File**: `database/migrations/YYYY_MM_DD_add_external_links_to_quizzes.php`

```php
Schema::table('quizzes', function (Blueprint $table) {
    $table->json('external_links')->nullable()->after('description');
});
```

**JSON Structure**:
```json
[
  {
    "title": "Tutorial Video",
    "url": "https://youtube.com/watch?v=xxx",
    "type": "youtube",
    "description": "Watch this before starting"
  },
  {
    "title": "Reference Material",
    "url": "https://drive.google.com/file/d/xxx",
    "type": "drive",
    "description": "Download the PDF"
  }
]
```

### 3.3 Migration: Add External Links to Assignments
**File**: `database/migrations/YYYY_MM_DD_add_external_links_to_assignments.php`

```php
Schema::table('assignments', function (Blueprint $table) {
    $table->json('external_links')->nullable()->after('description');
    $table->boolean('allow_link_submission')->default(false)->after('submission_type');
});
```

**Fields**:
- `external_links`: JSON array untuk reference links
- `allow_link_submission`: Boolean untuk allow student submit link

### 3.4 Migration: Add Submission Links
**File**: `database/migrations/YYYY_MM_DD_add_submission_links.php`

```php
Schema::table('submissions', function (Blueprint $table) {
    $table->json('submission_links')->nullable()->after('answer_text');
});
```

**JSON Structure**:
```json
[
  {
    "url": "https://drive.google.com/file/d/xxx",
    "type": "drive",
    "title": "My Assignment File",
    "submitted_at": "2026-03-23T10:00:00Z"
  }
]
```


---

## 4. ENUM UPDATES

### 4.1 Lesson Block Types
**File**: Update validation di `LessonBlockRequest`

**Current**: text, video, image, file  
**New**: text, video, image, file, link, youtube, drive, embed

**Mapping**:
- `link`: Generic external link
- `youtube`: YouTube video embed
- `drive`: Google Drive file/folder
- `embed`: Generic iframe embed (Loom, Vimeo, dll)

### 4.2 External Link Types (New Enum)
**File**: `Modules\Schemes\Enums\ExternalLinkType.php`

```php
enum ExternalLinkType: string
{
    case YouTube = 'youtube';
    case GoogleDrive = 'drive';
    case Vimeo = 'vimeo';
    case Loom = 'loom';
    case Generic = 'generic';
}
```

---

## 5. MODEL UPDATES

### 5.1 LessonBlock Model
**File**: `Modules\Schemes\Models\LessonBlock.php`

**Changes**:
```php
protected $fillable = [
    'lesson_id', 'slug', 'block_type', 'content', 'order',
    'external_url', 'external_type', 'external_metadata'
];

protected $casts = [
    'order' => 'integer',
    'external_metadata' => 'array',
];

// New methods
public function isExternalLink(): bool
{
    return in_array($this->block_type, ['link', 'youtube', 'drive', 'embed']);
}

public function getEmbedUrl(): ?string
{
    if ($this->block_type === 'youtube') {
        return $this->convertYouTubeToEmbed($this->external_url);
    }
    return $this->external_url;
}

private function convertYouTubeToEmbed(string $url): string
{
    // Convert youtube.com/watch?v=xxx to youtube.com/embed/xxx
    preg_match('/[?&]v=([^&]+)/', $url, $matches);
    return $matches ? "https://www.youtube.com/embed/{$matches[1]}" : $url;
}
```


### 5.2 Quiz Model
**File**: `Modules\Learning\Models\Quiz.php`

**Changes**:
```php
protected $fillable = [
    // ... existing fields
    'external_links',
];

protected $casts = [
    // ... existing casts
    'external_links' => 'array',
];

// New methods
public function hasExternalLinks(): bool
{
    return !empty($this->external_links);
}

public function getExternalLinksCount(): int
{
    return count($this->external_links ?? []);
}
```

### 5.3 Assignment Model
**File**: `Modules\Learning\Models\Assignment.php`

**Changes**:
```php
protected $fillable = [
    // ... existing fields
    'external_links',
    'allow_link_submission',
];

protected $casts = [
    // ... existing casts
    'external_links' => 'array',
    'allow_link_submission' => 'boolean',
];

// New methods
public function hasExternalLinks(): bool
{
    return !empty($this->external_links);
}

public function allowsLinkSubmission(): bool
{
    return $this->allow_link_submission;
}
```

### 5.4 Submission Model
**File**: `Modules\Learning\Models\Submission.php`

**Changes**:
```php
protected $fillable = [
    // ... existing fields
    'submission_links',
];

protected $casts = [
    // ... existing casts
    'submission_links' => 'array',
];

// New methods
public function hasSubmissionLinks(): bool
{
    return !empty($this->submission_links);
}

public function getSubmissionLinksCount(): int
{
    return count($this->submission_links ?? []);
}
```


---

## 6. VALIDATION UPDATES

### 6.1 LessonBlockRequest
**File**: `Modules\Schemes\Http\Requests\LessonBlockRequest.php`

**Changes**:
```php
public function rules(): array
{
    $maxMb = config('app.lesson_block_max_upload_mb', 50);
    $maxKb = $maxMb * 1024;

    return [
        'type' => 'required|in:text,video,image,file,link,youtube,drive,embed',
        'content' => 'nullable|string',
        'order' => 'nullable|integer|min:1',
        
        // External URL fields
        'external_url' => [
            'nullable',
            'url',
            'max:500',
            function ($attribute, $value, $fail) {
                $type = $this->input('type');
                if (in_array($type, ['link', 'youtube', 'drive', 'embed']) && !$value) {
                    $fail(__('validation.custom.external_url.required_for_type'));
                }
            },
        ],
        'external_type' => 'nullable|in:youtube,drive,vimeo,loom,generic',
        'external_metadata' => 'nullable|array',
        'external_metadata.title' => 'nullable|string|max:255',
        'external_metadata.description' => 'nullable|string',
        'external_metadata.thumbnail' => 'nullable|url',
        
        // Media file (for upload types)
        'media' => [
            'nullable',
            'file',
            'max:' . $maxKb,
            function ($attribute, $value, $fail) {
                $type = $this->input('type');
                if (in_array($type, ['video', 'image', 'file']) && !$value && !$this->input('external_url')) {
                    $fail(__('validation.custom.media.required_for_type'));
                }
            },
        ],
    ];
}
```


### 6.2 StoreQuizRequest
**File**: `Modules\Learning\Http\Requests\StoreQuizRequest.php`

**Changes**:
```php
public function rules(): array
{
    return [
        // ... existing rules
        'external_links' => ['nullable', 'array', 'max:10'],
        'external_links.*.title' => ['required', 'string', 'max:255'],
        'external_links.*.url' => ['required', 'url', 'max:500'],
        'external_links.*.type' => ['nullable', 'in:youtube,drive,vimeo,loom,generic'],
        'external_links.*.description' => ['nullable', 'string', 'max:500'],
    ];
}
```

### 6.3 StoreAssignmentRequest
**File**: `Modules\Learning\Http\Requests\StoreAssignmentRequest.php`

**Changes**:
```php
public function rules(): array
{
    return [
        // ... existing rules
        'external_links' => ['nullable', 'array', 'max:10'],
        'external_links.*.title' => ['required', 'string', 'max:255'],
        'external_links.*.url' => ['required', 'url', 'max:500'],
        'external_links.*.type' => ['nullable', 'in:youtube,drive,vimeo,loom,generic'],
        'external_links.*.description' => ['nullable', 'string', 'max:500'],
        'allow_link_submission' => ['nullable', 'boolean'],
    ];
}
```

### 6.4 SubmitAssignmentRequest (New)
**File**: `Modules\Learning\Http\Requests\SubmitAssignmentRequest.php`

**Changes**:
```php
public function rules(): array
{
    return [
        // ... existing rules
        'submission_links' => ['nullable', 'array', 'max:5'],
        'submission_links.*.url' => ['required', 'url', 'max:500'],
        'submission_links.*.type' => ['nullable', 'in:drive,generic'],
        'submission_links.*.title' => ['nullable', 'string', 'max:255'],
    ];
}

public function withValidator($validator): void
{
    $validator->after(function ($validator) {
        $assignment = $this->route('assignment');
        if ($this->has('submission_links') && !$assignment->allow_link_submission) {
            $validator->errors()->add('submission_links', 'This assignment does not allow link submissions.');
        }
    });
}
```


---

## 7. SERVICE LAYER UPDATES

### 7.1 LessonBlockService
**File**: `Modules\Schemes\Services\LessonBlockService.php`

**Changes**:
```php
public function create(int $lessonId, array $data, ?UploadedFile $mediaFile): LessonBlock
{
    return DB::transaction(function () use ($lessonId, $data, $mediaFile) {
        // ... existing order logic
        
        $blockData = [
            'lesson_id' => $lessonId,
            'slug' => (string) Str::uuid(),
            'block_type' => $data['type'],
            'content' => $data['content'] ?? null,
            'order' => $nextOrder,
        ];
        
        // Add external URL fields if present
        if (isset($data['external_url'])) {
            $blockData['external_url'] = $data['external_url'];
            $blockData['external_type'] = $data['external_type'] ?? $this->detectLinkType($data['external_url']);
            $blockData['external_metadata'] = $data['external_metadata'] ?? $this->extractMetadata($data['external_url']);
        }
        
        $block = $this->repository->create($blockData);
        
        // Only handle media file if not external link
        if ($mediaFile && !in_array($data['type'], ['link', 'youtube', 'drive', 'embed'])) {
            // ... existing media handling
        }
        
        cache()->tags(['schemes', 'lesson_blocks'])->flush();
        return $block->fresh();
    });
}

private function detectLinkType(string $url): string
{
    if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
        return 'youtube';
    }
    if (str_contains($url, 'drive.google.com')) {
        return 'drive';
    }
    if (str_contains($url, 'vimeo.com')) {
        return 'vimeo';
    }
    if (str_contains($url, 'loom.com')) {
        return 'loom';
    }
    return 'generic';
}

private function extractMetadata(string $url): array
{
    // Basic metadata extraction
    // Can be enhanced with oEmbed API calls
    return [
        'url' => $url,
        'extracted_at' => now()->toISOString(),
    ];
}
```


### 7.2 QuizService
**File**: `Modules\Learning\Services\QuizService.php`

**Changes**:
```php
public function create(array $data, int $createdBy): Quiz
{
    return DB::transaction(function () use ($data, $createdBy) {
        // ... existing logic
        
        $quizData = [
            // ... existing fields
            'external_links' => $data['external_links'] ?? null,
        ];
        
        $quiz = $this->repository->create($quizData);
        
        // ... rest of logic
        return $quiz;
    });
}

public function update(Quiz $quiz, array $data): Quiz
{
    return DB::transaction(function () use ($quiz, $data) {
        $updateData = [
            // ... existing fields
        ];
        
        if (isset($data['external_links'])) {
            $updateData['external_links'] = $data['external_links'];
        }
        
        $quiz->update($updateData);
        
        // ... rest of logic
        return $quiz->fresh();
    });
}
```

### 7.3 AssignmentService
**File**: `Modules\Learning\Services\AssignmentService.php`

**Changes**:
```php
public function create(array $data, int $createdBy): Assignment
{
    return DB::transaction(function () use ($data, $createdBy) {
        // ... existing logic
        
        $assignmentData = [
            // ... existing fields
            'external_links' => $data['external_links'] ?? null,
            'allow_link_submission' => $data['allow_link_submission'] ?? false,
        ];
        
        $assignment = $this->repository->create($assignmentData);
        
        // ... rest of logic
        return $assignment;
    });
}
```

### 7.4 SubmissionService
**File**: `Modules\Learning\Services\SubmissionService.php`

**Changes**:
```php
public function submitAssignment(Assignment $assignment, int $userId, array $data): Submission
{
    return DB::transaction(function () use ($assignment, $userId, $data) {
        // ... existing logic
        
        $submissionData = [
            // ... existing fields
            'submission_links' => $data['submission_links'] ?? null,
        ];
        
        $submission = $this->repository->create($submissionData);
        
        // ... rest of logic
        return $submission;
    });
}
```


---

## 8. RESOURCE UPDATES

### 8.1 LessonBlockResource
**File**: `Modules\Schemes\Http\Resources\LessonBlockResource.php`

**Changes**:
```php
public function toArray($request): array
{
    return [
        'id' => $this->id,
        'slug' => $this->slug,
        'lesson_id' => $this->lesson_id,
        'block_type' => $this->block_type,
        'content' => $this->content,
        'order' => $this->order,
        
        // External link fields
        'external_url' => $this->external_url,
        'external_type' => $this->external_type,
        'external_metadata' => $this->external_metadata,
        'embed_url' => $this->isExternalLink() ? $this->getEmbedUrl() : null,
        
        // Media fields (for uploaded files)
        'media_url' => $this->media_url,
        'media_thumb_url' => $this->media_thumb_url,
        'media_meta' => $this->media_meta,
        
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
    ];
}
```

### 8.2 QuizResource
**File**: `Modules\Learning\Http\Resources\QuizResource.php`

**Changes**:
```php
public function toArray($request): array
{
    return [
        // ... existing fields
        'external_links' => $this->external_links,
        'external_links_count' => $this->getExternalLinksCount(),
        'has_external_links' => $this->hasExternalLinks(),
        // ... rest of fields
    ];
}
```

### 8.3 AssignmentResource
**File**: `Modules\Learning\Http\Resources\AssignmentResource.php`

**Changes**:
```php
public function toArray($request): array
{
    return [
        // ... existing fields
        'external_links' => $this->external_links,
        'external_links_count' => count($this->external_links ?? []),
        'has_external_links' => $this->hasExternalLinks(),
        'allow_link_submission' => $this->allow_link_submission,
        // ... rest of fields
    ];
}
```

### 8.4 SubmissionResource
**File**: `Modules\Learning\Http\Resources\SubmissionResource.php`

**Changes**:
```php
public function toArray($request): array
{
    return [
        // ... existing fields
        'submission_links' => $this->submission_links,
        'submission_links_count' => $this->getSubmissionLinksCount(),
        'has_submission_links' => $this->hasSubmissionLinks(),
        // ... rest of fields
    ];
}
```


---

## 9. HELPER SERVICE (Optional Enhancement)

### 9.1 LinkMetadataService
**File**: `Modules\Common\Services\LinkMetadataService.php`

**Purpose**: Extract metadata dari external links menggunakan oEmbed API

```php
<?php

namespace Modules\Common\Services;

use Illuminate\Support\Facades\Http;

class LinkMetadataService
{
    public function extractMetadata(string $url): array
    {
        $type = $this->detectType($url);
        
        return match($type) {
            'youtube' => $this->extractYouTubeMetadata($url),
            'vimeo' => $this->extractVimeoMetadata($url),
            'drive' => $this->extractDriveMetadata($url),
            default => $this->extractGenericMetadata($url),
        };
    }
    
    private function extractYouTubeMetadata(string $url): array
    {
        // Extract video ID
        preg_match('/[?&]v=([^&]+)/', $url, $matches);
        $videoId = $matches[1] ?? null;
        
        if (!$videoId) {
            return ['url' => $url, 'type' => 'youtube'];
        }
        
        // Use YouTube oEmbed API
        $response = Http::get("https://www.youtube.com/oembed", [
            'url' => $url,
            'format' => 'json',
        ]);
        
        if ($response->successful()) {
            $data = $response->json();
            return [
                'url' => $url,
                'type' => 'youtube',
                'title' => $data['title'] ?? null,
                'thumbnail' => $data['thumbnail_url'] ?? null,
                'author' => $data['author_name'] ?? null,
                'duration' => null, // Requires YouTube Data API
            ];
        }
        
        return ['url' => $url, 'type' => 'youtube'];
    }
    
    private function extractVimeoMetadata(string $url): array
    {
        $response = Http::get("https://vimeo.com/api/oembed.json", [
            'url' => $url,
        ]);
        
        if ($response->successful()) {
            $data = $response->json();
            return [
                'url' => $url,
                'type' => 'vimeo',
                'title' => $data['title'] ?? null,
                'thumbnail' => $data['thumbnail_url'] ?? null,
                'author' => $data['author_name'] ?? null,
                'duration' => $data['duration'] ?? null,
            ];
        }
        
        return ['url' => $url, 'type' => 'vimeo'];
    }
    
    private function extractDriveMetadata(string $url): array
    {
        // Google Drive doesn't have public oEmbed
        // Extract file ID and construct preview URL
        preg_match('/\/d\/([^\/]+)/', $url, $matches);
        $fileId = $matches[1] ?? null;
        
        return [
            'url' => $url,
            'type' => 'drive',
            'file_id' => $fileId,
            'preview_url' => $fileId ? "https://drive.google.com/file/d/{$fileId}/preview" : null,
        ];
    }
    
    private function extractGenericMetadata(string $url): array
    {
        // Basic metadata extraction using HTTP headers
        try {
            $response = Http::timeout(5)->head($url);
            
            return [
                'url' => $url,
                'type' => 'generic',
                'content_type' => $response->header('Content-Type'),
                'content_length' => $response->header('Content-Length'),
            ];
        } catch (\Exception $e) {
            return ['url' => $url, 'type' => 'generic'];
        }
    }
    
    private function detectType(string $url): string
    {
        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            return 'youtube';
        }
        if (str_contains($url, 'vimeo.com')) {
            return 'vimeo';
        }
        if (str_contains($url, 'drive.google.com')) {
            return 'drive';
        }
        return 'generic';
    }
}
```


---

## 10. TRANSLATION UPDATES

### 10.1 Validation Messages
**File**: `lang/id/validation.php` & `lang/en/validation.php`

```php
'custom' => [
    'external_url' => [
        'required_for_type' => 'URL eksternal wajib diisi untuk tipe link/youtube/drive/embed',
        'invalid_youtube' => 'Format URL YouTube tidak valid',
        'invalid_drive' => 'Format URL Google Drive tidak valid',
    ],
    'submission_links' => [
        'not_allowed' => 'Assignment ini tidak mengizinkan submission link',
        'max_links' => 'Maksimal :max link dapat disubmit',
    ],
],

'attributes' => [
    'external_url' => 'URL Eksternal',
    'external_type' => 'Tipe Link',
    'external_links' => 'Link Eksternal',
    'allow_link_submission' => 'Izinkan Submission Link',
    'submission_links' => 'Link Submission',
],
```

### 10.2 Enum Translations
**File**: `lang/id/enums.php` & `lang/en/enums.php`

```php
'external_link_type' => [
    'youtube' => 'YouTube',
    'drive' => 'Google Drive',
    'vimeo' => 'Vimeo',
    'loom' => 'Loom',
    'generic' => 'Link Umum',
],

'lesson_block_type' => [
    'text' => 'Teks',
    'video' => 'Video Upload',
    'image' => 'Gambar',
    'file' => 'File',
    'link' => 'Link Eksternal',
    'youtube' => 'YouTube',
    'drive' => 'Google Drive',
    'embed' => 'Embed',
],
```

---

## 11. IMPLEMENTATION STEPS

### Phase 1: Database & Models (Week 1)
1. ✅ Create migration untuk lesson_blocks (external_url, external_type, external_metadata)
2. ✅ Create migration untuk quizzes (external_links)
3. ✅ Create migration untuk assignments (external_links, allow_link_submission)
4. ✅ Create migration untuk submissions (submission_links)
5. ✅ Update LessonBlock model (fillable, casts, methods)
6. ✅ Update Quiz model (fillable, casts, methods)
7. ✅ Update Assignment model (fillable, casts, methods)
8. ✅ Update Submission model (fillable, casts, methods)
9. ✅ Create ExternalLinkType enum
10. ✅ Run migrations

### Phase 2: Validation & Requests (Week 1-2)
1. ✅ Update LessonBlockRequest validation
2. ✅ Update StoreQuizRequest validation
3. ✅ Update StoreAssignmentRequest validation
4. ✅ Update SubmitAssignmentRequest validation
5. ✅ Add custom validation rules untuk URL formats
6. ✅ Add translation keys


### Phase 3: Services & Business Logic (Week 2)
1. ✅ Update LessonBlockService (create, update methods)
2. ✅ Add detectLinkType() helper method
3. ✅ Add extractMetadata() helper method
4. ✅ Update QuizService (create, update methods)
5. ✅ Update AssignmentService (create, update methods)
6. ✅ Update SubmissionService (submit method)
7. ✅ Create LinkMetadataService (optional)
8. ✅ Add unit tests untuk services

### Phase 4: Resources & API Response (Week 2-3)
1. ✅ Update LessonBlockResource
2. ✅ Update QuizResource
3. ✅ Update AssignmentResource
4. ✅ Update SubmissionResource
5. ✅ Test API responses

### Phase 5: Testing & Documentation (Week 3)
1. ✅ Create seeder untuk sample external links
2. ✅ Test lesson block creation dengan YouTube link
3. ✅ Test lesson block creation dengan Drive link
4. ✅ Test quiz dengan external links
5. ✅ Test assignment dengan external links
6. ✅ Test submission dengan link submission
7. ✅ Update API documentation
8. ✅ Create Postman collection examples

---

## 12. API EXAMPLES

### 12.1 Create Lesson Block dengan YouTube
```http
POST /api/courses/{course}/units/{unit}/lessons/{lesson}/blocks
Content-Type: application/json

{
  "type": "youtube",
  "external_url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
  "external_type": "youtube",
  "external_metadata": {
    "title": "Tutorial Video",
    "description": "Watch this tutorial"
  },
  "order": 1
}
```

### 12.2 Create Lesson Block dengan Google Drive
```http
POST /api/courses/{course}/units/{unit}/lessons/{lesson}/blocks
Content-Type: application/json

{
  "type": "drive",
  "external_url": "https://drive.google.com/file/d/1abc123xyz/view",
  "external_type": "drive",
  "external_metadata": {
    "title": "Course Material PDF"
  },
  "order": 2
}
```


### 12.3 Create Quiz dengan External Links
```http
POST /api/courses/{course}/quizzes
Content-Type: application/json

{
  "unit_slug": "unit-1",
  "title": "Quiz 1",
  "description": "Complete this quiz",
  "external_links": [
    {
      "title": "Tutorial Video",
      "url": "https://www.youtube.com/watch?v=abc123",
      "type": "youtube",
      "description": "Watch before starting"
    },
    {
      "title": "Reference Material",
      "url": "https://drive.google.com/file/d/xyz789/view",
      "type": "drive",
      "description": "Download the PDF"
    }
  ],
  "passing_grade": 75,
  "time_limit_minutes": 60
}
```

### 12.4 Create Assignment dengan Link Submission
```http
POST /api/courses/{course}/assignments
Content-Type: application/json

{
  "unit_slug": "unit-1",
  "title": "Assignment 1",
  "description": "Submit your work",
  "submission_type": "mixed",
  "allow_link_submission": true,
  "external_links": [
    {
      "title": "Instructions",
      "url": "https://docs.google.com/document/d/abc123",
      "type": "generic",
      "description": "Read the instructions"
    }
  ],
  "max_score": 100
}
```

### 12.5 Submit Assignment dengan Link
```http
POST /api/assignments/{assignment}/submissions
Content-Type: application/json

{
  "answer_text": "Here is my submission",
  "submission_links": [
    {
      "url": "https://drive.google.com/file/d/my-work-123/view",
      "type": "drive",
      "title": "My Assignment File"
    }
  ]
}
```

---

## 13. SECURITY CONSIDERATIONS

### 13.1 URL Validation
- ✅ Validate URL format menggunakan Laravel's `url` rule
- ✅ Whitelist allowed domains (optional): youtube.com, drive.google.com, vimeo.com, loom.com
- ✅ Sanitize URL untuk prevent XSS
- ✅ Check URL accessibility (optional HTTP HEAD request)

### 13.2 Embed Security
- ✅ Use iframe sandbox attributes
- ✅ Set CSP headers untuk iframe sources
- ✅ Validate embed URLs before rendering
- ✅ Use HTTPS only untuk external links

### 13.3 Rate Limiting
- ✅ Limit metadata extraction API calls
- ✅ Cache extracted metadata
- ✅ Implement retry logic dengan exponential backoff


---

## 14. FRONTEND CONSIDERATIONS

### 14.1 Lesson Block Rendering
**Component**: `LessonBlockViewer.tsx`

```typescript
// Pseudo-code untuk rendering
switch (block.block_type) {
  case 'youtube':
    return <YouTubeEmbed url={block.embed_url} />;
  
  case 'drive':
    return <GoogleDriveViewer url={block.external_url} />;
  
  case 'link':
    return <ExternalLink url={block.external_url} metadata={block.external_metadata} />;
  
  case 'embed':
    return <IframeEmbed url={block.external_url} />;
  
  case 'video':
    return <VideoPlayer url={block.media_url} />;
  
  // ... other types
}
```

### 14.2 Quiz External Links Display
**Component**: `QuizExternalLinks.tsx`

```typescript
// Display external links di quiz detail page
{quiz.has_external_links && (
  <div className="external-links">
    <h3>Reference Materials</h3>
    {quiz.external_links.map(link => (
      <ExternalLinkCard
        key={link.url}
        title={link.title}
        url={link.url}
        type={link.type}
        description={link.description}
      />
    ))}
  </div>
)}
```

### 14.3 Assignment Link Submission
**Component**: `AssignmentSubmissionForm.tsx`

```typescript
// Form untuk submit link
{assignment.allow_link_submission && (
  <div className="link-submission">
    <label>Submit Link (Google Drive, etc.)</label>
    <input
      type="url"
      placeholder="https://drive.google.com/..."
      value={submissionLink}
      onChange={handleLinkChange}
    />
    <button onClick={addLink}>Add Link</button>
    
    {submissionLinks.map((link, index) => (
      <LinkPreview
        key={index}
        url={link.url}
        onRemove={() => removeLink(index)}
      />
    ))}
  </div>
)}
```

---

## 15. PERFORMANCE OPTIMIZATION

### 15.1 Caching Strategy
```php
// Cache lesson blocks dengan external links
cache()->tags(['schemes', 'lesson_blocks'])
    ->remember("lesson_blocks:{$lessonId}", 300, function() {
        return LessonBlock::where('lesson_id', $lessonId)->get();
    });

// Cache metadata extraction results
cache()->remember("link_metadata:{$urlHash}", 3600, function() use ($url) {
    return $this->linkMetadataService->extractMetadata($url);
});
```

### 15.2 Lazy Loading
- Load external link metadata on-demand
- Use background jobs untuk metadata extraction
- Queue metadata updates untuk bulk operations

### 15.3 Database Indexing
```php
// Add indexes untuk performance
Schema::table('lesson_blocks', function (Blueprint $table) {
    $table->index('external_type');
    $table->index(['lesson_id', 'block_type']);
});
```


---

## 16. TESTING CHECKLIST

### 16.1 Unit Tests
- [ ] LessonBlockService::create() dengan external URL
- [ ] LessonBlockService::detectLinkType()
- [ ] LessonBlockService::extractMetadata()
- [ ] QuizService::create() dengan external_links
- [ ] AssignmentService::create() dengan external_links
- [ ] SubmissionService::submit() dengan submission_links
- [ ] LinkMetadataService::extractYouTubeMetadata()
- [ ] LinkMetadataService::extractDriveMetadata()

### 16.2 Feature Tests
- [ ] POST /lesson-blocks dengan YouTube URL
- [ ] POST /lesson-blocks dengan Drive URL
- [ ] POST /lesson-blocks dengan generic link
- [ ] POST /quizzes dengan external_links array
- [ ] POST /assignments dengan external_links
- [ ] POST /submissions dengan submission_links
- [ ] Validation errors untuk invalid URLs
- [ ] Authorization checks untuk external link creation

### 16.3 Integration Tests
- [ ] Create lesson dengan multiple blocks (upload + external)
- [ ] Create quiz dengan attachments + external links
- [ ] Submit assignment dengan files + links
- [ ] Student access external links dalam enrolled course
- [ ] Instructor manage external links

### 16.4 Manual Testing
- [ ] YouTube embed rendering
- [ ] Google Drive preview
- [ ] Vimeo embed
- [ ] Loom embed
- [ ] Generic link display
- [ ] Mobile responsiveness
- [ ] Link validation feedback
- [ ] Metadata extraction accuracy

---

## 17. ROLLBACK PLAN

### 17.1 Database Rollback
```bash
# Rollback migrations jika ada masalah
php artisan migrate:rollback --step=4

# Migrations yang akan di-rollback:
# - add_external_url_to_lesson_blocks
# - add_external_links_to_quizzes
# - add_external_links_to_assignments
# - add_submission_links_to_submissions
```

### 17.2 Code Rollback
- Revert model changes (fillable, casts)
- Revert validation rules
- Revert service methods
- Revert resource changes
- Clear cache: `php artisan cache:clear`

### 17.3 Data Migration
Jika perlu rollback setelah production:
```php
// Script untuk migrate data kembali
// Convert external_url blocks ke media uploads (jika memungkinkan)
// Or soft-delete external link blocks
```


---

## 18. FUTURE ENHANCEMENTS

### 18.1 Advanced Metadata Extraction
- Integrate YouTube Data API untuk video duration, views, likes
- Use Google Drive API untuk file metadata
- Implement Open Graph protocol parser
- Add thumbnail generation untuk generic links

### 18.2 Link Analytics
- Track link clicks per student
- Monitor most accessed external resources
- Generate reports untuk instructor
- A/B testing untuk different link types

### 18.3 Link Validation & Health Check
- Periodic check untuk broken links
- Notify instructor jika link tidak accessible
- Auto-suggest alternative links
- Link expiration warnings

### 18.4 Enhanced Embed Support
- Support untuk Figma embeds
- Support untuk CodePen embeds
- Support untuk Google Slides
- Support untuk Miro boards
- Custom embed templates

### 18.5 Smart Link Detection
- Auto-detect link type dari paste
- Auto-extract metadata saat paste URL
- Suggest embed vs direct link
- Preview link sebelum save

---

## 19. DOCUMENTATION UPDATES

### 19.1 API Documentation
- [ ] Update Postman collection
- [ ] Add external link examples
- [ ] Document validation rules
- [ ] Add error response examples

### 19.2 User Guide
- [ ] Instruktur guide: How to add YouTube videos
- [ ] Instruktur guide: How to link Google Drive files
- [ ] Student guide: How to submit Drive links
- [ ] FAQ: Supported link types

### 19.3 Developer Documentation
- [ ] Architecture decision record (ADR)
- [ ] Database schema documentation
- [ ] Service layer documentation
- [ ] Testing guide

---

## 20. SUMMARY

### What's Being Added
1. **Lesson Blocks**: Support untuk YouTube, Google Drive, dan generic external links
2. **Quizzes**: Multiple external reference links
3. **Assignments**: Reference links + allow student link submissions
4. **Submissions**: Student dapat submit Google Drive links

### Key Benefits
- ✅ Instruktur tidak perlu upload large video files
- ✅ Easy integration dengan Google Workspace
- ✅ Support untuk berbagai video platforms
- ✅ Flexible content delivery
- ✅ Reduced storage costs

### Technical Approach
- Minimal database changes (add 3-4 columns)
- Backward compatible (existing media uploads tetap work)
- Optional metadata extraction service
- Proper validation dan security
- Clean API design

### Timeline
- **Week 1**: Database, models, validation
- **Week 2**: Services, business logic, resources
- **Week 3**: Testing, documentation, deployment

### Risk Mitigation
- Comprehensive testing strategy
- Rollback plan ready
- Backward compatibility maintained
- Security considerations addressed

---

**Status**: Ready for Implementation  
**Next Steps**: Review plan → Create migrations → Update models → Implement services

