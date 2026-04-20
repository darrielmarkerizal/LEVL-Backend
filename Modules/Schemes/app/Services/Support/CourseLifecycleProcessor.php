<?php

declare(strict_types=1);

namespace Modules\Schemes\Services\Support;

use App\Exceptions\DuplicateResourceException;
use App\Support\CodeGenerator;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Auth\Models\User;
use Modules\Schemes\Contracts\Repositories\CourseRepositoryInterface;
use Modules\Schemes\DTOs\CreateCourseDTO;
use Modules\Schemes\DTOs\UpdateCourseDTO;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Services\SchemesCacheService;

class CourseLifecycleProcessor
{
    public function __construct(
        private readonly CourseRepositoryInterface $repository,
        private readonly SchemesCacheService $cacheService,
        private readonly \Modules\Schemes\Services\TagService $tagService,
    ) {}

    public function create(CreateCourseDTO|array $data, ?User $actor = null, array $files = []): Course
    {
        $loggingGuard = new Course;
        $loggingGuard->disableLogging();
        $eventDispatcher = Course::getEventDispatcher();
        Course::unsetEventDispatcher();
        activity()->disableLogging();

        try {
            return DB::transaction(function () use ($data, $actor, $files) {
                $attributes = $data instanceof CreateCourseDTO ? $data->toArrayWithoutNull() : $data;

                if (! isset($attributes['code'])) {
                    $attributes['code'] = $this->generateCourseCode();
                }

                if (! isset($attributes['slug']) || trim((string) $attributes['slug']) === '') {
                    $attributes['slug'] = $this->generateUniqueSlug((string) ($attributes['title'] ?? 'course'));
                }

                if (! isset($attributes['instructor_id'])) {
                    $attributes['instructor_id'] = null;
                }

                $hasTagsInput = array_key_exists('tags', $attributes)
                    || array_key_exists('tags_list', $attributes);

                $tags = $attributes['tags'] ?? $attributes['tags_list'] ?? null;
                if (! is_array($tags) && $tags !== null) {
                    $tags = [(string) $tags];
                }

                $instructorIds = $attributes['instructor_ids'] ?? null;
                if (! is_array($instructorIds) && $instructorIds !== null) {
                    $instructorIds = [(int) $instructorIds];
                }

                $outcomes = $attributes['outcomes'] ?? null;
                if (! is_array($outcomes) && $outcomes !== null) {
                    $outcomes = [$outcomes];
                }

                $attributes = Arr::except($attributes, ['tags', 'tags_list', 'instructor_ids', 'outcomes']);

                $course = Course::withoutEvents(fn () => $this->repository->create($attributes));

                if ($hasTagsInput && is_array($tags)) {
                    $this->tagService->syncCourseTags($course, $tags);
                }

                if (is_array($instructorIds) && ! empty($instructorIds)) {
                    $course->instructors()->sync($instructorIds);
                }

                if (is_array($outcomes)) {
                    $this->syncOutcomes($course, $outcomes);
                }

                $this->handleMedia($course, $files);
                $this->cacheService->invalidateListings();

                $course = $course->fresh(['tags', 'instructors', 'outcomes']);

                if ($actor) {
                    activity('schemes')
                        ->causedBy($actor)
                        ->performedOn($course)
                        ->withProperties(['course_id' => $course->id, 'action' => 'create'])
                        ->log("Created course: {$course->title} ({$course->code})");
                }

                return $course;
            });
        } catch (QueryException $e) {
            if ($this->isUniqueConstraintViolation($e)) {
                throw new DuplicateResourceException($this->parseCourseDuplicates($e));
            }

            throw $e;
        } finally {
            activity()->enableLogging();
            Course::setEventDispatcher($eventDispatcher);
            $loggingGuard->enableLogging();
        }
    }

    public function update(Course $course, UpdateCourseDTO|array $data, array $files = []): Course
    {
        $course->disableLogging();
        $eventDispatcher = Course::getEventDispatcher();
        Course::unsetEventDispatcher();
        activity()->disableLogging();

        try {
            $attributes = $data instanceof UpdateCourseDTO ? $data->toArrayWithoutNull() : $data;

            $hasTagsInput = array_key_exists('tags', $attributes)
                || array_key_exists('tags_list', $attributes);

            $tags = $attributes['tags'] ?? $attributes['tags_list'] ?? null;
            if (! is_array($tags) && $tags !== null) {
                $tags = [(string) $tags];
            }

            $instructorIds = $attributes['instructor_ids'] ?? null;
            if (! is_array($instructorIds) && $instructorIds !== null) {
                $instructorIds = [(int) $instructorIds];
            }

            $outcomes = $attributes['outcomes'] ?? null;
            if (! is_array($outcomes) && $outcomes !== null) {
                $outcomes = [$outcomes];
            }

            if (! isset($attributes['slug']) || trim((string) $attributes['slug']) === '') {
                $attributes['slug'] = $this->generateUniqueSlug(
                    (string) ($attributes['title'] ?? $course->title),
                    $course->id,
                );
            }

            $tagIds = ($hasTagsInput && is_array($tags))
                ? $this->tagService->prepareTagIds($tags)
                : null;

            $attributes = Arr::except($attributes, ['tags', 'tags_list', 'instructor_ids', 'outcomes']);

            return DB::transaction(function () use ($course, $attributes, $hasTagsInput, $tagIds, $instructorIds, $outcomes, $files) {
                Course::withoutEvents(fn () => $this->repository->update($course, $attributes));

                if ($hasTagsInput && $tagIds !== null) {
                    $course->tags()->sync($tagIds);
                    DB::afterCommit(function (): void {
                        cache()->tags(['schemes', 'tags'])->flush();
                        cache()->tags(['schemes', 'courses'])->flush();
                    });
                }

                if (is_array($instructorIds)) {
                    $course->instructors()->sync($instructorIds);
                }

                if (is_array($outcomes)) {
                    $this->syncOutcomes($course, $outcomes);
                }

                $this->handleMedia($course, $files);
                $this->cacheService->invalidateCourse($course->id, $course->slug);

                $updatedCourse = $course->fresh(['tags', 'instructors', 'outcomes']);

                $actor = auth()->user();
                if ($actor) {
                    activity('schemes')
                        ->causedBy($actor)
                        ->performedOn($updatedCourse)
                        ->withProperties(['course_id' => $course->id, 'action' => 'update', 'changes' => $attributes])
                        ->log("Updated course: {$updatedCourse->title}");
                }

                return $updatedCourse;
            });
        } catch (QueryException $e) {
            if ($this->isUniqueConstraintViolation($e)) {
                throw new DuplicateResourceException($this->parseCourseDuplicates($e));
            }

            throw $e;
        } finally {
            activity()->enableLogging();
            Course::setEventDispatcher($eventDispatcher);
            $course->enableLogging();
        }
    }

    public function delete(Course $course): bool
    {
        $deleted = $this->repository->delete($course);

        if ($deleted) {
            $actor = auth()->user();
            if ($actor) {
                dispatch(new \App\Jobs\LogActivityJob([
                    'log_name' => 'schemes',
                    'causer_id' => $actor->id,
                    'description' => "Deleted course: {$course->title}",
                    'properties' => ['course_id' => $course->id, 'action' => 'delete'],
                ]));
            }
        }

        return $deleted;
    }

    public function updateEnrollmentSettings(Course $course, array $data): array
    {
        $plainKey = null;

        if ($data['enrollment_type'] === 'key_based' && empty($data['enrollment_key'])) {
            $plainKey = $this->generateEnrollmentKey(12);
            $data['enrollment_key'] = $plainKey;
        } elseif ($data['enrollment_type'] === 'key_based' && ! empty($data['enrollment_key'])) {
            $plainKey = $data['enrollment_key'];
        }

        if ($data['enrollment_type'] !== 'key_based') {
            $data['enrollment_key'] = null;
        }

        $updated = $this->update($course, $data);

        return [
            'course' => $updated,
            'enrollment_key' => $plainKey,
        ];
    }

    public function uploadThumbnail(Course $course, UploadedFile $file): Course
    {
        $course->clearMediaCollection('thumbnail');
        $course->addMedia($file)->toMediaCollection('thumbnail');

        return $course->fresh();
    }

    public function uploadBanner(Course $course, UploadedFile $file): Course
    {
        $course->clearMediaCollection('banner');
        $course->addMedia($file)->toMediaCollection('banner');

        return $course->fresh();
    }

    public function deleteThumbnail(Course $course): Course
    {
        $course->clearMediaCollection('thumbnail');

        return $course->fresh();
    }

    public function deleteBanner(Course $course): Course
    {
        $course->clearMediaCollection('banner');

        return $course->fresh();
    }

    public function generateEnrollmentKey(int $length = 12): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $key = '';

        for ($i = 0; $i < $length; $i++) {
            $key .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $key;
    }

    private function handleMedia(Course $course, array $files): void
    {
        foreach (['thumbnail', 'banner'] as $collection) {
            if (! empty($files[$collection])) {
                $course->clearMediaCollection($collection);
                $course->addMedia($files[$collection])->toMediaCollection($collection);
            }
        }
    }

    private function parseCourseDuplicates(QueryException $e): array
    {
        $message = $this->buildDuplicateSourceMessage($e);
        $fields = [];
        $constraint = $this->extractConstraintName($message);
        $constraintField = $this->detectDuplicateField($constraint);
        if ($constraintField !== null) {
            $fields[] = $constraintField;
        }

        foreach ($this->extractDetailColumns($message) as $column) {
            $columnField = $this->detectDuplicateField($column);
            if ($columnField !== null) {
                $fields[] = $columnField;
            }
        }

        $fields = array_values(array_unique($fields));
        if (empty($fields)) {
            return ['general' => [__('messages.courses.duplicate_data')]];
        }

        $errors = [];
        foreach ($fields as $field) {
            $errors[$field] = [$this->duplicateMessageForField($field)];
        }

        return $errors;
    }

    private function extractConstraintName(string $message): ?string
    {
        if (preg_match('/constraint "([^"]+)"/i', $message, $matches)) {
            return strtolower(trim($matches[1]));
        }

        if (preg_match("/for key '([^']+)'/i", $message, $matches)) {
            return strtolower(trim($matches[1]));
        }

        if (preg_match('/for key `([^`]+)`/i', $message, $matches)) {
            return strtolower(trim($matches[1]));
        }

        if (preg_match('/for key ([^\\s,]+)/i', $message, $matches)) {
            return strtolower(trim($matches[1], " \t\n\r\0\x0B'`"));
        }

        return null;
    }

    private function extractDetailColumns(string $message): array
    {
        $columns = [];

        if (preg_match('/Key \(([^)]+)\)=\(([^)]*)\)/i', $message, $matches)) {
            $columns = array_merge($columns, explode(',', $matches[1]));
        }

        if (preg_match('/for key [\'`]?([^\'`\\s,]+)[\'`]?/i', $message, $matches)) {
            $columns[] = $matches[1];
        }

        if (preg_match('/Duplicate entry .* for key [\'`]?([^\'`\\s,]+)[\'`]?/i', $message, $matches)) {
            $columns[] = $matches[1];
        }

        return array_map(
            fn (string $column): string => strtolower(trim($column)),
            array_values(array_filter($columns, fn (string $column): bool => trim($column) !== '')),
        );
    }

    private function detectDuplicateField(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtolower($value);

        if (str_contains($normalized, 'slug')) {
            return 'slug';
        }

        if (str_contains($normalized, 'code')) {
            return 'code';
        }

        if (str_contains($normalized, 'title')) {
            return 'title';
        }

        if (str_contains($normalized, 'course_tag') || str_contains($normalized, 'tag')) {
            return 'tags';
        }

        if (str_contains($normalized, 'course_admin') || str_contains($normalized, 'instructor')) {
            return 'instructor_ids';
        }

        return null;
    }

    private function duplicateMessageForField(string $field): string
    {
        return match ($field) {
            'code' => __('messages.courses.code_exists'),
            'slug' => __('messages.courses.slug_exists'),
            'title' => __('messages.courses.title_exists'),
            'tags' => __('messages.courses.duplicate_data_field', ['field' => __('validation.attributes.tags')]),
            'instructor_ids' => __('messages.courses.duplicate_data_field', ['field' => __('validation.attributes.instructors')]),
            default => __('messages.courses.duplicate_data'),
        };
    }

    private function buildDuplicateSourceMessage(QueryException $e): string
    {
        $parts = [(string) $e->getMessage()];
        $errorInfo = $e->errorInfo;

        if (is_array($errorInfo)) {
            foreach ($errorInfo as $value) {
                if (is_string($value) && $value !== '') {
                    $parts[] = $value;
                }
            }
        }

        return strtolower(implode(' | ', array_unique($parts)));
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        $source = $this->buildDuplicateSourceMessage($e);
        $sqlState = is_array($e->errorInfo) ? ($e->errorInfo[0] ?? null) : null;

        return $sqlState === '23505'
            || $sqlState === '23000'
            || str_contains($source, 'duplicate key')
            || str_contains($source, 'unique constraint')
            || str_contains($source, 'already exists')
            || str_contains($source, 'duplicate entry');
    }

    private function generateCourseCode(): string
    {
        return CodeGenerator::generate('CRS-', 6, Course::class);
    }

    private function generateUniqueSlug(string $title, ?int $ignoreCourseId = null): string
    {
        $baseSlug = Str::slug($title);

        if ($baseSlug === '') {
            $baseSlug = 'course';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            Course::query()
                ->where('slug', $slug)
                ->when($ignoreCourseId !== null, fn ($query) => $query->whereKeyNot($ignoreCourseId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function syncOutcomes(Course $course, array $outcomes): void
    {
        $course->outcomes()->delete();

        $order = 1;
        foreach ($outcomes as $outcomeText) {
            if (is_string($outcomeText) && trim($outcomeText) !== '') {
                $course->outcomes()->create([
                    'outcome_text' => trim($outcomeText),
                    'order' => $order++,
                ]);
            }
        }
    }
}
