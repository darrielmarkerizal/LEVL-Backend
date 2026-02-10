<?php

declare(strict_types=1);

namespace Modules\Common\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Common\Traits\PgSearchable;

class LevelConfig extends Model
{
    use PgSearchable;

    protected array $searchable_columns = [
        'name',
        'description',
    ];

    protected $table = 'level_configs';

    protected $fillable = [
        'level',
        'name',
        'xp_required',
        'rewards',
    ];

    protected $casts = [
        'level' => 'integer',
        'xp_required' => 'integer',
        'rewards' => 'array',
    ];


}
