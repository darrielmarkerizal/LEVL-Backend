<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Trash\Services\TrashBinService;

class PurgeExpiredTrashBins extends Command
{
    
    protected $signature = 'trash:purge-expired';

    
    protected $description = 'Purge expired trash bins';

    public function __construct()
    {
        parent::__construct();

        $this->description = __('messages.trash_bins.purge_description');
    }

    public function getDescription(): string
    {
        return __('messages.trash_bins.purge_description');
    }

    
    public function handle(TrashBinService $service): int
    {
        $count = $service->purgeExpired();

        $this->info(__('messages.trash_bins.purge_success', ['count' => $count]));

        return self::SUCCESS;
    }
}
