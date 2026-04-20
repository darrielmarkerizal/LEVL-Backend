<?php

declare(strict_types=1);

namespace Modules\Gamification\Database\Seeders;

use App\Support\SeederDate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Gamification\Enums\PointReason;
use Modules\Gamification\Enums\PointSourceType;
use Modules\Gamification\Models\XpSource;

class LedgerPointsFromLearningFactsSeeder extends Seeder
{
    public function run(): void
    {
        if (config('seeding.mode') !== 'uat') {
            return;
        }

        $this->command->info('UAT: rebuilding point ledger from learning facts...');

        $studentIds = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'Student')
            ->where('model_has_roles.model_type', 'Modules\\Auth\\Models\\User')
            ->pluck('users.id')
            ->all();

        if ($studentIds === []) {
            return;
        }

        DB::table('points')->whereIn('user_id', $studentIds)->delete();
        DB::table('user_badges')->whereIn('user_id', $studentIds)->delete();

        if (Schema::hasTable('gamification_event_logs')) {
            DB::table('gamification_event_logs')->whereIn('user_id', $studentIds)->delete();
        }

        $xpAmounts = XpSource::query()
            ->whereIn('code', [
                'lesson_completed',
                'quiz_passed',
                'assignment_submitted',
                'perfect_score',
            ])
            ->pluck('xp_amount', 'code');

        $lessonXp = (int) ($xpAmounts['lesson_completed'] ?? 50);
        $quizXp = (int) ($xpAmounts['quiz_passed'] ?? 80);
        $assignXp = (int) ($xpAmounts['assignment_submitted'] ?? 100);
        $perfectXp = (int) ($xpAmounts['perfect_score'] ?? 50);

        $now = SeederDate::randomPastDateTimeBetween(1, 180);
        $pointRows = [];
        $logRows = [];

        $lessonProgress = DB::table('lesson_progress')
            ->join('enrollments', 'lesson_progress.enrollment_id', '=', 'enrollments.id')
            ->whereIn('enrollments.user_id', $studentIds)
            ->where('lesson_progress.status', 'completed')
            ->select(
                'enrollments.user_id',
                'lesson_progress.lesson_id',
                'lesson_progress.completed_at',
                'lesson_progress.updated_at',
            )
            ->get();

        foreach ($lessonProgress as $row) {
            $uid = (int) $row->user_id;
            $lid = (int) $row->lesson_id;
            $occ = $row->completed_at ?? $row->updated_at ?? $now;
            $pointRows[] = $this->pointRow(
                $uid,
                PointSourceType::Lesson->value,
                $lid,
                $lessonXp,
                PointReason::LessonCompleted->value,
                'lesson_completed',
                $occ,
                $now
            );
            $logRows[] = $this->logRow($uid, 'lesson_completed', PointSourceType::Lesson->value, $lid, ['lesson_id' => $lid], $occ);
        }

        $quizPassing = DB::table('quizzes')->pluck('passing_grade', 'id');

        $quizSubs = DB::table('quiz_submissions')
            ->whereIn('user_id', $studentIds)
            ->whereIn('status', ['graded', 'submitted'])
            ->get();

        foreach ($quizSubs as $qs) {
            $qid = (int) $qs->quiz_id;
            $passing = (float) ($quizPassing[$qid] ?? 75);
            $final = (float) ($qs->final_score ?? $qs->score ?? 0);
            if ($final < $passing) {
                continue;
            }
            $uid = (int) $qs->user_id;
            $occ = $qs->submitted_at ?? $qs->created_at ?? $now;
            $pointRows[] = $this->pointRow(
                $uid,
                PointSourceType::Quiz->value,
                $qid,
                $quizXp,
                PointReason::QuizPassed->value,
                'quiz_passed',
                $occ,
                $now
            );
            $logRows[] = $this->logRow($uid, 'quiz_passed', PointSourceType::Quiz->value, $qid, ['quiz_id' => $qid, 'score' => $final], $occ);
        }

        $submissions = DB::table('submissions')
            ->whereIn('user_id', $studentIds)
            ->whereIn('status', ['submitted', 'graded'])
            ->get();

        foreach ($submissions as $sub) {
            $uid = (int) $sub->user_id;
            $aid = (int) $sub->assignment_id;
            $occ = $sub->submitted_at ?? $sub->created_at ?? $now;
            $pointRows[] = $this->pointRow(
                $uid,
                PointSourceType::Assignment->value,
                $aid,
                $assignXp,
                PointReason::AssignmentSubmitted->value,
                'assignment_submitted',
                $occ,
                $now
            );
            $logRows[] = $this->logRow($uid, 'assignment_submitted', PointSourceType::Assignment->value, $aid, ['assignment_id' => $aid], $occ);

            if ($sub->status === 'graded' && $sub->score !== null && (float) $sub->score >= 100) {
                $pointRows[] = $this->pointRow(
                    $uid,
                    PointSourceType::Assignment->value,
                    $aid,
                    $perfectXp,
                    PointReason::PerfectScore->value,
                    'perfect_score',
                    $occ,
                    $now
                );
                $logRows[] = $this->logRow($uid, 'perfect_score', PointSourceType::Assignment->value, $aid, ['assignment_id' => $aid], $occ);
            }
        }

        foreach (array_chunk($pointRows, 500) as $chunk) {
            DB::table('points')->insertOrIgnore($chunk);
        }

        if (Schema::hasTable('gamification_event_logs') && $logRows !== []) {
            foreach (array_chunk($logRows, 500) as $chunk) {
                DB::table('gamification_event_logs')->insert($chunk);
            }
        }

        $this->command->info('UAT: inserted '.count($pointRows).' point ledger rows (deduped by unique index).');
    }

    private function pointRow(
        int $userId,
        string $sourceType,
        int $sourceId,
        int $points,
        string $reason,
        string $xpCode,
        string $occurredAt,
        string $now
    ): array {
        return [
            'user_id' => $userId,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'points' => $points,
            'reason' => $reason,
            'description' => null,
            'xp_source_code' => $xpCode,
            'old_level' => null,
            'new_level' => null,
            'metadata' => json_encode(['seed_mode' => 'uat']),
            'ip_address' => null,
            'user_agent' => null,
            'created_at' => $occurredAt,
            'updated_at' => $now,
        ];
    }

    private function logRow(
        int $userId,
        string $eventType,
        string $sourceType,
        int $sourceId,
        array $payload,
        string $occurredAt
    ): array {
        return [
            'user_id' => $userId,
            'event_type' => $eventType,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'payload' => json_encode($payload),
            'created_at' => $occurredAt,
        ];
    }
}
