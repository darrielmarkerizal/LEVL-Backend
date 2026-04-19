<?php

namespace App\Support\Traits;

trait ProvidesMetadata
{
    
    protected function buildMetadata(
        array $allowedSorts = [],
        array $filters = [],
        ?string $translationPrefix = null,
    ): array {
        
        $sortsWithLabels = array_map(function ($sort) use ($translationPrefix) {
            return [
                'field' => $sort,
                'label' => $this->getTranslatedSortLabel($sort, $translationPrefix),
            ];
        }, $allowedSorts);

        return [
            'sorts' => $sortsWithLabels,
            'filters' => $this->buildFiltersMetadata($filters, $translationPrefix),
        ];
    }

    
    protected function buildMetadataFromQuery(
        \Spatie\QueryBuilder\QueryBuilder $query,
        array $filterConfig = [],
        ?string $translationPrefix = null,
    ): array {
        $extractor = app(\App\Services\QueryBuilderMetadataExtractor::class);
        $extracted = $extractor->extractMetadata($query, $translationPrefix);

        
        $filtersMetadata = [];
        foreach ($extracted['filters'] as $filterKey => $filterData) {
            $config = $filterConfig[$filterKey] ?? [];

            $filtersMetadata[$filterKey] = [
                'label' => $config['label'] ?? $filterData['label'],
                'type' => $this->resolveFilterDisplayType($filterData, $config),
                'options' => $this->resolveFilterOptions($config),
            ];
        }

        return [
            'sorts' => $extracted['sorts'],
            'filters' => $filtersMetadata,
        ];
    }

    
    protected function buildFiltersMetadata(array $filters, ?string $translationPrefix = null): array
    {
        $metadata = [];

        foreach ($filters as $key => $config) {
            $metadata[$key] = [
                'label' => $config['label'] ??
                  $this->getTranslatedSortLabel(
                      $key,
                      $translationPrefix ? $translationPrefix.'.filters' : null,
                  ),
                'type' => $config['type'] ?? 'select',
                'options' => $config['options'] ?? [],
            ];
        }

        return $metadata;
    }

    
    private function getTranslatedSortLabel(string $field, ?string $translationPrefix = null): string
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

    
    private function resolveFilterDisplayType(array $filterData, array $config): string
    {
        
        if (isset($config['type'])) {
            return $config['type'];
        }

        
        if (
            str_contains($filterData['name'], 'is_') ||
            in_array($filterData['name'], ['active', 'published', 'enabled'])
        ) {
            return 'boolean';
        }

        
        if (str_contains($filterData['name'], 'date') || str_contains($filterData['name'], '_at')) {
            return 'date_range';
        }

        
        return 'select';
    }

    
    private function resolveFilterOptions(array $config): array
    {
        
        if (
            ! isset($config['enum']) &&
            ! isset($config['options']) &&
            ! isset($config['query']) &&
            ! isset($config['type'])
        ) {
            return [];
        }

        
        if (isset($config['options'])) {
            return is_array($config['options']) ? $config['options'] : [];
        }

        
        if (isset($config['enum'])) {
            return $this->resolveEnumOptions($config['enum']);
        }

        
        if (isset($config['query']) && is_callable($config['query'])) {
            return $config['query']();
        }

        
        if (isset($config['type']) && $config['type'] === 'boolean') {
            return $this->buildBooleanOptions(
                $config['true_label'] ?? __('master_data.filter_options.active'),
                $config['false_label'] ?? __('master_data.filter_options.inactive'),
            );
        }

        return [];
    }

    
    private function resolveEnumOptions(string $enumClass): array
    {
        if (! enum_exists($enumClass)) {
            return [];
        }

        return array_map(
            fn ($case) => [
                'value' => $case->value,
                'label' => method_exists($case, 'label') ? $case->label() : $case->name,
            ],
            $enumClass::cases(),
        );
    }

    
    protected function buildBooleanOptions(string $trueLabel, string $falseLabel): array
    {
        return [['value' => true, 'label' => $trueLabel], ['value' => false, 'label' => $falseLabel]];
    }

    
    protected function buildSelectOptions(array $options): array
    {
        $result = [];
        foreach ($options as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }
}
