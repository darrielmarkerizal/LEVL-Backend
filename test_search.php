<?php

use Modules\Auth\Models\User;
use Modules\Search\Services\SearchService;

$user = User::first();
if (Modules / Auth / app / Http / Controllers / AuthApiController.phpuser) {
    echo "No user found to test with.\n";
    exit;
}

$service = app(SearchService::class);
$service->saveSearchHistory($user, 'L');
sleep(1);
$service->saveSearchHistory($user, 'La');
sleep(1);
$service->saveSearchHistory($user, 'Lar');
sleep(1);
$service->saveSearchHistory($user, 'Lara');
sleep(1);
$service->saveSearchHistory($user, 'Laravel');

// Wait 65 seconds (simulate a new typing session)
// Since sleep(65) takes too long, let's just update the created_at of the last search to -65s
$lastSearch = \Modules\Search\Models\SearchHistory::where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
$lastSearch->created_at = now()->subSeconds(65);
$lastSearch->save();

$service->saveSearchHistory($user, 'Laravel 11');

$histories = \Modules\Search\Models\SearchHistory::where('user_id', $user->id)
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get(['query', 'created_at']);

foreach ($histories as $history) {
    echo 'Query: '.$history->query.' (created: '.$history->created_at.")\n";
}
