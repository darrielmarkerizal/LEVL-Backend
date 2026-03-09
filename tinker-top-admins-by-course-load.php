// Optional filters
$limit = 10;
$minCourses = 1;

$admins = \Modules\Auth\Models\User::query()
    ->role('Admin')
    ->select(['id', 'name', 'email'])
    ->orderBy('id')
    ->get();

if ($admins->isEmpty()) {
    echo "No admin users found.\n";
    return;
}

$rows = [];

foreach ($admins as $admin) {
    $courseIds = \Modules\Schemes\Models\CourseAdmin::query()
        ->where('user_id', $admin->id)
        ->pluck('course_id')
        ->unique()
        ->values();

    $managedCoursesCount = $courseIds->count();

    if ($managedCoursesCount < $minCourses) {
        continue;
    }

    // Instructor unik dari course yang dikelola admin
    $uniqueInstructorsCount = \Modules\Schemes\Models\Course::query()
        ->whereIn('id', $courseIds)
        ->whereNotNull('instructor_id')
        ->distinct('instructor_id')
        ->count('instructor_id');

    // Participant unik dengan enrollment active/completed di course yang dikelola admin
    $uniqueParticipantsCount = \Modules\Enrollments\Models\Enrollment::query()
        ->whereIn('course_id', $courseIds)
        ->whereIn('status', [
            \Modules\Enrollments\Enums\EnrollmentStatus::Active->value,
            \Modules\Enrollments\Enums\EnrollmentStatus::Completed->value,
        ])
        ->distinct('user_id')
        ->count('user_id');

    // Total enrollment active/completed (bukan unique user)
    $activeCompletedEnrollmentsCount = \Modules\Enrollments\Models\Enrollment::query()
        ->whereIn('course_id', $courseIds)
        ->whereIn('status', [
            \Modules\Enrollments\Enums\EnrollmentStatus::Active->value,
            \Modules\Enrollments\Enums\EnrollmentStatus::Completed->value,
        ])
        ->count();

    $rows[] = [
        'admin_id' => $admin->id,
        'admin_name' => $admin->name,
        'admin_email' => $admin->email,
        'managed_courses' => $managedCoursesCount,
        'unique_participants_active_completed' => $uniqueParticipantsCount,
        'active_completed_enrollments' => $activeCompletedEnrollmentsCount,
        'unique_instructors' => $uniqueInstructorsCount,
        // Score sederhana untuk ranking gabungan
        'score' => ($managedCoursesCount * 3) + ($uniqueParticipantsCount * 2) + $uniqueInstructorsCount,
    ];
}

if (empty($rows)) {
    echo "No admin matches the minimum course criteria.\n";
    return;
}

usort($rows, static function (array $a, array $b): int {
    return [$b['score'], $b['managed_courses'], $b['unique_participants_active_completed'], $b['unique_instructors']]
        <=>
        [$a['score'], $a['managed_courses'], $a['unique_participants_active_completed'], $a['unique_instructors']];
});

$rows = array_slice($rows, 0, $limit);

echo "=== TOP ADMINS BY COURSE/PARTICIPANT/INSTRUCTOR LOAD ===\n";
echo "Limit: {$limit}, Min Courses: {$minCourses}\n\n";

foreach ($rows as $index => $row) {
    $rank = $index + 1;

    echo "#{$rank} {$row['admin_name']} (ID: {$row['admin_id']})\n";
    echo "  Email: {$row['admin_email']}\n";
    echo "  Managed Courses: {$row['managed_courses']}\n";
    echo "  Unique Participants (active/completed): {$row['unique_participants_active_completed']}\n";
    echo "  Active/Completed Enrollments: {$row['active_completed_enrollments']}\n";
    echo "  Unique Instructors: {$row['unique_instructors']}\n";
    echo "  Score: {$row['score']}\n";
    echo str_repeat('-', 60) . "\n";
}

echo "\nDone.\n";
