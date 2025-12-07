<?php

namespace Modules\Schemes\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Str;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Tag;

class TagService
{
    public function list(array $params = [], int $perPage = 0): LengthAwarePaginator|Collection
    {
        $query = Tag::query();

        // Global search across name and slug
        if (! empty($params['search'])) {
            $keyword = trim((string) $params['search']);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // Individual field filters
        $name = $params['filter']['name'] ?? $params['name'] ?? null;
        if (is_string($name) && $name !== '') {
            $query->where('name', 'like', "%{$name}%");
        }

        $slug = $params['filter']['slug'] ?? $params['slug'] ?? null;
        if (is_string($slug) && $slug !== '') {
            $query->where('slug', 'like', "%{$slug}%");
        }

        $description = $params['filter']['description'] ?? $params['description'] ?? null;
        if (is_string($description) && $description !== '') {
            $query->where('description', 'like', "%{$description}%");
        }

        // Sorting
        $sort = (string) ($params['sort'] ?? '');
        $sortableFields = ['created_at', 'updated_at', 'name', 'slug'];
        if ($sort !== '') {
            $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
            $field = ltrim($sort, '-');
            if (in_array($field, $sortableFields, true)) {
                $query->orderBy($field, $direction);
            } else {
                $query->orderBy('name');
            }
        } else {
            $query->orderBy('name');
        }

        if ($perPage > 0) {
            $size = max(1, $perPage);

            return $query->paginate($size)->appends($params);
        }

        return $query->get();
    }

    public function create(array $data): Tag
    {
        $name = trim((string) ($data['name'] ?? ''));

        return $this->firstOrCreateByName($name);
    }

    /**
     * @param  array<int, string>  $names
     * @return \Illuminate\Support\Collection<int, Tag>
     */
    public function createMany(array $names): BaseCollection
    {
        return BaseCollection::make($names)
            ->map(fn ($name) => trim((string) $name))
            ->filter(fn ($name) => $name !== '')
            ->map(fn ($name) => $this->firstOrCreateByName($name))
            ->values();
    }

    public function update(int $id, array $data): ?Tag
    {
        $tag = Tag::find($id);
        if (! $tag) {
            return null;
        }

        $payload = $this->preparePayload($data, $tag->id, $tag->slug, $tag->name);
        $tag->fill($payload);
        $tag->save();

        return $tag;
    }

    public function delete(int $id): bool
    {
        $tag = Tag::find($id);
        if (! $tag) {
            return false;
        }

        $tag->courses()->detach();

        return (bool) $tag->delete();
    }

    /**
     * Sync the provided tags (slug or name) to the course.
     */
    public function syncCourseTags(Course $course, array $tags): void
    {
        $tagIds = $this->resolveTagIds($tags);

        $course->tags()->sync($tagIds);

        $names = Tag::query()->whereIn('id', $tagIds)->pluck('name')->unique()->values()->toArray();
        $course->tags_json = $names;
        $course->save();
    }

    private function preparePayload(array $data, ?int $ignoreId = null, ?string $currentSlug = null, ?string $currentName = null): array
    {
        $name = trim((string) ($data['name'] ?? ''));

        $slugSource = Str::slug($name);

        if ($currentName !== null && mb_strtolower($currentName) === mb_strtolower($name) && $currentSlug) {
            $slug = $currentSlug;
        } else {
            if ($slugSource === '') {
                $slugSource = Str::slug(Str::uuid()->toString());
            }
            $slug = $this->ensureUniqueSlug($slugSource, $ignoreId);
        }

        return [
            'name' => $name,
            'slug' => $slug,
        ];
    }

    private function firstOrCreateByName(string $name): Tag
    {
        $existing = Tag::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing) {
            return $existing;
        }

        $payload = $this->preparePayload(['name' => $name]);

        return Tag::create($payload);
    }

    private function ensureUniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug;
        $counter = 1;

        while (Tag::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * @param  array<string|int>  $tags
     * @return array<int>
     */
    private function resolveTagIds(array $tags): array
    {
        return BaseCollection::make($tags)
            ->map(function ($tag) {
                if (is_numeric($tag)) {
                    return Tag::query()->where('id', (int) $tag)->value('id');
                }

                $value = trim((string) $tag);
                if ($value === '') {
                    return null;
                }

                $bySlug = Tag::query()
                    ->where('slug', Str::slug($value))
                    ->orWhere('slug', $value)
                    ->first();

                if ($bySlug) {
                    return $bySlug->id;
                }

                $byName = Tag::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])->first();
                if ($byName) {
                    return $byName->id;
                }

                $payload = $this->preparePayload(['name' => $value]);

                return Tag::create($payload)->id;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
