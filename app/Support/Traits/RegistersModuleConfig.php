<?php

namespace App\Support\Traits;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

trait RegistersModuleConfig
{
    protected function registerModuleConfig(): void
    {
        $configPath = module_path($this->name, config('modules.paths.generator.config.path'));

        if (! is_dir($configPath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($configPath)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $config = str_replace($configPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
            $configKey = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
            $segments = explode('.', $this->nameLower.'.'.$configKey);

            // Remove duplicated adjacent segments
            $normalized = $this->removeDuplicateSegments($segments);

            $key = ($config === 'config.php') ? $this->nameLower : implode('.', $normalized);

            $this->publishes([$file->getPathname() => config_path($config)], 'config');
            $this->mergeConfigFrom($file->getPathname(), $key);
        }
    }

    /**
     * Merge config from the given path recursively.
     *
     * @param  string  $path  Path to the config file
     * @param  string  $key  Config key to merge into
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        $existing = config($key, []);
        $moduleConfig = require $path;

        config([$key => array_replace_recursive($existing, $moduleConfig)]);
    }

    private function removeDuplicateSegments(array $segments): array
    {
        $normalized = [];
        
        foreach ($segments as $segment) {
            if (end($normalized) !== $segment) {
                $normalized[] = $segment;
            }
        }

        return $normalized;
    }
}
