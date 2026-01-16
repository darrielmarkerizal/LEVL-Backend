<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class TagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var \Modules\Schemes\Models\Tag|null $tag */
        $tag = $this->route('tag');
        $tagId = $tag?->id;

        /** @var Unique $uniqueName */
        $uniqueName = Rule::unique('tags', 'name');
        if ($tagId) {
            $uniqueName = $uniqueName->ignore($tagId);
        }

        return [
            'name' => ['required_without:names', 'nullable', 'string', 'min:1', 'max:100', $uniqueName],
            'names' => ['required_without:name', 'array', 'min:1'],
            'names.*' => ['string', 'min:1', 'max:100'],
        ];
    }
}


