<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IdleTimeout
{
    /**
     * Force logout after configured inactivity window.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timeoutSeconds = max(1, (int) config('session.lifetime', 10)) * 60;

        if (Auth::guard('web')->check() || Auth::guard('supervisor')->check()) {
            $lastActivity = $request->session()->get('last_activity_timestamp');

            if ($lastActivity && (now()->timestamp - $lastActivity) >= $timeoutSeconds) {
                // Clear all authenticated guards and session on timeout
                collect(['web', 'supervisor'])->each(function (string $guard): void {
                    if (Auth::guard($guard)->check()) {
                        Auth::guard($guard)->logout();
                    }
                });

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('landing')->with('message', 'Session expired due to inactivity.');
            }

            $request->session()->put('last_activity_timestamp', now()->timestamp);
        } else {
            $request->session()->forget('last_activity_timestamp');
        }

        return $next($request);
    }
}
