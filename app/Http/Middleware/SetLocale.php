<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->detectLocale($request);

        App::setLocale($locale);

        Log::info('Locale set for request', [
            'locale' => $locale,
            'request_id' => $request->id ?? uniqid(),
            'path' => $request->path(),
        ]);

        return $next($request);
    }

    
    private function detectLocale(Request $request): string
    {
        
        if ($request->has('lang')) {
            $locale = $request->query('lang');
            if ($this->isSupported($locale)) {
                return $locale;
            }
        }

        
        $acceptLanguage = $request->header('Accept-Language');
        if ($acceptLanguage) {
            $locale = $this->parseAcceptLanguageHeader($acceptLanguage);
            if ($locale && $this->isSupported($locale)) {
                return $locale;
            }
        }

        
        return config('app.locale', 'id');
    }

    
    private function parseAcceptLanguageHeader(string $header): ?string
    {
        
        $locales = [];

        
        $parts = explode(',', $header);

        foreach ($parts as $part) {
            $part = trim($part);

            
            if (preg_match('/^([a-z]{2}(?:-[A-Z]{2})?)(?:;q=([0-9.]+))?$/i', $part, $matches)) {
                $locale = strtolower($matches[1]);
                $quality = isset($matches[2]) ? (float) $matches[2] : 1.0;

                
                if (str_contains($locale, '-')) {
                    $locale = explode('-', $locale)[0];
                }

                $locales[] = [
                    'locale' => $locale,
                    'quality' => $quality,
                ];
            }
        }

        
        usort($locales, function ($a, $b) {
            return $b['quality'] <=> $a['quality'];
        });

        
        foreach ($locales as $item) {
            if ($this->isSupported($item['locale'])) {
                return $item['locale'];
            }
        }

        return null;
    }

    
    private function isSupported(string $locale): bool
    {
        $supportedLocales = config('app.supported_locales', ['en', 'id']);

        return in_array($locale, $supportedLocales, true);
    }
}
