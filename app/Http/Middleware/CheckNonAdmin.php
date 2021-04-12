<?php

namespace App\Http\Middleware;

use Closure;

class CheckNonAdmin {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if (\Auth::check()) {
            if (!auth()->user()->hasAdminPermission()) {
                return $next($request);
            } else {
                return redirect('/eduadmin')->with('error', 'Login as a non-admin user to access this page.');
            }
        }
    }
}
