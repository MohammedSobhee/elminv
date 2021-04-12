<?php
namespace App\Schedule;

use jeremykenedy\LaravelLogger\App\Models\Activity;
use Carbon\Carbon;

class DeleteActivityLogs {
    public function __invoke() {
        $deleted = Activity::where('created_at', '<=', Carbon::now()->subDays(3)->toDateTimeString())->forceDelete();
        \Log::debug('Deleted ' . $deleted . ' activity logs');
    }
}
