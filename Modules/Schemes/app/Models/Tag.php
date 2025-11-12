<?php

namespace Modules\Schemes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    protected $casts = [
        'name' => 'string',
        'slug' => 'string',
    ];

    protected $hidden = [
        'pivot',
        'created_at',
        'updated_at',
    ];

    protected $visible = [
        'id',
        'name',
        'slug',
    ];

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(
            Course::class,
            'course_tag_pivot',
            'tag_id',
            'course_id'
        )->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}


