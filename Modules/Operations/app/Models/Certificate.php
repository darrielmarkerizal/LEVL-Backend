<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'user_id', 'course_id', 'certificate_number', 'file_path',
        'issued_at', 'expired_at', 'status'
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\Modules\Auth\Entities\User::class);
    }

    public function course()
    {
        return $this->belongsTo(\Modules\Schemes\Entities\Course::class);
    }
}
