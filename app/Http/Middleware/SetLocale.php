<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        // Ensure fallback logic if invalid
        if (!in_array($locale, ['id', 'en'])) {
            $locale = 'id';
        }

        App::setLocale($locale);

        $response = $next($request);

        // To ensure persistence across requests for guests who haven't explicitly set it yet
        // We set a cookie if they don't have one, or if they just switched it via session.
        if (!$request->hasCookie('locale') || $request->cookie('locale') !== $locale) {
            // Only set cookie if it's a GET request returning HTML to avoid breaking API/AJAX
            if ($request->isMethod('GET') && ! $request->expectsJson()) {
                 $cookie = cookie()->forever('locale', $locale, null, null, true, false, false, 'lax');
                 if (method_exists($response, 'withCookie')) {
                     $response->withCookie($cookie);
                 }
            }
        }

        return $response;
    }

    /**
     * Resolve the locale with explicit priorities.
     */
    private function resolveLocale(Request $request): string
    {
        // 1. Explicit Session (When user switches language)
        if ($request->session()->has('locale')) {
            return $request->session()->get('locale');
        }

        // 2. Authenticated User Preference
        if ($request->user() && $request->user()->locale) {
            return $request->user()->locale;
        }

        // 3. Persistent Cookie
        if ($request->hasCookie('locale')) {
            return $request->cookie('locale');
        }

        // 4. IP Geolocation (CF or ip-api)
        return $this->detectLocaleByIp($request);
    }

    /**
     * Detect locale based on IP Geolocation.
     */
    private function detectLocaleByIp(Request $request): string
    {
        // Check Cloudflare header first (instant, free, 100% reliable if using CF)
        $cfCountry = $request->header('HTTP_CF_IPCOUNTRY') ?? $request->header('CF-IPCountry');
        if ($cfCountry) {
            return strtoupper($cfCountry) === 'ID' ? 'id' : 'en';
        }

        $ip = $request->ip();
        
        // Skip for local environments or private IPs
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'id'; // Default fallback
        }

        // Cache the IP lookup for 7 days
        $countryCode = Cache::remember("geoip_country_{$ip}", now()->addDays(7), function () use ($ip) {
            try {
                $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}?fields=countryCode");
                if ($response->successful() && isset($response['countryCode'])) {
                    return $response['countryCode'];
                }
            } catch (\Exception $e) {
                Log::warning("GeoIP detection failed for IP: {$ip} - " . $e->getMessage());
            }
            return 'ID'; // Fallback to ID if API fails
        });

        return strtoupper($countryCode) === 'ID' ? 'id' : 'en';
    }
}
