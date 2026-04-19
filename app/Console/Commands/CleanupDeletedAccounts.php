<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupDeletedAccounts extends Command
{
    
    protected $signature = 'auth:cleanup-deleted-accounts';

    
    protected $description = 'Permanently delete accounts that have been soft-deleted for more than 30 days.';

    
    public function handle()
    {
        $days = (int) ((\Modules\Common\Models\SystemSetting::get('auth_account_retention_days', 30)) ?? 30);

        $users = \Modules\Auth\Models\User::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays($days))
            ->get();

        $count = $users->count();

        foreach ($users as $user) {
            $user->forceDelete();
        }

        $this->info(__('messages.account.cleanup_success', ['count' => $count]));
    }
}
