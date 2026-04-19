<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

class TranslationService
{
    
    public function trans(string $key, array $params = [], ?string $locale = null): string
    {
        $currentLocale = App::getLocale();

        if ($locale && $locale !== $currentLocale) {
            
            App::setLocale($locale);
            $translation = __($key, $params);
            App::setLocale($currentLocale);

            return $translation;
        }

        return __($key, $params);
    }

    
    public function transChoice(string $key, int $count, array $params = [], ?string $locale = null): string
    {
        $currentLocale = App::getLocale();

        if ($locale && $locale !== $currentLocale) {
            
            App::setLocale($locale);
            $translation = trans_choice($key, $count, $params);
            App::setLocale($currentLocale);

            return $translation;
        }

        return trans_choice($key, $count, $params);
    }

    
    public function hasTranslation(string $key, ?string $locale = null): bool
    {
        $locale = $locale ?? App::getLocale();
        $currentLocale = App::getLocale();

        if ($locale !== $currentLocale) {
            App::setLocale($locale);
        }

        $translation = trans($key);
        $exists = $translation !== $key;

        if ($locale !== $currentLocale) {
            App::setLocale($currentLocale);
        }

        return $exists;
    }

    
    public function getCurrentLocale(): string
    {
        return App::getLocale();
    }

    
    public function getSupportedLocales(bool $scanFilesystem = true): array
    {
        
        $cacheKey = 'supported_locales_'.($scanFilesystem ? 'with_fs' : 'config_only');

        return cache()->remember($cacheKey, 3600, function () use ($scanFilesystem) {
            $configLocales = config('app.supported_locales', ['en', 'id']);

            if (! $scanFilesystem) {
                return $configLocales;
            }

            
            $langPath = lang_path();
            $fileSystemLocales = [];

            if (File::isDirectory($langPath)) {
                $directories = File::directories($langPath);

                foreach ($directories as $directory) {
                    $locale = basename($directory);
                    
                    if ($this->hasTranslationFiles($locale)) {
                        $fileSystemLocales[] = $locale;
                    }
                }
            }

            
            $allLocales = array_unique(array_merge($configLocales, $fileSystemLocales));

            return array_values($allLocales);
        });
    }

    
    protected function hasTranslationFiles(string $locale): bool
    {
        $localePath = lang_path($locale);

        if (! File::isDirectory($localePath)) {
            return false;
        }

        
        $files = File::files($localePath);

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                return true;
            }
        }

        return false;
    }

    
    public function getFallbackLocale(): string
    {
        return config('app.fallback_locale', 'id');
    }

    
    public function isLocaleSupported(string $locale): bool
    {
        return in_array($locale, $this->getSupportedLocales());
    }

    
    public function setLocale(string $locale): bool
    {
        if ($this->isLocaleSupported($locale)) {
            App::setLocale($locale);

            return true;
        }

        return false;
    }
}
