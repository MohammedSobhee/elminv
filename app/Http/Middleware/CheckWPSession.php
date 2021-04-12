<?php

namespace App\Http\Middleware;

use Closure;
use App\UserSessionData;

class CheckWPSession {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        // Redirect to logout if cookie doesn't exist (or expired)
        if (!isset($_COOKIE['tgui'])) {
            return self::logout($request);
        }

        // Get user session data for wordpress and assign blade variables
        else if ($user = UserSessionData::get()) {

            // If user_data doesn't exist, force logout (usually because of artisan command:deleteusersessiondata or forcelogout)
            if(!$user->user_data || !$user->hash) {
                return self::logout($request, 'There have been system changes which require you to login again. Thank you for your patience.');
            }

            \View::composer('*', function ($view) use ($user) {
                // Sub page list of last visited page in /course
                \View::share('parent_page', json_decode($user->parent_page) ?? null);
                \View::share('page_list', json_decode($user->page_list) ?? null);
                \View::share('page_last_visited', $user->page_last_visited ?? null);
                // Share all user_data
                \View::share('usersess', $user->user_data);
                // Announcement
                \View::share('announcement', auth()->user()->role->slug !== 'student' ? $user->announcement : 0);
            });

        } else {
            return self::logout($request);
        }
        return $next($request);
    }



    /**
     * logout
     *
     * @param  mixed $request
     * @return Response/Redirect
     */
    private static function logout($request, $logoutMessage = '') {
        if ($request->ajax()) {
            return response()->json(['error' => 'logout'], 503);
        } else {
            \Session::flush();
            \Auth::logout();
            return redirect('login')->with('primary', $logoutMessage);
        }
    }
}
