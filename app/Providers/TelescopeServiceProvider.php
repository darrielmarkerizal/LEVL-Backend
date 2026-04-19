<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;

if (class_exists(\Laravel\Telescope\TelescopeApplicationServiceProvider::class)) {
    class TelescopeServiceProvider extends \Laravel\Telescope\TelescopeApplicationServiceProvider
    {
        
        public function register(): void
        {
            

            $this->hideSensitiveRequestDetails();

            $isLocal = $this->app->environment('local');
            $telescopeEnabled = config('telescope.enabled', false);

            Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {

                return $isLocal ||
                       $entry->isReportableException() ||
                       $entry->isFailedRequest() ||
                       $entry->isFailedJob() ||
                       $entry->isScheduledTask() ||
                       $entry->hasMonitoredTag();
            });

            Telescope::tag(function (IncomingEntry $entry) {
                if ($entry->type === 'request') {
                    $tags = ['status:'.$entry->content['response_status']];

                    if (isset($entry->content['duration'])) {
                        $tags[] = 'time:'.$entry->content['duration'].'ms';
                    }

                    if (isset($entry->content['memory'])) {
                        $tags[] = 'mem:'.$entry->content['memory'].'MB';
                    }

                    return $tags;
                }

                return [];
            });
        }

        
        protected function hideSensitiveRequestDetails(): void
        {
            if ($this->app->environment('local')) {
                return;
            }

            Telescope::hideRequestParameters(['_token']);

            Telescope::hideRequestHeaders([
                'cookie',
                'x-csrf-token',
                'x-xsrf-token',
            ]);
        }

        
        protected function gate(): void
        {
            Gate::define('viewTelescope', function ($user = null) {
                
                return true;
            });
        }
    }
}
