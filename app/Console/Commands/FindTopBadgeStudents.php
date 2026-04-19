<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Gamification\Models\UserBadge;

class FindTopBadgeStudents extends Command
{
    protected $signature = 'gamification:top-badge-students 
                            {--limit=10 : Number of top students to display}
                            {--detailed : Show detailed badge information}';

    protected $description = 'Find students with the most badges';

    public function handle()
    {
        $limit = (int) $this->option('limit');
        $detailed = $this->option('detailed');

        $this->info('=== TOP STUDENTS BY BADGE COUNT ===');
        $this->newLine();

        
        $topStudents = User::role('Student')
            ->withCount('badges')
            ->with(['gamificationStats' => function ($query) {
                $query->select('user_id', 'total_xp', 'global_level');
            }])
            ->orderByDesc('badges_count')
            ->limit($limit)
            ->get(['id', 'name', 'email']);

        if ($topStudents->isEmpty()) {
            $this->warn('No students found with badges.');

            return 0;
        }

        
        $headers = ['Rank', 'Name', 'Email', 'Badges', 'Level', 'Total XP'];
        $rows = [];

        foreach ($topStudents as $index => $student) {
            $rows[] = [
                '#'.($index + 1),
                $student->name,
                $student->email,
                $student->badges_count,
                $student->gamificationStats?->global_level ?? 'N/A',
                $student->gamificationStats?->total_xp ?? 'N/A',
            ];
        }

        $this->table($headers, $rows);

        
        if ($detailed && $topStudents->isNotEmpty()) {
            $this->showDetailedInfo($topStudents->first());
        }

        
        $this->showSummaryStatistics();

        return 0;
    }

    protected function showDetailedInfo(User $student)
    {
        $this->newLine();
        $this->info('=== DETAILED BADGE INFORMATION ===');
        $this->line("Top Student: {$student->name} ({$student->email})");
        $this->line("Total Badges: {$student->badges_count}");
        $this->newLine();

        
        $badges = UserBadge::where('user_id', $student->id)
            ->with(['badge' => function ($query) {
                $query->select('id', 'name', 'code', 'category', 'rarity', 'type');
            }])
            ->orderBy('earned_at', 'desc')
            ->get();

        $badgeRows = [];
        foreach ($badges as $userBadge) {
            $badgeRows[] = [
                $userBadge->badge->name ?? 'Unknown',
                $userBadge->badge->category ?? 'N/A',
                $userBadge->badge->rarity?->value ?? 'N/A',
                $userBadge->earned_at?->format('Y-m-d H:i') ?? 'N/A',
            ];
        }

        $this->table(
            ['Badge Name', 'Category', 'Rarity', 'Earned At'],
            $badgeRows
        );

        
        $this->showBadgeBreakdown($student->id);
    }

    protected function showBadgeBreakdown(int $userId)
    {
        $this->newLine();
        $this->info('Badge Breakdown by Category:');

        $byCategory = UserBadge::where('user_id', $userId)
            ->join('badges', 'user_badges.badge_id', '=', 'badges.id')
            ->select('badges.category', DB::raw('COUNT(*) as count'))
            ->groupBy('badges.category')
            ->orderByDesc('count')
            ->get();

        foreach ($byCategory as $stat) {
            $this->line('  - '.ucfirst($stat->category ?? 'Unknown').": {$stat->count} badges");
        }

        $this->newLine();
        $this->info('Badge Breakdown by Rarity:');

        $byRarity = UserBadge::where('user_id', $userId)
            ->join('badges', 'user_badges.badge_id', '=', 'badges.id')
            ->select('badges.rarity', DB::raw('COUNT(*) as count'))
            ->groupBy('badges.rarity')
            ->orderByDesc('count')
            ->get();

        foreach ($byRarity as $stat) {
            $this->line('  - '.ucfirst($stat->rarity ?? 'Unknown').": {$stat->count} badges");
        }
    }

    protected function showSummaryStatistics()
    {
        $this->newLine();
        $this->info('=== SUMMARY STATISTICS ===');

        $totalStudents = User::role('Student')->count();
        $studentsWithBadges = User::role('Student')->has('badges')->count();
        $totalBadgesAwarded = UserBadge::count();
        $averageBadges = $studentsWithBadges > 0
            ? round($totalBadgesAwarded / $studentsWithBadges, 2)
            : 0;
        $percentage = $totalStudents > 0
            ? round(($studentsWithBadges / $totalStudents) * 100, 1)
            : 0;

        $this->line("Total Students: {$totalStudents}");
        $this->line("Students with Badges: {$studentsWithBadges} ({$percentage}%)");
        $this->line("Total Badges Awarded: {$totalBadgesAwarded}");
        $this->line("Average Badges per Student: {$averageBadges}");
    }
}
