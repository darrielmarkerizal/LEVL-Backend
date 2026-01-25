<?php

declare(strict_types=1);

namespace Modules\Grading\Models;

use Illuminate\Database\Eloquent\Model;

class GradeReview extends Model
{
    protected $fillable = [
        'grade_id',
        'requested_by',
        'reason',
        'response',
        'reviewed_by',
        'status',
    ];

    public function grade(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function requester(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\Auth\Models\User::class, 'requested_by');
    }

    public function reviewer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\Auth\Models\User::class, 'reviewed_by');
    }
}
