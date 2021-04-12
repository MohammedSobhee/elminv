<?php
namespace App\Schedule;

use App\Chat;
use Carbon\Carbon;

class DeleteChats {
    public function __invoke() {
        $deleted = Chat::where('created_at', '<=', Carbon::now()->subDays(300)->toDateTimeString())->delete();
    }
}
