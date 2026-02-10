<?php

declare(strict_types=1);

namespace Modules\Common\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait PgSearchable
{
    /**
     * Scope a query to search for a term using PostgreSQL Full Text Search (pg_trgm).
     */
    public function scopeSearch(Builder $query, string $term, float $threshold = 0.3): Builder
    {
        if (empty($term)) {
            return $query;
        }

        // Get searchable columns from the model
        $columns = $this->getSearchableColumns();

        if (empty($columns)) {
            return $query;
        }

        // Ensure pg_trgm extension is available (handled by migration, but good to be aware)
        // We use similarity for typo tolerance and ILIKE for partial matching fallback if needed.
        // However, checks for similarity usually cover fuzzy matching.

        // Normalize term for unaccent search if needed, but pg_trgm handles accents if configured or we can explicitly unaccent.
        // For simplicity and performance with pg_trgm:
        
        $term = trim($term);

        return $query->where(function (Builder $subQuery) use ($columns, $term, $threshold) {
             foreach ($columns as $column) {
                // unaccent(column)ILIKE unaccent(%term%)
                $subQuery->orWhereRaw("unaccent($column) ILIKE unaccent(?)", ["%{$term}%"]);
                // similarity(column, term) > threshold
                 $subQuery->orWhereRaw("similarity($column, ?) > ?", [$term, $threshold]);
             }
        });
    }

    /**
     * Get the columns that should be searchable.
     * Defaults to 'searchable_columns' property if defined, otherwise empty.
     */
    public function getSearchableColumns(): array
    {
        return property_exists($this, 'searchable_columns') ? $this->searchable_columns : [];
    }
}
