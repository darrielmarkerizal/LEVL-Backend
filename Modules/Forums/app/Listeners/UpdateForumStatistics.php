<?php

namespace Modules\Forums\Listeners;

use Carbon\Carbon;
use Modules\Forums\Events\ReplyCreated;
use Modules\Forums\Events\ThreadCreated;
use Modules\Forums\Repositories\ForumStatisticsRepository;

class UpdateForumStatistics
{
    protected ForumStatisticsRepository $statisticsRepository;

    public function __construct(ForumStatisticsRepository $statisticsRepository)
    {
        $this->statisticsRepository = $statisticsRepository;
    }

     
    public function handle($event): void
    {
        $now = Carbon::now();
        $periodStart = $now->copy()->startOfMonth();
        $periodEnd = $now->copy()->endOfMonth();

        if ($event instanceof ThreadCreated) {
            $thread = $event->thread;

            
            $this->statisticsRepository->updateSchemeStatistics(
                $thread->scheme_id,
                $periodStart,
                $periodEnd
            );

            
            $this->statisticsRepository->updateUserStatistics(
                $thread->scheme_id,
                $thread->author_id,
                $periodStart,
                $periodEnd
            );
        } elseif ($event instanceof ReplyCreated) {
            $reply = $event->reply;
            $thread = $reply->thread;

            
            $this->statisticsRepository->updateSchemeStatistics(
                $thread->scheme_id,
                $periodStart,
                $periodEnd
            );

            
            $this->statisticsRepository->updateUserStatistics(
                $thread->scheme_id,
                $reply->author_id,
                $periodStart,
                $periodEnd
            );
        }
    }
}
