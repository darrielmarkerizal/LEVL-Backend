<?php

namespace App\Support\Traits;

use Illuminate\Http\Request;

trait HandlesFiltering
{
    protected function extractFilterParams(Request $request): array
    {
        $params = [
            'filter' => $request->input('filter', []),
            'sort' => $request->input('sort'),
            'page' => $request->input('page', 1),
            'per_page' => $request->input('per_page', 15),
            'search' => $request->input('search'),
        ];

        // Remove null values to avoid passing unnecessary parameters
        return array_filter($params, fn ($value) => ! is_null($value));
    }
}
