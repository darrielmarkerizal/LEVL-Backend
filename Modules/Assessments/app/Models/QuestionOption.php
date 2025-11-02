<?php

namespace Modules\Assessments\Entities;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    protected $fillable = [
        'question_id', 'option_text', 'is_correct', 'order'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
