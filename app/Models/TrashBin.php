<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Common\Traits\PgSearchable;

class TrashBin extends Model
{
    use PgSearchable;

    protected array $searchable_columns = [
        'resource_type',
        'trashable_type',
        'original_status',
        'trashed_status',
        'group_uuid',
    ];

    protected $table = 'trash_bins';

    protected $fillable = [
        'resource_type',
        'trashable_type',
        'trashable_id',
        'group_uuid',
        'root_resource_type',
        'root_resource_id',
        'original_status',
        'trashed_status',
        'deleted_by',
        'deleted_at',
        'expires_at',
        'metadata',
        'restored_at',
        'force_deleted_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'deleted_at' => 'datetime',
        'expires_at' => 'datetime',
        'restored_at' => 'datetime',
        'force_deleted_at' => 'datetime',
    ];
}
