<?php

declare(strict_types=1);

namespace Modules\Operations\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Operations\Enums\CertificateStatus;
use Modules\Operations\Models\Certificate;

class OperationsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding certificates...');

        $completed = DB::table('enrollments')
            ->join('users', 'enrollments.user_id', '=', 'users.id')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->where('enrollments.status', 'completed')
            ->whereNull('users.deleted_at')
            ->orderBy('enrollments.id')
            ->limit(60)
            ->get([
                'enrollments.user_id',
                'enrollments.course_id',
                'enrollments.completed_at',
            ]);

        $lastSequence = (int) DB::table('certificates')->max('id');

        foreach ($completed as $row) {
            if (Certificate::query()
                ->where('user_id', $row->user_id)
                ->where('course_id', $row->course_id)
                ->exists()
            ) {
                continue;
            }

            $issuedAt = $row->completed_at
                ? Carbon::parse($row->completed_at)
                : now();

            $lastSequence++;
            $certificateNumber = sprintf(
                'LEVL/%s/%s',
                $issuedAt->format('Y'),
                str_pad((string) $lastSequence, 6, '0', STR_PAD_LEFT)
            );

            Certificate::query()->create([
                'user_id' => $row->user_id,
                'course_id' => $row->course_id,
                'certificate_number' => $certificateNumber,
                'issued_at' => $issuedAt,
                'expired_at' => $issuedAt->copy()->addYears(3),
                'status' => CertificateStatus::Active,
            ]);
        }

        $this->command->info('Operations seeding done.');
    }
}
