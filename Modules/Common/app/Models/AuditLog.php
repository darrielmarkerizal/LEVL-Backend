<?php

declare(strict_types=1);

namespace Modules\Common\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\Common\Traits\PgSearchable;
use Spatie\Activitylog\Models\Activity;

/**
 * AuditLog Model - Backward Compatibility Wrapper for Spatie Activity Log
 *
 * This model now extends Spatie Activity Log for unified logging.
 * The audit_logs table has been migrated to activity_log in Phase 3 optimization.
 *
 * All existing AuditLog::logAction() calls will now use Spatie Activity Log.
 *
 * @property int $id
 * @property string|null $log_name
 * @property string $description (maps to action)
 * @property int|null $subject_id
 * @property string|null $subject_type
 * @property int|null $causer_id (maps to actor_id)
 * @property string|null $causer_type (maps to actor_type)
 * @property array|null $properties (maps to context)
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class AuditLog extends Activity
{
    use PgSearchable;

    /**
     * The table associated with the model.
     */
    protected $table = 'activity_log';

    /**
     * Searchable columns for PgSearchable trait.
     */
    protected array $searchable_columns = [
        'description',
    ];

    /**
     * Scope for filtering by multiple actions.
     */
    public function scopeActionIn($query, array $actions)
    {
        return $query->whereIn('description', $actions);
    }

    /**
     * Scope for date range filtering.
     */
    public function scopeCreatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [
            \Carbon\Carbon::parse($startDate)->startOfDay(),
            \Carbon\Carbon::parse($endDate)->endOfDay(),
        ]);
    }

    /**
     * Scope for searching in properties JSON.
     */
    public function scopeContextContains($query, $search)
    {
        return $query->whereRaw('properties::text ILIKE ?', ["%{$search}%"]);
    }

    /**
     * Scope for assignment_id in properties.
     */
    public function scopeAssignmentId($query, $id)
    {
        return $query->whereRaw("properties->>'assignment_id' = ?", [(string) $id]);
    }

    /**
     * Scope for student_id in properties.
     */
    public function scopeStudentId($query, $id)
    {
        return $query->whereRaw("properties->>'student_id' = ?", [(string) $id]);
    }

    /**
     * Get the actor model (backward compatibility alias).
     */
    public function actor()
    {
        return $this->causer();
    }

    /**
     * Get actor_id (backward compatibility).
     */
    public function getActorIdAttribute()
    {
        return $this->causer_id;
    }

    /**
     * Get actor_type (backward compatibility).
     */
    public function getActorTypeAttribute()
    {
        return $this->causer_type;
    }

    /**
     * Get context (backward compatibility alias for properties).
     */
    public function getContextAttribute()
    {
        return $this->properties;
    }

    /**
     * Get action (backward compatibility alias for description).
     */
    public function getActionAttribute()
    {
        return $this->description;
    }

    /**
     * Scope a query to only include logs for a specific action.
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('description', $action);
    }

    /**
     * Scope a query to only include logs for a specific subject.
     */
    public function scopeForSubject(Builder $query, Model $subject): Builder
    {
        return $query->where('subject_type', get_class($subject))
            ->where('subject_id', $subject->getKey());
    }

    /**
     * Scope a query to only include logs for a specific actor.
     */
    public function scopeForActor($query, $model)
    {
        return $query->where('causer_type', get_class($model))
            ->where('causer_id', $model->getKey());
    }

    /**
     * Scope a query to only include logs for a specific user.
     */
    public function scopeForUser($query, $user)
    {
        $userId = is_object($user) ? $user->id : $user;

        return $query->where('causer_id', $userId);
    }

    /**
     * Scope a query to only include logs within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Log an action with backward compatibility.
     *
     * @param  string  $action  The action being logged (e.g., 'submission_created', 'grade_override')
     * @param  Model|null  $subject  The entity being acted upon
     * @param  Model|null  $actor  The entity performing the action
     * @param  array  $context  Additional context data
     */
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

    /**
     * Legacy log method for backward compatibility.
     */
    public static function log(
        string $event,
        $target = null,
        $actor = null,
        array $properties = []
    ): Activity {
        return static::logAction($event, $target, $actor, $properties);
    }
}
