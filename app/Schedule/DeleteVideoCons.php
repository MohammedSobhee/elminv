<?php
namespace App\Schedule;

use App\VideoCon;
use Carbon\Carbon;

class DeleteVideoCons {
    public function __invoke() {
        $deleted = VideoCon::where('created_at', '<=', Carbon::now()->subDays(300)->toDateTimeString())->delete();
    }
}
