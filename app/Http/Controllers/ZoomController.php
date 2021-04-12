<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Socialite;
use App\Services\ZoomService;
use Carbon\Carbon;

class ZoomController extends Controller {

    private $driver = 'zoom';

    /**
     * Socialite redirectToProvider
     *
     * @return void
     */
    public function redirectToProvider() {
        return Socialite::driver($this->driver)->redirect();
    }

    /**
     * Socialite handleProviderCallback
     *
     * @return void
     */
    public function handleProviderCallback() {
        try {
            $user = Socialite::driver($this->driver)->stateless()->user();
            //$user = Socialite::driver($this->driver)->user();
        } catch (\Exception $e) {
            \Log::info('exception: ' . $e);
            $user = Socialite::driver($this->driver)->stateless()->user();
            if (!$user) {
                return redirect('dashboard')->with('error', 'Something went wrong. Please contact <a href="/support">support</a>.');
            }
        }

        if (static::saveAuth($user)) {
            return redirect('edit/settings')->with('success', 'You have successfully set up Zoom.');
        }
    }

    /**
     * saveAuth
     *
     * @param  mixed $user
     * @return void
     */
    private static function saveAuth($user) {
        $auth = auth()->user()->zoomtoken()->updateOrCreate(
            ['account_id' => $user->account_id],
            [
                'token_id' => $user->id,
                'account_id' => $user->account_id,
                'access_token' => $user->token,
                'refresh_token' => $user->refreshToken,
                'expires_at' => Carbon::now()->addSeconds($user->expiresIn)->toDateTimeString(),
                'personal_url' => $user->personal_meeting_url,
                'avatar' => $user->avatar,
                'email' => $user->email,
                'name' => $user->name
            ]
        );
        $auth->touch(); // Update timestamp
        return $auth;
    }

    /**
     * Delete Zoom Authorization
     *
     * @return Redirect
     */
    public function deleteAuth() {
        $auth = ZoomService::getAuth();
        $settings = \App\CourseSettings::where([
            'teacher_id' => auth()->user()->id,
            'name' => 'videocon_zoom'
        ])->first();
        $settings->value = '0';
        if ($auth->delete() && $settings->update()) {
            return redirect()->back()->with('success', 'Successfully removed Zoom authorization from your account. You may also want to <a href="https://marketplace.zoom.us/user/installed" target="_blank">uninstall our app</a> from within your Zoom account.');
        } else {
            return redirect()->back()->with('error', 'Failed to remove Zoom authorization. Please contact <a href="/support">support.</a>');
        }
    }

    /**
     * returnAuth
     *
     * @return Response
     */
    public function returnAuth() {
        $auth = ZoomService::getAuth();
        if ($auth) {
            return response()->json([
                'success' => 'Retrieved Zoom auth',
                'auth' => $auth->token_id
            ], 200);
        }
    }

    /**
     * deAuth
     *
     * @return void
     */
    // public function deAuth(Request $request) {
    //     //\Log::debug($request);
    //     if ($request->header('authorization') === 'XqqvzwpeTRS7vF0OTYLqgw') { // Zoom verification token
    //         $payload = json_decode($request->getContent(), true);
    //         //\Log::debug($payload);
    //         if ($payload) {
    //             $zoom = new ZoomService;
    //             $completed = $zoom->deAuth($payload['payload']);
    //             if ($completed) {
    //                 return response()->json(['success' => 'Successfully removed Zoom authorization'], 200);
    //             } else {
    //                 return response()->json(['success' => 'Nothing to deauthorize.'], 200);
    //             }
    //         } else {
    //             return response()->json(['error' => 'Invalid response'], 503);
    //         }
    //     }
    // }
}
