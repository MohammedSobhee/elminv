<?php
namespace App\Schedule;

use Mail;
use App\Mail\AdminEmail;

use App\SchoolSettings;
use App\Services\AdminService;

class SendSchoolExpiringNotice {
    public function __invoke() {
        $expiring = AdminService::expiring(1);
        $payment_due = AdminService::paymentDue();

        if ($expiring->count()) {
            Mail::to('field.nathan@inventionlandinstitute.com')
                ->cc([config('mail.admin'), 'carlino.clay@inventionland.com'])
                ->send(new AdminEmail($expiring, $payment_due, \Config::get('app.url')));

            foreach ($expiring as $item) {
                $school = SchoolSettings::where('school_id', $item->id)->first();
                $school->notified_admin = 1;
                $school->update();
            }
        }
    }
}
