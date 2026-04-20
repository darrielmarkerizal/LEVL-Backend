<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SuperAdminSeeder::class,
            ActiveUsersSeeder::class,
            MasterDataSeeder::class,
            \Modules\Common\Database\Seeders\CommonDatabaseSeeder::class,
            \Modules\Auth\Database\Seeders\AuthComprehensiveDataSeeder::class,
            \Modules\Auth\Database\Seeders\InstructorSpecializationSeeder::class,
            \Modules\Schemes\Database\Seeders\SchemesDatabaseSeeder::class,
            \Modules\Enrollments\Database\Seeders\EnrollmentsDatabaseSeeder::class,
            \Modules\Learning\Database\Seeders\LearningDatabaseSeeder::class,
            \Modules\Grading\Database\Seeders\GradingDatabaseSeeder::class,
            \Modules\Gamification\Database\Seeders\GamificationDatabaseSeeder::class,
            \Modules\Forums\Database\Seeders\ForumsDatabaseSeeder::class,
            \Modules\Content\Database\Seeders\ContentDatabaseSeeder::class,
            \Modules\Notifications\Database\Seeders\NotificationsDatabaseSeeder::class,
            \Modules\Search\Database\Seeders\SearchDatabaseSeeder::class,
            \Modules\Operations\Database\Seeders\OperationsDatabaseSeeder::class,
            \Modules\Common\Database\Seeders\ActivityLogSeeder::class,
            \Modules\Common\Database\Seeders\AuditLogSeeder::class,
            \Modules\Dashboard\Database\Seeders\DashboardDatabaseSeeder::class,
            \Modules\Mail\Database\Seeders\MailDatabaseSeeder::class,
        ]);

        $this->call([
            \Modules\Auth\Database\Seeders\UATPersonaSeeder::class,
            UATGamificationPipelineSeeder::class,
            \Modules\Enrollments\Database\Seeders\EnrollmentActivityTimelineSeeder::class,
        ]);
    }
}
