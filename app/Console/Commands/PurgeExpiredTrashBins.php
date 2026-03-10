<?php

namespace App\Console\Commands;

use App\Services\Trash\TrashBinService;
use Illuminate\Console\Command;

class PurgeExpiredTrashBins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trash:purge-expired';

    /**
     * The console command description.
     *
     * @var string
     */
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

    /**
     * Execute the console command.
     */
    public function handle(TrashBinService $service): int
    {
        $count = $service->purgeExpired();

        $this->info(__('messages.trash_bins.purge_success', ['count' => $count]));

        return self::SUCCESS;
    }
}
