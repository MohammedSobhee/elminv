<?php
namespace App\Schedule;

use App\ActivationAccounts;
use App\User;
use App\Classes;
use Carbon\Carbon;

class DeleteDemos {
    private $deleteCount = 0;

    public function __invoke() {
        $time = Carbon::now()->subDays(7)->toDateTimeString();
        $school = config('app.demo.school');
        $admin = config('app.demo.admin');

        // Run each individually for observers
        ActivationAccounts::where('school_id', $school)
            ->where('created_at', '<=', $time)
            ->delete();

        User::where('school_id', $school) // Demo School
            ->where('created_at', '<=', $time)
            ->where('id', '<>', $admin) // Don't delete main Demo school admin
            ->each(function ($u) {
                $u->delete();
                $this->deleteCount++;
            });

        Classes::where('school_id', $school)
            ->where('created_at', '<=', $time)->each(function ($c) {
                $c->delete();
            });

        \Log::debug('Deleted ' . $this->deleteCount . ' demo accounts.');

    }
}
