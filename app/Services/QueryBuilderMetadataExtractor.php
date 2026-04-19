<?php

namespace App\Services;

use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class QueryBuilderMetadataExtractor
{
    
    public function extractMetadata(QueryBuilder $query, ?string $translationPrefix = null): array
    {
        return [
            'sorts' => $this->extractAllowedSorts($query, $translationPrefix),
            'filters' => $this->extractAllowedFilters($query, $translationPrefix),
        ];
    }

    
    private function extractAllowedSorts(
        QueryBuilder $query,
        ?string $translationPrefix = null,
    ): array {
        try {
            $reflection = new \ReflectionClass($query);
            $property = $reflection->getProperty('allowedSorts');
            $property->setAccessible(true);

            $allowedSorts = $property->getValue($query);

            if (! $allowedSorts) {
                return [];
            }

            
            $sorts = [];
            foreach ($allowedSorts as $sort) {
                $sortField = null;
                if ($sort instanceof AllowedSort) {
                    $sortField = $this->extractSortName($sort);
                } elseif (is_string($sort)) {
                    $sortField = $sort;
                }

                if ($sortField) {
                    $sorts[] = [
                        'field' => $sortField,
                        'label' => $this->getTranslatedLabel($sortField, $translationPrefix),
                    ];
                }
            }

            return $sorts;
        } catch (\ReflectionException $e) {
            return [];
        }
    }

    
    private function extractSortName(AllowedSort $sort): string
    {
        try {
            $reflection = new \ReflectionClass($sort);
            $property = $reflection->getProperty('name');
            $property->setAccessible(true);

            return $property->getValue($sort);
        } catch (\ReflectionException $e) {
            return '';
        }
    }

    
    private function extractAllowedFilters(
        QueryBuilder $query,
        ?string $translationPrefix = null,
    ): array {
        try {
            $reflection = new \ReflectionClass($query);
            $property = $reflection->getProperty('allowedFilters');
            $property->setAccessible(true);

            $allowedFilters = $property->getValue($query);

            if (! $allowedFilters) {
                return [];
            }

            $filters = [];
            foreach ($allowedFilters as $filter) {
                if ($filter instanceof AllowedFilter) {
                    $filterData = $this->extractFilterData($filter, $translationPrefix);
                    if ($filterData) {
                        $filters[$filterData['name']] = $filterData;
                    }
                } elseif (is_string($filter)) {
                    $filters[$filter] = [
                        'name' => $filter,
                        'type' => 'partial',
                        'label' => $this->getTranslatedLabel($filter, $translationPrefix),
                    ];
                }
            }

            return $filters;
        } catch (\ReflectionException $e) {
            return [];
        }
    }

    
    private function extractFilterData(
        AllowedFilter $filter,
        ?string $translationPrefix = null,
    ): ?array {
        try {
            $reflection = new \ReflectionClass($filter);

            
            $nameProperty = $reflection->getProperty('name');
            $nameProperty->setAccessible(true);
            $name = $nameProperty->getValue($filter);

            
            $filterClassName = get_class($filter);
            $type = $this->detectFilterType($filterClassName, $filter);

            return [
                'name' => $name,
                'type' => $type,
                'label' => $this->getTranslatedLabel($name, $translationPrefix),
            ];
        } catch (\ReflectionException $e) {
            return null;
        }
    }

    
    private function getTranslatedLabel(string $field, ?string $translationPrefix = null): string
    {
        if ($translationPrefix) {
            $translationKey = $translationPrefix.'.'.$field;

            
            $translated = __($translationKey);

            
            if ($translated !== $translationKey) {
                return $translated;
            }
        }

        
        return ucfirst(str_replace('_', ' ', $field));
    }

    
    private function detectFilterType(string $className, AllowedFilter $filter): string
    {
        
        try {
            $reflection = new \ReflectionClass($filter);
            if ($reflection->hasProperty('filterClass')) {
                $property = $reflection->getProperty('filterClass');
                $property->setAccessible(true);
                $filterClass = $property->getValue($filter);

                
                if (is_string($filterClass)) {
                    if (str_contains($filterClass, 'FiltersExact')) {
                        return 'exact';
                    }
                    if (str_contains($filterClass, 'FiltersPartial')) {
                        return 'partial';
                    }
                    if (str_contains($filterClass, 'FiltersScope')) {
                        return 'scope';
                    }
                }

                
                if (is_object($filterClass)) {
                    $filterClassName = get_class($filterClass);
                    if (str_contains($filterClassName, 'FiltersExact')) {
                        return 'exact';
                    }
                    if (str_contains($filterClassName, 'FiltersPartial')) {
                        return 'partial';
                    }
                    if (str_contains($filterClassName, 'FiltersScope')) {
                        return 'scope';
                    }
                }
            }
        } catch (\ReflectionException $e) {
            
        }

        return 'partial'; 
    }
}
