<?php

namespace App\Http\Middleware;

use Closure;

class Activation {
    /**
     * Handle activation redirect
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        return redirect('/activate/process/' . $request->activation_code . '/' . $request->type);
    }
}
