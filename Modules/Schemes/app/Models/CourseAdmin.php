<?php

declare(strict_types=1);

namespace Modules\Schemes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseAdmin extends Model
{
    
    protected $table = 'course_admins';

    
    public $timestamps = true;

    
    protected $fillable = [
        'course_id',
        'user_id',
    ];

    
    public function course(): BelongsTo
    {
        return $this->belongsTo(\Modules\Schemes\Models\Course::class);
    }

    
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Auth\Models\User::class);
    }
}
