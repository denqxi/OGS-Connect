<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ProgressiveLockout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to login requests
        if (!$request->is('login') || !$request->isMethod('POST')) {
            return $next($request);
        }

        $loginId = $request->input('login_id');
        $throttleKey = $this->getThrottleKey($request, $loginId);
        
        // Check if progressive lockout is enabled
        if (!config('security.login.progressive_lockout', true)) {
            return $next($request);
        }

        // Get lockout count for this user
        $lockoutCount = Cache::get("lockout_count_{$throttleKey}", 0);
        
        // If user has been locked out multiple times, suggest password reset
        $resetAfterLockouts = config('security.login.reset_after_lockouts', 3);
        
        if ($lockoutCount >= $resetAfterLockouts) {
            // Store suggestion in session for display
            $request->session()->flash('suggest_password_reset', true);
            $request->session()->flash('lockout_count', $lockoutCount);
        }

        return $next($request);
    }

    /**
     * Get throttle key for the request
     */
    private function getThrottleKey(Request $request, string $loginId): string
    {
        return 'progressive_lockout_' . md5(strtolower($loginId) . '|' . $request->ip());
    }

    /**
     * Increment lockout count when user gets locked out
     */
    public static function incrementLockoutCount(Request $request, string $loginId): void
    {
        $throttleKey = 'progressive_lockout_' . md5(strtolower($loginId) . '|' . $request->ip());
        $currentCount = Cache::get("lockout_count_{$throttleKey}", 0);
        Cache::put("lockout_count_{$throttleKey}", $currentCount + 1, now()->addDays(1));
    }

    /**
     * Clear lockout count on successful login
     */
    public static function clearLockoutCount(Request $request, string $loginId): void
    {
        $throttleKey = 'progressive_lockout_' . md5(strtolower($loginId) . '|' . $request->ip());
        Cache::forget("lockout_count_{$throttleKey}");
    }
}
