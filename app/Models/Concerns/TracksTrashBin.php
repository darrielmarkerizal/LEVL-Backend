<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Services\Trash\TrashBinService;
use Illuminate\Database\Eloquent\Model;

trait TracksTrashBin
{
    public static function bootTracksTrashBin(): void
    {
        static::deleting(function (Model $model): void {
            if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                return;
            }

            app(TrashBinService::class)->beforeSoftDelete($model);
        });

        static::deleted(function (Model $model): void {
            if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                return;
            }

            app(TrashBinService::class)->recordSoftDeleted($model);
        });

        static::restored(function (Model $model): void {
            app(TrashBinService::class)->afterRestored($model);
        });

        static::forceDeleted(function (Model $model): void {
            app(TrashBinService::class)->afterForceDeleted($model);
        });
    }
}
