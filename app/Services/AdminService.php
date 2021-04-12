<?php

namespace App\Services;

use App\Schools;
use Carbon\Carbon;

class AdminService {

    /**
     * Get expiring
     *
     * @param  int $task
     * @return object
     */
    public static function expiring($task = 0) {
        $current_date = Carbon::now();
        $sixmos = ($current_date)->modify('+182 day')->format('Y-m-d');

        $schools = Schools::join('school_settings as ss', 'ss.school_id', 'schools.id')
            ->whereNotNull('ss.contract_expiration_date')
            ->where('ss.contract_expiration_date', '<=', $sixmos)
            ->select('schools.id', 'schools.name', 'ss.contract_expiration_date')
            ->orderBy('ss.contract_expiration_date', 'ASC');

        if ($task) {
            $schools = $schools->where('ss.notified_admin', '!=', 1);
        }

        $schools = $schools->get();

        $schools->each(function ($item) {
            $item->user_count = $item->users()->count();
            $item->contract_expiration_date = Carbon::parse($item->contract_expiration_date)->format('F j, Y');
        });
        return $schools;

    }

    /**
     * Payment Due
     *
     * @return object
     */
    public static function paymentDue() {
        return Schools::whereHas('settings', fn($q) => $q->where('payment_due', 1))->get();
    }

    /**
     * Get school list helper
     *
     * @return object
     */
    public static function getSchoolList() {
        return Schools::with('settings:school_id,payment_due')
            ->orderBy('status', 'desc')
            ->orderBy('name');
    }

}
