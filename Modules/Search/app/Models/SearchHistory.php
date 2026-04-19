<?php

namespace Modules\Search\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;

class SearchHistory extends Model
{
    
    protected $table = 'search_history';

    
    public $timestamps = false;

    
    protected $fillable = [
        'user_id',
        'query',
        'filters',
        'results_count',
        'clicked_result_id',
        'clicked_result_type',
    ];

    
    protected $casts = [
        'filters' => 'array',
        'results_count' => 'integer',
        'created_at' => 'datetime',
    ];

    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
