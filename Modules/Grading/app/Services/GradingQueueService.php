<?php

declare(strict_types=1);

namespace Modules\Grading\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Modules\Grading\Models\Grade;
use Modules\Learning\Enums\SubmissionState;
use Modules\Learning\Models\Submission;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GradingQueueService
{
    public function getGradingQueue(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);
        $search = data_get($filters, 'search');

        $cleanFilters = Arr::except($filters, ['search']);
        $request = new Request($cleanFilters);

        $query = QueryBuilder::for(Submission::class, $request)
            ->with([
                'user:id,name,email',
                'assignment:id,title,max_score',
                'answers.question'
            ])
            ->allowedFilters([
                AllowedFilter::exact('assignment_id'),
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('state'),
                AllowedFilter::exact('is_late'),
                AllowedFilter::callback('date_from', fn ($q, $v) => $q->where('submitted_at', '>=', $v)),
                AllowedFilter::callback('date_to', fn ($q, $v) => $q->where('submitted_at', '<=', $v)),
            ])
            ->allowedSorts(['submitted_at', 'created_at', 'updated_at'])
            ->allowedIncludes(['assignment.course', 'user.profile']);

        if ($search && trim((string) $search) !== '') {
            $ids = Submission::search($search)->keys();
            $query->whereIn('id', $ids);
        }

        if (!$request->has('filter.state')) {
            $query->where('state', SubmissionState::PendingManualGrading->value);
        }

        $query->whereHas('answers', function ($q) {
            $q->whereNull('score')
                ->whereHas('question', function ($qq) {
                    $qq->whereIn('type', [
                        \Modules\Learning\Enums\QuestionType::Essay->value,
                        \Modules\Learning\Enums\QuestionType::FileUpload->value
                    ]);
                });
        });

        return $query
            ->defaultSort('submitted_at')
            ->paginate($perPage)
            ->appends($filters);
    }

    public function getGradingStatusDetails(int $submissionId): array
    {
        $submission = Submission::with(['answers.question', 'grade'])->findOrFail($submissionId);

        $gradedCount = $submission->answers->filter(fn ($a) => $a->score !== null)->count();
        $totalCount = $submission->answers->count();
        
        $isComplete = $gradedCount === $totalCount;
        
        if (!empty($submission->question_set)) {
             $questionSet = $submission->question_set;
             $answersInSet = $submission->answers->whereIn('question_id', $questionSet);
             $gradedCount = $answersInSet->filter(fn ($a) => $a->score !== null)->count();
             $totalCount = $answersInSet->count();
             $isComplete = $gradedCount === $totalCount;
        }

        return [
            'submission_id' => $submission->id,
            'is_complete' => $isComplete,
            'graded_questions' => $gradedCount,
            'total_questions' => $totalCount,
            'can_finalize' => $isComplete,
            'can_release' => $isComplete && $submission->grade && ! $submission->grade->is_draft,
        ];
    }

    public function getDraftGrade(int $submissionId): ?Grade
    {
        return Grade::where('submission_id', $submissionId)
            ->where('is_draft', true)
            ->with(['grader:id,name'])
            ->first();
    }
}
