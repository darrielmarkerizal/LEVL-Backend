<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\Searchable;

class MasterDataItem extends Model
{
    use Searchable;

    protected $table = 'master_data';

    protected $fillable = [
        'type',
        'value',
        'label',
        'metadata',
        'is_system',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Cache key prefix for master data.
     */
    private const CACHE_PREFIX = 'master_data:';

    /**
     * Cache TTL in seconds (1 hour).
     */
    private const CACHE_TTL = 3600;

    /**
     * Get all items by type (cached).
     */
    public static function getByType(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            self::CACHE_PREFIX.$type,
            self::CACHE_TTL,
            fn () => self::where('type', $type)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
        );
    }

    /**
     * Get a single item by type and value (cached).
     */
    public static function getByTypeAndValue(string $type, string $value): ?self
    {
        $items = self::getByType($type);

        return $items->firstWhere('value', $value);
    }

    /**
     * Clear cache for a specific type.
     */
    public static function clearCache(string $type): void
    {
        Cache::forget(self::CACHE_PREFIX.$type);
    }

    /**
     * Clear all master data cache.
     */
    public static function clearAllCache(): void
    {
        // Get all distinct types and clear each
        self::distinct('type')->pluck('type')->each(function ($type) {
            self::clearCache($type);
        });
    }

    /**
     * Boot method to handle cache invalidation.
     */
    protected static function booted(): void
    {
        static::saved(function (self $item) {
            self::clearCache($item->type);
        });

        static::deleted(function (self $item) {
            self::clearCache($item->type);
        });
    }

    /**
     * Scope for active items only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'value' => $this->value,
            'label' => $this->label,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'master_data_index';
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->is_active;
    }
}
