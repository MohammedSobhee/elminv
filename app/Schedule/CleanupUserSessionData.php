<?php
namespace App\Schedule;

use App\UserSessionData;
use Carbon\Carbon;

class CleanupUserSessionData {


    public function __invoke() {
        $time = Carbon::now()->subMonth(2)->toDateTimeString();
        $deleted = UserSessionData::where('updated_at', '<=', $time)->delete();
        \Log::debug('Deleted ' . $deleted . ' old user data sessions.');
    }
}
