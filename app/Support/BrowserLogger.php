<?php

namespace App\Support;

use Illuminate\Support\Facades\Request;
use hisorange\BrowserDetect\Parser as Browser;

class BrowserLogger
{
  /**
   * Get browser and device information from request
   */
  public static function getDeviceInfo(): array
  {
    try {
      $browser = app(Browser::class);

      return [
        "ip_address" => Request::ip(),
        "browser" => $browser->browserName(),
        "browser_version" => $browser->browserVersion(),
        "platform" => $browser->platformName(),
        "device" => $browser->deviceModel() ?? $browser->platformName(),
        "device_type" => self::getDeviceType($browser),
      ];
    } catch (\Exception $e) {
      // Fallback if browser-detect fails
      return [
        "ip_address" => Request::ip(),
        "browser" => "Unknown",
        "browser_version" => null,
        "platform" => "Unknown",
        "device" => "Unknown",
        "device_type" => "desktop",
      ];
    }
  }

  /**
   * Determine device type
   */
  private static function getDeviceType(Browser $browser): string
  {
    if ($browser->isMobile()) {
      return "mobile";
    }

    if ($browser->isTablet()) {
      return "tablet";
    }

    if ($browser->isDesktop()) {
      return "desktop";
    }

    return "unknown";
  }
}
