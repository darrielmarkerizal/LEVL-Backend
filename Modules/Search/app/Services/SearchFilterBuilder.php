<?php

namespace Modules\Search\Services;

class SearchFilterBuilder
{
    protected array $filters = [];

    
    public function addCategoryFilter(array $categoryIds): self
    {
        $this->filters['category_id'] = $categoryIds;

        return $this;
    }

    
    public function addLevelFilter(array $levels): self
    {
        $this->filters['level_tag'] = $levels;

        return $this;
    }

    
    public function addInstructorFilter(array $instructorIds): self
    {
        $this->filters['instructor_id'] = $instructorIds;

        return $this;
    }

    
    public function addDurationFilter(int $minDuration, int $maxDuration): self
    {
        $this->filters['duration_estimate'] = [
            'min' => $minDuration,
            'max' => $maxDuration,
        ];

        return $this;
    }

    
    public function addStatusFilter(array $statuses): self
    {
        $this->filters['status'] = $statuses;

        return $this;
    }

    
    public function build(): array
    {
        return $this->filters;
    }
}
