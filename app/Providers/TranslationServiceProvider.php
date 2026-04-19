<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\Translator;

class TranslationServiceProvider extends ServiceProvider
{
    
    public function register(): void
    {
        
    }

    
    public function boot(): void
    {
        
        $this->app->extend('translator', function (Translator $translator) {
            
            $originalGet = \Closure::bind(function ($key, array $replace = [], $locale = null, $fallback = true) {
                return $this->get($key, $replace, $locale, $fallback);
            }, $translator, Translator::class);

            
            $translator->macro('getWithLogging', function ($key, array $replace = [], $locale = null) use ($translator) {
                $locale = $locale ?: $translator->locale();
                $fallbackLocale = $translator->getFallback();

                
                $line = $translator->get($key, $replace, $locale, false);

                
                if ($line === $key) {
                    
                    $fallbackLine = $translator->get($key, $replace, $fallbackLocale, false);

                    if ($fallbackLine === $key) {
                        
                        Log::warning('Missing translation key', [
                            'key' => $key,
                            'locale' => $locale,
                            'fallback_locale' => $fallbackLocale,
                            'request_id' => request()->id ?? null,
                        ]);
                    } else {
                        
                        Log::info('Translation fallback used', [
                            'key' => $key,
                            'requested_locale' => $locale,
                            'fallback_locale' => $fallbackLocale,
                            'request_id' => request()->id ?? null,
                        ]);
                    }
                }

                return $line;
            });

            return $translator;
        });

        
        $this->app->booted(function () {
            $translator = app('translator');
            $supportedLocales = config('app.supported_locales', ['en', 'id']);

            foreach ($supportedLocales as $locale) {
                $langPath = lang_path($locale);

                if (! is_dir($langPath)) {
                    Log::error('Missing translation directory', [
                        'locale' => $locale,
                        'path' => $langPath,
                    ]);
                }
            }
        });
    }
}
