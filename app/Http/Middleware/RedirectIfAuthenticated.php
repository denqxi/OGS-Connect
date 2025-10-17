<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        // Check all authentication guards
        if (Auth::guard('supervisor')->check()) {
            return redirect('/dashboard');
        }
        
        if (Auth::guard('tutor')->check()) {
            return redirect('/tutor_portal');
        }
        
        if (Auth::guard('web')->check()) {
            return redirect('/dashboard');
        }

        return $next($request);
    }
}