<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupDeletedAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:cleanup-deleted-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete accounts that have been soft-deleted for more than 30 days.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) ((\Modules\Common\Models\SystemSetting::get('auth_account_retention_days', 30)) ?? 30);
        
        $users = \Modules\Auth\Models\User::onlyTrashed()
            ->where('account_status', 'deleted')
            ->where('deleted_at', '<=', now()->subDays($days))
            ->get();

        $count = $users->count();

        foreach ($users as $user) {
            $user->forceDelete();
        }

        $this->info(__('messages.account.cleanup_success', ['count' => $count]));
    }
}
