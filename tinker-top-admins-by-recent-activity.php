// Optional filters
$limit = 10;    // Top N admins
$days = 30;     // Lookback period in days

$admins = \Modules\Auth\Models\User::query()
    ->role('Admin')
    ->select(['id', 'name', 'email'])
    ->orderBy('id')
    ->get();

if ($admins->isEmpty()) {
    echo "No admin users found.\n";
    return;
}

$adminIds = $admins->pluck('id');

$activityRows = \Spatie\Activitylog\Models\Activity::query()
    ->selectRaw('causer_id, COUNT(*) as total_activities, MAX(created_at) as latest_activity_at')
    ->where('causer_type', \Modules\Auth\Models\User::class)
    ->whereIn('causer_id', $adminIds)
    ->where('created_at', '>=', now()->subDays($days))
    ->groupBy('causer_id')
    ->orderByDesc('total_activities')
    ->orderByDesc('latest_activity_at')
    ->limit($limit)
    ->get();

if ($activityRows->isEmpty()) {
    echo "No admin activities found in the last {$days} days.\n";
    return;
}

echo "=== TOP ADMINS BY RECENT ACTIVITY ===\n";
echo "Lookback: {$days} days | Limit: {$limit}\n\n";

foreach ($activityRows as $index => $row) {
    $admin = $admins->firstWhere('id', (int) $row->causer_id);
    $rank = $index + 1;

    echo "#{$rank} ".($admin?->name ?? 'Unknown')." (ID: {$row->causer_id})\n";
    echo "  Email: ".($admin?->email ?? '-') ."\n";
    echo "  Recent Activities: {$row->total_activities}\n";
    echo "  Latest Activity At: {$row->latest_activity_at}\n";
    echo str_repeat('-', 60)."\n";
}

$top = $activityRows->first();
$topAdmin = $admins->firstWhere('id', (int) $top->causer_id);

echo "\nMost active admin in last {$days} days:\n";
echo "- Name: ".($topAdmin?->name ?? 'Unknown')."\n";
echo "- ID: {$top->causer_id}\n";
echo "- Activity count: {$top->total_activities}\n";

echo "\nDone.\n";
