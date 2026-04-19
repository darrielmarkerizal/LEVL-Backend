<?php

declare(strict_types=1);

namespace Modules\Common\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\Common\Traits\PgSearchable;
use Spatie\Activitylog\Models\Activity;


class AuditLog extends Activity
{
    use PgSearchable;

    
    protected $table = 'activity_log';

    
    protected array $searchable_columns = [
        'description',
    ];

    
    public function scopeActionIn($query, array $actions)
    {
        return $query->whereIn('description', $actions);
    }

    
    public function scopeCreatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [
            \Carbon\Carbon::parse($startDate)->startOfDay(),
            \Carbon\Carbon::parse($endDate)->endOfDay(),
        ]);
    }

    
    public function scopeContextContains($query, $search)
    {
        return $query->whereRaw('properties::text ILIKE ?', ["%{$search}%"]);
    }

    
    public function scopeAssignmentId($query, $id)
    {
        return $query->whereRaw("properties->>'assignment_id' = ?", [(string) $id]);
    }

    
    public function scopeStudentId($query, $id)
    {
        return $query->whereRaw("properties->>'student_id' = ?", [(string) $id]);
    }

    
    public function actor()
    {
        return $this->causer();
    }

    
    public function getActorIdAttribute()
    {
        return $this->causer_id;
    }

    
    public function getActorTypeAttribute()
    {
        return $this->causer_type;
    }

    
    public function getContextAttribute()
    {
        return $this->properties;
    }

    
    public function getActionAttribute()
    {
        return $this->description;
    }

    
    public function scopeForAction($query, string $action)
    {
        return $query->where('description', $action);
    }

    
    public function scopeForSubject(Builder $query, Model $subject): Builder
    {
        return $query->where('subject_type', get_class($subject))
            ->where('subject_id', $subject->getKey());
    }

    
    public function scopeForActor($query, $model)
    {
        return $query->where('causer_type', get_class($model))
            ->where('causer_id', $model->getKey());
    }

    
    public function scopeForUser($query, $user)
    {
        $userId = is_object($user) ? $user->id : $user;

        return $query->where('causer_id', $userId);
    }

    
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    
    public static function logAction(
        string $action,
        $subject = null,
        $actor = null,
        array $context = []
    ): Activity {
        $activity = activity()
            ->withProperties($context)
            ->log($action);

        if ($subject) {
            $activity->subject()->associate($subject);
        }

        if ($actor) {
            $activity->causer()->associate($actor);
        } elseif (Auth::check()) {
            $activity->causer()->associate(Auth::user());
        }

        $activity->save();

        return $activity;
    }

    
    public static function log(
        string $event,
        $target = null,
        $actor = null,
        array $properties = []
    ): Activity {
        return static::logAction($event, $target, $actor, $properties);
    }
}
