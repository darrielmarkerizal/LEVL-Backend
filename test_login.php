<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$response = Illuminate\Support\Facades\Http::post("http://localhost:8000/api/v1/auth/login", [
    'login' => 'admin@levl.test',
    'password' => 'Password123!',
]);

echo "Status: " . $response->status() . "\n";
echo "Body: " . $response->body() . "\n";

