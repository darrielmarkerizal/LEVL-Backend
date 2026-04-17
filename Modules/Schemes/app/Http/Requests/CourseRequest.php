<?php

declare(strict_types=1);

namespace Modules\Schemes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Common\Http\Requests\Concerns\HasApiValidation;
use Modules\Schemes\Http\Requests\Concerns\HasSchemesRequestRules;
use Modules\Schemes\Models\Course;

class CourseRequest extends FormRequest
{
    use HasApiValidation, HasSchemesRequestRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $course = $this->route('course');
        $courseId = $this->resolveCourseId($course);

        return $this->rulesCourse($courseId);
    }

    public function messages(): array
    {
        return $this->messagesCourse();
    }

    protected function prepareForValidation(): void
    {
        $fields = ['tags', 'outcomes', 'instructor_ids'];
        $payload = [];
        foreach ($fields as $field) {
            $val = $this->input($field);
            if (is_string($val)) {
                $decoded = $this->decodeJsonArrayString($val);
                if (is_array($decoded)) {
                    $payload[$field] = $decoded;
                }
            }
        }
        if (! empty($payload)) {
            $this->merge($payload);
        }
    }

    private function decodeJsonArrayString(string $value): ?array
    {
        $trim = trim($value);
        if ($trim === '') {
            return null;
        }

        if ($trim[0] === '[') {
            $decoded = json_decode($trim, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        $urldec = urldecode($trim);
        if ($urldec !== $trim && strlen($urldec) > 0 && $urldec[0] === '[') {
            $decoded = json_decode($urldec, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        if (\Illuminate\Support\Arr::has($data, 'tags')) {
            $data['tags_list'] = $data['tags'];
            unset($data['tags']);
        }

        return $data;
    }

    private function resolveCourseId(mixed $course): int
    {
        if (is_object($course) && isset($course->id)) {
            return (int) $course->id;
        }

        if (is_scalar($course)) {
            $courseValue = (string) $course;
            if (is_numeric($courseValue)) {
                return (int) $courseValue;
            }

            $resolvedId = Course::query()
                ->where('slug', $courseValue)
                ->value('id');

            return (int) ($resolvedId ?? 0);
        }

        return 0;
    }
}
