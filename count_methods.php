<?php

$modulesPath = __DIR__.'/Modules';
$modules = array_filter(glob($modulesPath . '/*'), 'is_dir');

echo "Module          | Controllers | Endpoints (Methods)\n";
echo str_repeat("-", 50) . "\n";

$totalControllers = 0;
$totalEndpoints = 0;

foreach ($modules as $modulePath) {
    $moduleName = basename($modulePath);
    
    $controllersPath = $modulePath . '/app/Http/Controllers';
    $cCount = 0;
    $eCount = 0;
    
    if (is_dir($controllersPath)) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllersPath));
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php' && strpos($file->getFilename(), 'Controller') !== false) {
                $cCount++;
                
                $content = file_get_contents($file->getPathname());
                preg_match_all('/public\s+function\s+([a-zA-Z0-9_]+)\s*\(/', $content, $matches);
                if (isset($matches[1])) {
                    foreach ($matches[1] as $method) {
                        if (!in_array($method, ['__construct', 'create', 'edit'])) {
                            $eCount++;
                        }
                    }
                }
            }
        }
    }
    
    echo str_pad($moduleName, 15) . " | " . str_pad($cCount, 11) . " | " . $eCount . "\n";
    $totalControllers += $cCount;
    $totalEndpoints += $eCount;
}

echo str_repeat("-", 50) . "\n";
echo str_pad("TOTAL", 15) . " | " . str_pad($totalControllers, 11) . " | " . $totalEndpoints . "\n";

