<?php

namespace Modules\Notifications\Entities;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'code', 'title', 'body', 'channel'
    ];
}
