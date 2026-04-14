<?php

declare(strict_types=1);

namespace Modules\Grading\Services;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Grading\Models\Grade;
use Modules\Learning\Models\QuizSubmission;
use Modules\Learning\Models\Submission;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Exceptions\InvalidFilterQuery;
use Spatie\QueryBuilder\QueryBuilder;

class GradingQueueService
{
    private const ALLOWED_FILTERS = ['status', 'user_id', 'assignment_id', 'quiz_id', 'grading_status', 'date_from', 'date_to'];

    private const ASSIGNMENT_FILTERS = ['status', 'user_id', 'assignment_id', 'date_from', 'date_to'];

    private const QUIZ_FILTERS = ['status', 'user_id', 'quiz_id', 'grading_status', 'date_from', 'date_to'];

    private const QUIZ_ONLY_FILTERS = ['quiz_id', 'grading_status'];

    private const ASSIGNMENT_ONLY_FILTERS = ['assignment_id'];

    public function getGradingQueue(array $filters = []): LengthAwarePaginator
    {
        $this->validateFilters((array) data_get($filters, 'filter', []));

        $perPage = max(1, min((int) ($filters['per_page'] ?? 15), 100));
        $page = max(1, (int) ($filters['page'] ?? 1));

        $filterData = (array) data_get($filters, 'filter', []);

        $hasQuizOnlyFilter = ! empty(array_intersect(array_keys($filterData), self::QUIZ_ONLY_FILTERS));
        $hasAssignmentOnlyFilter = ! empty(array_intersect(array_keys($filterData), self::ASSIGNMENT_ONLY_FILTERS));

        $assignmentSubmissions = $hasQuizOnlyFilter ? collect() : $this->buildAssignmentQuery($filterData)->get();
        $quizSubmissions = $hasAssignmentOnlyFilter ? collect() : $this->buildQuizQuery($filterData)->get();

        $all = $assignmentSubmissions->concat($quizSubmissions)
            ->sortByDesc('submitted_at')
            ->values();

        return new LengthAwarePaginator($all->forPage($page, $perPage)->values(), $all->count(), $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    public function getGradingStatusDetails(int $submissionId): array
    {
        $submission = Submission::with(['answers', 'grade'])->findOrFail($submissionId);

        $gradedCount = $submission->answers->filter(fn ($a) => $a->score !== null)->count();
        $totalCount = $submission->answers->count();

        $isComplete = $gradedCount === $totalCount;

        return [
            'submission_id' => $submission->id,
            'is_complete' => $isComplete,
            'graded_questions' => $gradedCount,
            'total_questions' => $totalCount,
            'can_finalize' => $isComplete,
            'can_release' => $isComplete && $submission->grade && ! $submission->grade->is_draft,
        ];
    }

    public function getGrade(int $submissionId): ?Grade
    {
        return Grade::where('submission_id', $submissionId)
            ->with(['grader:id,name'])
            ->latest()
            ->first();
    }

    private function validateFilters(array $filterData): void
    {
        $unknown = array_diff(array_keys($filterData), self::ALLOWED_FILTERS);

        if (! empty($unknown)) {
            throw InvalidFilterQuery::filtersNotAllowed(
                Collection::make($unknown),
                Collection::make(self::ALLOWED_FILTERS)
            );
        }
    }

    private function buildAssignmentQuery(array $filterData): QueryBuilder
    {
        $relevant = array_intersect_key($filterData, array_flip(self::ASSIGNMENT_FILTERS));
        $request = new Request(['filter' => $relevant]);

        return QueryBuilder::for(Submission::class, $request)
            ->with(['user:id,name,email', 'assignment:id,title,max_score', 'answers'])
            ->allowedFilters([
                AllowedFilter::callback('status', fn ($q, $v) => $q->where('state', $v)),
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('assignment_id'),
                AllowedFilter::callback('date_from', fn ($q, $v) => $q->where('submitted_at', '>=', $v)),
                AllowedFilter::callback('date_to', fn ($q, $v) => $q->where('submitted_at', '<=', $v)),
            ]);
    }

    private function buildQuizQuery(array $filterData): QueryBuilder
    {
        $relevant = array_intersect_key($filterData, array_flip(self::QUIZ_FILTERS));
        $request = new Request(['filter' => $relevant]);

        return QueryBuilder::for(QuizSubmission::class, $request)
            ->with(['user:id,name,email', 'quiz:id,title', 'answers.question'])
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('quiz_id'),
                AllowedFilter::exact('grading_status'),
                AllowedFilter::callback('date_from', fn ($q, $v) => $q->where('submitted_at', '>=', $v)),
                AllowedFilter::callback('date_to', fn ($q, $v) => $q->where('submitted_at', '<=', $v)),
            ]);
    }
}
