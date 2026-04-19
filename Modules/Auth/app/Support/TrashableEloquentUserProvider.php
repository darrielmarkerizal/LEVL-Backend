<?php

declare(strict_types=1);

namespace Modules\Auth\Support;

use Illuminate\Auth\EloquentUserProvider;

class TrashableEloquentUserProvider extends EloquentUserProvider
{
    
    public function retrieveById($identifier)
    {
        $model = $this->createModel();

        return $this->newModelQuery($model)
            ->withTrashed()
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();
    }
}
