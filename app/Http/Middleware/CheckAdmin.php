<?php

namespace App\Http\Middleware;

use Closure;

class CheckAdmin {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if (\Auth::check()) {
            if (auth()->user()->hasAdminPermission()) {
                return $next($request);
            } else {
                return redirect('/dashboard')->with('error', 'Permission denied.');
            }
        }
    }
}
