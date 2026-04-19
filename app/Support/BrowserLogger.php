<?php

namespace App\Support;

use hisorange\BrowserDetect\Facade as Browser;
use Illuminate\Support\Facades\Request;

class BrowserLogger
{
    
    public static function getDeviceInfo(): array
    {
        try {
            $userAgent = self::getUserAgent();
            $ipAddress = self::getClientIp();

            
            if (empty($userAgent)) {
                return [
                    'ip_address' => $ipAddress,
                    'browser' => 'CLI',
                    'browser_version' => null,
                    'platform' => PHP_OS,
                    'device' => 'Server',
                    'device_type' => 'desktop',
                ];
            }

            
            $result = Browser::parse($userAgent);

            
            $location = \Stevebauman\Location\Facades\Location::get($ipAddress);

            return [
                'ip_address' => $ipAddress,
                'browser' => $result->browserName() ?: 'Unknown',
                'browser_version' => $result->browserVersion() ?: null,
                'platform' => $result->platformName() ?: 'Unknown',
                'device' => $result->deviceModel() ?: ($result->platformName() ?: 'Unknown'),
                'device_type' => self::getDeviceType($result),
                'city' => $location ? $location->cityName : null,
                'region' => $location ? $location->regionName : null,
                'country' => $location ? $location->countryName : null,
            ];
        } catch (\Exception $e) {
            
            return [
                'ip_address' => self::getClientIp(),
                'browser' => 'Unknown',
                'browser_version' => null,
                'platform' => 'Unknown',
                'device' => 'Unknown',
                'device_type' => 'desktop',
                'city' => null,
                'region' => null,
                'country' => null,
            ];
        }
    }

    
    private static function getUserAgent(): string
    {
        $request = Request::instance();

        
        $userAgent = $request->header('User-Agent');
        if (! empty($userAgent)) {
            return $userAgent;
        }

        
        $alternativeHeaders = [
            'X-Original-User-Agent',
            'X-Device-User-Agent',
            'X-Operamini-Phone-Ua',
            'Device-Stock-Ua',
        ];

        foreach ($alternativeHeaders as $header) {
            $value = $request->header($header);
            if (! empty($value)) {
                return $value;
            }
        }

        
        $serverVars = ['HTTP_USER_AGENT', 'HTTP_X_ORIGINAL_USER_AGENT'];
        foreach ($serverVars as $var) {
            $value = $request->server($var);
            if (! empty($value)) {
                return $value;
            }
        }

        return '';
    }

    
    private static function getClientIp(): string
    {
        $request = Request::instance();

        
        $forwardedHeaders = [
            'X-Forwarded-For',
            'X-Real-IP',
            'CF-Connecting-IP', 
            'True-Client-IP', 
            'X-Client-IP',
        ];

        foreach ($forwardedHeaders as $header) {
            $value = $request->header($header);
            if (! empty($value)) {
                
                $ips = array_map('trim', explode(',', $value));
                $clientIp = $ips[0];
                if (filter_var($clientIp, FILTER_VALIDATE_IP)) {
                    return $clientIp;
                }
            }
        }

        return $request->ip() ?? '127.0.0.1';
    }

    
    private static function getDeviceType($result): string
    {
        if ($result->isMobile()) {
            return 'mobile';
        }

        if ($result->isTablet()) {
            return 'tablet';
        }

        if ($result->isDesktop()) {
            return 'desktop';
        }

        return 'unknown';
    }
}
