<?php

namespace Modules\Schemes\Models;

use Illuminate\Database\Eloquent\Model;

class CourseTag extends Model
{
    protected $table = 'course_tag_pivot';

    protected $fillable = [
        'course_id',
        'tag_id',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
