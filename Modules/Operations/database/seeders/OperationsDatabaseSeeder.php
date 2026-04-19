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
                    'issued_at' => $row->completed_at ?? now(),
                    'expired_at' => null,
                    'status' => CertificateStatus::Active,
                ]
            );
        }

        
        

        $this->command->info('Operations seeding done.');
    }
}
