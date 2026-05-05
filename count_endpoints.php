<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = \Illuminate\Support\Facades\Route::getRoutes();

$moduleEndpoints = [];
$moduleControllers = [];

$modulesPath = __DIR__.'/Modules';
$modules = array_filter(glob($modulesPath . '/*'), 'is_dir');

foreach ($modules as $modulePath) {
    $moduleName = basename($modulePath);
    $moduleEndpoints[$moduleName] = 0;
    
    $controllersPath = $modulePath . '/app/Http/Controllers';
    $count = 0;
    if (is_dir($controllersPath)) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllersPath));
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $count++;
            }
        }
    }
    $moduleControllers[$moduleName] = $count;
}

foreach ($routes as $route) {
    $action = $route->getActionName();
    if (strpos($action, 'Modules\\') === 0) {
        $parts = explode('\\', $action);
        $moduleName = $parts[1]; 
        
        if (strpos($route->uri(), 'api') === 0) { 
             if (isset($moduleEndpoints[$moduleName])) {
                 $moduleEndpoints[$moduleName]++;
             } else {
                 $moduleEndpoints[$moduleName] = 1;
             }
        }
    }
}

$globalControllersPath = __DIR__.'/app/Http/Controllers';
$globalControllersCount = 0;
if (is_dir($globalControllersPath)) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($globalControllersPath));
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $globalControllersCount++;
        }
    }
}

echo "Module          | Controllers | Endpoints\n";
echo str_repeat("-", 40) . "\n";
foreach ($moduleControllers as $module => $cCount) {
    $eCount = $moduleEndpoints[$module] ?? 0;
    echo str_pad($module, 15) . " | " . str_pad($cCount, 11) . " | " . $eCount . "\n";
}
echo str_repeat("-", 40) . "\n";
$totalControllers = array_sum($moduleControllers);
$totalEndpoints = array_sum($moduleEndpoints);
echo str_pad("TOTAL", 15) . " | " . str_pad($totalControllers, 11) . " | " . $totalEndpoints . "\n";
echo "Global controllers: $globalControllersCount\n";

