<?php

declare(strict_types=1);

namespace Modules\Learning\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Laravel\Scout\Builder as ScoutBuilder;
use Modules\Auth\Models\User;
use Modules\Learning\Contracts\Repositories\SubmissionRepositoryInterface;
use Modules\Learning\Enums\SubmissionState;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Submission;

class SubmissionRepository extends BaseRepository implements SubmissionRepositoryInterface
{
        protected const DEFAULT_EAGER_LOAD = [
        'user:id,name,email',
        'assignment:id,title,deadline_at,tolerance_minutes,review_mode',
        'answers.question',
        'grade',
            'enrollment',
            'files',
            'previousSubmission',
            'appeal',
    ];

        protected const DETAILED_EAGER_LOAD = [
        'user:id,name,email',
        'assignment:id,title,deadline_at,tolerance_minutes,review_mode',
        'answers.question',
        'grade.grader:id,name,email',
        'appeal.reviewer:id,name,email',
    ];

    protected function model(): string
    {
        return Submission::class;
    }

    protected array $allowedFilters = ['assignment_id', 'user_id', 'status'];

    protected array $allowedSorts = ['id', 'created_at', 'submitted_at', 'graded_at'];

    protected string $defaultSort = '-created_at';

    protected array $with = ['user', 'enrollment'];

        public function listForAssignment(Assignment $assignment, ?User $user = null, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        $query = \Spatie\QueryBuilder\QueryBuilder::for(Submission::class)
            ->where('assignment_id', $assignment->id)
            ->allowedFilters([
                'status',
                \Spatie\QueryBuilder\AllowedFilter::exact('user_id'),
                \Spatie\QueryBuilder\AllowedFilter::exact('is_late'),
                \Spatie\QueryBuilder\AllowedFilter::callback('date_from', fn ($q, $v) => $q->where('submitted_at', '>=', $v)),
                \Spatie\QueryBuilder\AllowedFilter::callback('date_to', fn ($q, $v) => $q->where('submitted_at', '<=', $v)),
            ])
            ->allowedSorts(['submitted_at', 'created_at', 'score', 'status'])
            ->defaultSort('-created_at')
            ->with([
                'user:id,name,email',
                'enrollment:id,status',
                'files',
                'grade.grader:id,name,email',
                'answers.question:id,type,content,weight',
            ]);

        if ($user && $user->hasRole('Student')) {
            $query->where('user_id', $user->id);
        } elseif (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->paginate($perPage)->appends($filters);
    }

    public function search(string $query, array $filters = [], array $options = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $perPage = (int) ($options['per_page'] ?? 15);
        $sortBy = $options['sort_by'] ?? 'submitted_at';
        $sortDirection = strtolower($options['sort_direction'] ?? 'desc');

        $builder = Submission::search($query);

        if (isset($filters['state']) && $filters['state'] !== '') {
            $builder->where('state', $filters['state']);
        }

        if (isset($filters['assignment_id'])) {
            $builder->where('assignment_id', (int) $filters['assignment_id']);
        }

        // Apply complex filters via callback for Meilisearch
        $builder->callback(function ($meilisearch, $query, $options) use ($filters, $sortBy, $sortDirection) {
            $filterStrings = [];

            if (isset($filters['score_min'])) {
                $filterStrings[] = 'score >= ' . $filters['score_min'];
            }
            if (isset($filters['score_max'])) {
                $filterStrings[] = 'score <= ' . $filters['score_max'];
            }
            if (isset($filters['date_from'])) {
                $ts = Carbon::parse($filters['date_from'])->startOfDay()->timestamp;
                $filterStrings[] = 'submitted_at >= ' . $ts;
            }
            if (isset($filters['date_to'])) {
                $ts = Carbon::parse($filters['date_to'])->endOfDay()->timestamp;
                $filterStrings[] = 'submitted_at <= ' . $ts;
            }

            if (!empty($filterStrings)) {
                $options['filter'] = implode(' AND ', $filterStrings);
            }

            $options['sort'] = ["{$sortBy}:{$sortDirection}"];

            return $meilisearch->search($query, $options);
        });

        return $builder->query(fn ($q) => $q->with([
            'user:id,name,email',
            'assignment:id,title,deadline_at',
            'grade.grader:id,name,email',
            'answers.question:id,type,content,weight',
        ]))->paginate($perPage);
    }

    public function create(array $attributes): Submission
    {
        // Temporarily disable Scout observer to prevent serialization issues with enums during model creation
        return Submission::withoutSyncingToSearch(function () use ($attributes) {
            return Submission::create($attributes);
        });
    }

    public function update(Model|Submission $model, array $attributes): Model|Submission
    {
        $model->fill($attributes)->save();

        return $model;
    }

    public function updateSubmission(Submission $submission, array $attributes): Submission
    {
        $submission->fill($attributes)->save();

        return $submission;
    }

    public function delete(Model|Submission $model): bool
    {
        return $model->delete();
    }

    public function hasCompletedAssignment(int $assignmentId, int $studentId): bool
    {
        return Submission::where('assignment_id', $assignmentId)
            ->where('user_id', $studentId)
            ->where('status', \Modules\Learning\Enums\SubmissionStatus::Graded)
            ->exists();
    }

        public function latestCommittedSubmission(Assignment $assignment, int $userId): ?Submission
    {
        return Submission::query()
            ->where('assignment_id', $assignment->id)
            ->where('user_id', $userId)
            ->whereIn('status', ['submitted', 'late', 'graded'])
            ->with(self::DEFAULT_EAGER_LOAD)
            ->latest('id')
            ->first();
    }

        public function findHighestScore(int $studentId, int $assignmentId): ?Submission
    {
        return Submission::query()
            ->where('assignment_id', $assignmentId)
            ->where('user_id', $studentId)
            ->whereNotNull('score')
            ->whereNotIn('state', [SubmissionState::InProgress->value])
            ->with(self::DEFAULT_EAGER_LOAD)
            ->orderByDesc('score')
            ->first();
    }

        public function findByUserAndAssignment(int $userId, int $assignmentId): ?Submission
    {
        return Submission::query()
            ->where('assignment_id', $assignmentId)
            ->where('user_id', $userId)
            ->with(self::DEFAULT_EAGER_LOAD)
            ->first();
    }

        public function findByStudentAndAssignment(int $studentId, int $assignmentId): Collection
    {
        return Submission::query()
            ->where('assignment_id', $assignmentId)
            ->where('user_id', $studentId)
            ->whereNotIn('state', [SubmissionState::InProgress->value])
            ->with(self::DEFAULT_EAGER_LOAD)
            ->orderByDesc('submitted_at')
            ->get();
    }

        public function findWithDetails(int $submissionId): ?Submission
    {
        return Submission::query()
            ->where('id', $submissionId)
            ->with(self::DETAILED_EAGER_LOAD)
            ->first();
    }

        public function findWithAnswers(int $submissionId): ?Submission
    {
        return Submission::query()
            ->where('id', $submissionId)
            ->with([
                'user:id,name,email',
                'assignment:id,title,deadline_at,tolerance_minutes,review_mode',
                'answers.question:id,type,content,options,answer_key,weight,max_score',
                'grade',
            ])
            ->first();
    }

        public function countAttempts(int $studentId, int $assignmentId): int
    {
        return Submission::query()
            ->where('assignment_id', $assignmentId)
            ->where('user_id', $studentId)
            ->whereNotIn('state', [SubmissionState::InProgress->value])
            ->count();
    }

        public function getLastSubmissionTime(int $studentId, int $assignmentId): ?Carbon
    {
        $submission = Submission::query()
            ->where('assignment_id', $assignmentId)
            ->where('user_id', $studentId)
            ->whereNotNull('submitted_at')
            ->orderByDesc('submitted_at')
            ->first();

        return $submission?->submitted_at;
    }

        public function findPendingManualGrading(array $filters = []): Collection
    {
        $query = Submission::query()
            ->where('state', SubmissionState::PendingManualGrading->value)
            ->with([
                'user:id,name,email',
                'assignment:id,title,deadline_at',
                'answers' => function ($q) {
                    $q->whereNull('score')
                        ->orWhereHas('question', function ($q) {
                            $q->whereIn('type', ['essay', 'file_upload']);
                        });
                },
                'answers.question:id,type,content,weight',
            ])
            ->orderBy('submitted_at', 'asc');

        
        if (isset($filters['assignment_id'])) {
            $query->where('assignment_id', $filters['assignment_id']);
        }

        if (isset($filters['student_id'])) {
            $query->where('user_id', $filters['student_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('submitted_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (isset($filters['date_to'])) {
            $query->where('submitted_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        return $query->get();
    }

        protected function applyScoutFilters(ScoutBuilder $builder, array $filters): ScoutBuilder
    {
        
        if (isset($filters['state']) && $filters['state'] !== '') {
            $builder->where('state', $filters['state']);
        }

        
        if (isset($filters['assignment_id'])) {
            $builder->where('assignment_id', (int) $filters['assignment_id']);
        }

        
        
        if (isset($filters['score_min'])) {
            $builder->where('score', '>=', (float) $filters['score_min']);
        }

        if (isset($filters['score_max'])) {
            $builder->where('score', '<=', (float) $filters['score_max']);
        }

        
        
        if (isset($filters['date_from'])) {
            $fromTimestamp = Carbon::parse($filters['date_from'])->startOfDay()->timestamp;
            $builder->where('submitted_at', '>=', $fromTimestamp);
        }

        if (isset($filters['date_to'])) {
            $toTimestamp = Carbon::parse($filters['date_to'])->endOfDay()->timestamp;
            $builder->where('submitted_at', '<=', $toTimestamp);
        }

        return $builder;
    }

        public function filterByState(string $state): Collection
    {
        return Submission::query()
            ->where('state', $state)
            ->with([
                'user:id,name,email',
                'assignment:id,title,deadline_at',
                'grade.grader:id,name,email',
                'answers.question:id,type,content,weight',
            ])
            ->orderByDesc('submitted_at')
            ->get();
    }

        public function filterByScoreRange(float $min, float $max): Collection
    {
        return Submission::query()
            ->whereNotNull('score')
            ->where('score', '>=', $min)
            ->where('score', '<=', $max)
            ->with([
                'user:id,name,email',
                'assignment:id,title,deadline_at',
                'grade.grader:id,name,email',
                'answers.question:id,type,content,weight',
            ])
            ->orderByDesc('submitted_at')
            ->get();
    }

        public function filterByDateRange(string $from, string $to): Collection
    {
        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate = Carbon::parse($to)->endOfDay();

        return Submission::query()
            ->whereNotNull('submitted_at')
            ->whereBetween('submitted_at', [$fromDate, $toDate])
            ->with([
                'user:id,name,email',
                'assignment:id,title,deadline_at',
                'grade.grader:id,name,email',
                'answers.question:id,type,content,weight',
            ])
            ->orderByDesc('submitted_at')
            ->get();
    }
}
