<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $units = DB::table('units')->pluck('id');

        foreach ($units as $unitId) {
            $content = collect();

            $lessons = DB::table('lessons')
                ->where('unit_id', $unitId)
                ->whereNull('deleted_at')
                ->orderBy('order')
                ->orderBy('id')
                ->get(['id', 'order']);

            foreach ($lessons as $lesson) {
                $content->push([
                    'type' => 'lesson',
                    'id' => $lesson->id,
                    'original_order' => $lesson->order,
                ]);
            }

            $assignments = DB::table('assignments')
                ->where('unit_id', $unitId)
                ->whereNull('deleted_at')
                ->orderBy('order')
                ->orderBy('id')
                ->get(['id', 'order']);

            foreach ($assignments as $assignment) {
                $content->push([
                    'type' => 'assignment',
                    'id' => $assignment->id,
                    'original_order' => $assignment->order,
                ]);
            }

            $quizzes = DB::table('quizzes')
                ->where('unit_id', $unitId)
                ->whereNull('deleted_at')
                ->orderBy('order')
                ->orderBy('id')
                ->get(['id', 'order']);

            foreach ($quizzes as $quiz) {
                $content->push([
                    'type' => 'quiz',
                    'id' => $quiz->id,
                    'original_order' => $quiz->order,
                ]);
            }

            $typePriority = ['lesson' => 1, 'assignment' => 2, 'quiz' => 3];
            $sorted = $content->sortBy([
                ['original_order', 'asc'],
                fn ($a, $b) => ($typePriority[$a['type']] ?? 99) <=> ($typePriority[$b['type']] ?? 99),
                ['id', 'asc'],
            ])->values();

            $now = now();

            foreach ($sorted as $index => $item) {
                $newOrder = $index + 1;

                DB::table('unit_contents')->insert([
                    'unit_id' => $unitId,
                    'contentable_type' => $item['type'],
                    'contentable_id' => $item['id'],
                    'order' => $newOrder,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $table = match ($item['type']) {
                    'lesson' => 'lessons',
                    'assignment' => 'assignments',
                    'quiz' => 'quizzes',
                };

                DB::table($table)
                    ->where('id', $item['id'])
                    ->update(['order' => $newOrder]);
            }
        }
    }

    public function down(): void
    {
        DB::table('unit_contents')->truncate();
    }
};
