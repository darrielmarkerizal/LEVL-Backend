<?php

declare(strict_types=1);

namespace Modules\Operations\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Operations\Enums\CertificateStatus;
use Modules\Operations\Models\Certificate;

class OperationsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding certificates and sample reports...');

        $completed = DB::table('enrollments')
            ->where('status', 'completed')
            ->orderBy('id')
            ->limit(60)
            ->get(['user_id', 'course_id', 'completed_at']);

        foreach ($completed as $row) {
            Certificate::query()->firstOrCreate(
                [
                    'user_id' => $row->user_id,
                    'course_id' => $row->course_id,
                ],
                [
                    'certificate_number' => 'UAT-'.$row->user_id.'-'.$row->course_id,
                    'file_path' => null,
                    'issued_at' => $row->completed_at ?? now(),
                    'expired_at' => null,
                    'status' => CertificateStatus::Active,
                ]
            );
        }

        $adminId = User::query()->role('Admin')->orderBy('id')->value('id')
            ?? User::query()->role('Superadmin')->orderBy('id')->value('id');

        if ($adminId !== null) {
            $now = now()->toDateTimeString();
            DB::table('reports')->insert([
                'type' => 'activity',
                'generated_by' => $adminId,
                'filters' => json_encode(['range' => 'last_30_days']),
                'file_path' => null,
                'notes' => 'UAT sample activity export',
                'generated_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('reports')->insert([
                'type' => 'grading',
                'generated_by' => $adminId,
                'filters' => json_encode(['course_id' => 'all']),
                'file_path' => null,
                'notes' => 'UAT grading summary',
                'generated_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info('Operations seeding done.');
    }
}
