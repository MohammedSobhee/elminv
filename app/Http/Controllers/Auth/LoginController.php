<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Socialite;
use App\User;

class LoginController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect after authenticated
     *
     * @return void
     */

    public function authenticated() {
        if (auth()->user()->hasAdminPermission()) {
            return redirect('/eduadmin');
        } else {
            return redirect()->intended();
        }
    }

    /**
     * Login Oauth, get username first
     *
     * @return void
     */
    public function loginUsername($driver) {
        return view('auth.login_username', [
            'driver' => $driver
        ]);
    }

    public function processLogin($driver, $username) {
        session()->put('login_username', $username);
        return Socialite::driver($driver)->redirect();
    }

    /**
     * Redirect the user to the provider authentication page.
     *
     * @return \Illuminate\Http\Response
     */

    public function redirectToProvider($driver) {
        return Socialite::driver($driver)->redirect();
    }

    public function handleProviderCallback($driver) {
        try {
            $user = Socialite::driver($driver)->user();
        } catch (\Exception $e) {
            \Log::info('Oauth exception: ' . $e);
            $user = Socialite::driver($driver)->stateless()->user();
            if (!$user) {
                return redirect()->route('login');
            }
        }

        switch ($driver) {
            case 'google':
                $username = $user->getEmail();
                break;
            case 'apple':$user->email;
                break;
            case 'clever':
                $username = !$user->email ? session()->get('login_username') : $user->email;
                break;
        }

        $existingUser = User::where('email', $username)->first();

        if ($existingUser) {
            if (!$existingUser->provider_id) {
                $existingUser->provider_name = $driver;
                switch ($driver) {
                    case 'google':
                        $existingUser->provider_id = $user->getId();
                        $existingUser->avatar = $user->getAvatar();
                        break;
                    case 'apple':
                    case 'clever':
                        $existingUser->provider_id = $user->id;
                        break;
                }
                $existingUser->save();
            }
            auth()->login($existingUser);

        } else {
            \Log::info('SSO User failed to login with ' . $driver, ['email' => $user->email]);

            switch ($driver) {
                case 'google': return redirect()->route('login')->with('error', 'This account doesn\'t exist in Inventionland Institute. Did you mean to use <a href="https://accounts.google.com/AddSession?hl=en&continue=https://www.google.com/">different Google account?</a>  Come back to this login page once you\'ve logged in with a different account.');
                case 'clever': return redirect()->route('login')->with('error', 'This account doesn\'t exist in Inventionland Institute. Did you mean to use different Clever account?</a>');
                default: return redirect()->route('login')->with('error', "You don't have an account with Inventionland Institute.");
            }
        }
        return redirect($this->redirectPath());
    }
}
