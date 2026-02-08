<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Forums\Models\Thread;

class SyncMeilisearchSettings extends Command
{
    protected $signature = 'scout:sync-settings {model}';

    protected $description = 'Sync Meilisearch settings for a model';

    public function handle(): int
    {
        $modelClass = $this->argument('model');

        if (! class_exists($modelClass)) {
            $this->error("Model {$modelClass} not found");

            return self::FAILURE;
        }

        $model = new $modelClass;

        if (! method_exists($model, 'searchableOptions')) {
            $this->error("Model {$modelClass} does not have searchableOptions() method");

            return self::FAILURE;
        }

        $engine = $model->searchableUsing();
        $index = $engine->index($model->searchableAs());

        $settings = $model->searchableOptions();

        if (isset($settings['filterableAttributes'])) {
            $index->updateFilterableAttributes($settings['filterableAttributes']);
            $this->info('Updated filterable attributes: '.implode(', ', $settings['filterableAttributes']));
        }

        if (isset($settings['sortableAttributes'])) {
            $index->updateSortableAttributes($settings['sortableAttributes']);
            $this->info('Updated sortable attributes: '.implode(', ', $settings['sortableAttributes']));
        }

        if (isset($settings['searchableAttributes'])) {
            $index->updateSearchableAttributes($settings['searchableAttributes']);
            $this->info('Updated searchable attributes: '.implode(', ', $settings['searchableAttributes']));
        }

        $this->info("Settings synced successfully for {$modelClass}");

        return self::SUCCESS;
    }
}
