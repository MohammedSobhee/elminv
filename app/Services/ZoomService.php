<?php

namespace App\Services;
use App\ZoomAuthToken;
use Illuminate\Support\Facades\Http;
use App\VideoCon;
use Carbon\Carbon;

class ZoomService {

    private $createMeetingUrl = 'https://api.zoom.us/v2/users/me/meetings';
    private $meetingUrl = 'https://api.zoom.us/v2/meetings/';
    private $refreshTokenUrl = 'https://zoom.us/oauth/token?grant_type=refresh_token&refresh_token=';
    private $complianceUrl = 'https://api.zoom.us/oauth/data/compliance';
    private $clientId;
    private $basicAuthToken;

    public function __construct() {
        $this->clientId = config('services.zoom')['client_id'];
        $clientSecret = config('services.zoom')['client_secret'];
        $this->basicAuthToken = 'Basic ' . base64_encode($this->clientId . ':' . $clientSecret);
    }

    /**
     * Get Saved Zoom Authenticcation
     *
     * @return Object
     */
    public static function getAuth() {
        return ZoomAuthToken::where('user_id', auth()->user()->id)->first();
    }

    /**
     * deAuth
     *
     * @param  mixed $request
     * @return object|null
     */
    public function deAuth($payload) {
        $response = null;
        $auth = ZoomAuthToken::where('account_id', $payload['account_id']);
        $auth_users = $auth->pluck('user_id')->toArray();
        if ($payload['user_data_retention'] == 'false') {
            $videocon = VideoCon::whereIn('user_id', $auth_users);
            if ($videocon) {
                $videocon->delete();
            }
        }

        $settings = \App\CourseSettings::whereIn('teacher_id', $auth_users)->where('name', 'videocon_zoom');
        if ($settings) {
            $settings->update(['value' => '0']);
        }

        if ($auth->delete()) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => $this->basicAuthToken
                ])->post($this->complianceUrl, [
                    'client_id' => $this->clientId,
                    'account_id' => $payload['account_id'],
                    'user_id' => $payload['user_id'],
                    'deauthorization_event_received' => $payload,
                    'compliance_completed' => true
                ])->throw();
                \Log::debug('deauth compliance ' . $response);
            } catch (\Exception $e) {
                \Log::debug('deauth error ' . $e);
            }
        }
        return $response;
    }

    /**
     * Refresh Token
     *
     * @return Object
     */
    private function refreshToken() {
        $auth = self::getAuth();
        if ((new Carbon($auth->expires_at))->greaterThan(Carbon::now())) {
            \Log::debug('existing refresh token used ' . $auth);
            return $auth;
        }
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->basicAuthToken
            ])->post($this->refreshTokenUrl . $auth->refresh_token)->throw();
            if ($response->successful()) {
                $response = $response->json();
                $auth->access_token = $response['access_token'];
                $auth->refresh_token = $response['refresh_token'];
                $auth->expires_at = Carbon::now()->addSeconds($response['expires_in'])->toDateTimeString();
                $auth->update();
                \Log::debug('refresh token requested ' . $auth);
                return $auth;
            }
        } catch (\Exception $e) {
            $auth->delete();
            \Log::debug('refresh error refresh token' . $e);
        }
    }

    /**
     * Create Meeting
     *
     * @param  string $label
     * @param  string $password
     * @return array|null
     */
    public function createMeeting($label, $password) {
        $auth = $this->refreshToken();
        if ($auth) {
            try {
                $response = Http::withToken($auth->access_token)->post($this->createMeetingUrl, [
                    'topic' => $label,
                    'type' => 3, // reoccuring - no fixed time
                    'password' => $password
                ])->throw();
                if ($response->status() == 201) {
                    return $response->json();
                } else {
                    \Log::debug('create meeting status ' . $response->status());
                }
            } catch (\Exception $e) {
                \Log::debug('create error access token ' . $e);
            }
        }
    }

    /**
     * Delete Meeting
     *
     * @param  string $id
     * @return mixed|null
     */
    public function deleteMeeting($id) {
        $auth = $this->refreshToken();
        try {
            $response = Http::withToken($auth->access_token)->delete($this->meetingUrl . $id)->throw();
            if ($response->status() == 204) {
                return true;
            } else {
                \Log::debug('delete meeting status ' . $response->status());
            }
        } catch (\Exception $e) {
            \Log::debug('delete error access token ' . $e);
        }
    }

    /**
     * Update Meeting
     *
     * @param  string $label
     * @param  string $password
     * @return array|null
     */
    public function updateMeeting($id, $label, $password) {
        $auth = $this->refreshToken();
        try {
            $response = Http::withToken($auth->access_token)->patch($this->meetingUrl . $id, [
                'topic' => $label,
                'password' => $password
            ])->throw();
            if ($response->status() == 204) {
                return true;
            } else {
                \Log::debug('update meeting status ' . $response->status());
            }
        } catch (\Exception $e) {
            \Log::debug('update error access token ' . $e);
        }
    }
}
