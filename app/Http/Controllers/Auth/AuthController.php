<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller {

    /**
     * Show View
     *
     * @param  Request $request
     * @return resource - View
     */
    public function show(Request $request) {
        if (auth()->check()) {
            return redirect('dashboard');
        } else {
            return view('auth.login', [
                'uri' => $request->path(),
                'username' => $request->username ?? ''
            ]);
        }
    }

    /**
     * Log out
     *
     * @return Redirect
     */
    public function logout() {
        setcookie('tgui', '', time() - 3600);
        \Session::flush();
        \Auth::logout();
        return redirect()->route('login');
    }

    public function wpUserdataLogout() {
        \Session::flush();
        \Auth::logout();
        return redirect()->route('login');//->with('primary', 'There have been system changes which require you to login again. Thank you for your patience.');
    }
}
