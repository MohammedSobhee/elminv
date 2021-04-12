<?php

namespace App\Observers;

use App\Classes;

use App\AssignmentClasses;
use App\AssignmentDue;
use App\Teams;
use App\ClassMembers;
use App\Messages;

use Mail;
use App\Mail\ClassEmail;
use App\UserSessionData;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;

use App\Traits\CoursewareTypesTrait;

class ClassesObserver {
    use CoursewareTypesTrait;
    /**
     * Handle the classes "created" event.
     *
     * @param  \App\Classes  $cls
     * @return void
     */
    public function created(Classes $cls) {

        // Create teacher's class member for class
        $cm = new ClassMembers();
        $cm->class_id = $cls->id;
        $cm->user_id = auth()->user()->id;
        $cm->role_id = 3;


        // Generate register codes and email them
        if ($cm->save()) {
            $u = auth()->user();

            // Update Session Data
            $class_types = $u->classes->pluck('class_type')->unique()->values()->toArray();
            UserSessionData::put($u->id, 'class_ids', $u->getClassIDs());
            UserSessionData::put($u->id, 'class_types', $class_types);
            //UserSessionData::put($u->id, 'courseware_types', $this->getCoursewareTypes($class_types));

            // Generate student and assistant activation codes
            $cls->generate($u->school_id);

            // Send email
            $validator = new EmailValidator();
            if ($validator->isValid($u->email, new RFCValidation())) {
                Mail::to($u->email)
                    ->bcc(\Config::get('mail.admin_notification'))
                    ->send(new ClassEmail($cls->student_code, $cls->assistant_teacher_code, $cls->class_name));
            }
        }
    }

    /**
     * Handle the classes "updated" event.
     *
     * @param  \App\Classes  $cls
     * @return void
     */
    public function updated(Classes $cls) {
        //
    }

    /**
     * Handle the classes "deleted" event.
     *
     * @param  \App\Classes  $cls
     * @return void
     */
    public function deleted(Classes $cls) {
        // Remove this class from any custom assignments
        AssignmentClasses::where('class_id', $cls->id)->delete();
        // Delete due dates of class
        AssignmentDue::where('class_id', $cls->id)->delete();
        // Delete all members from class_members
        ClassMembers::where('class_id', $cls->id)->delete();
        // Delete associated teams and their team members
        Teams::where('class_id', $cls->id)->each(function($t) {
            $t->delete(); // Delete individually for TeamsObserver
        });
        // Remove associated messages
        Messages::where(['recipient_id' => $cls->id, 'type' => 1])->delete();

        // Update session data
        $u = auth()->user();
        if($u) {
            $class_types = $u->classes->pluck('class_type')->unique()->values()->toArray();
            UserSessionData::put($u->id, 'class_ids', $u->getClassIDs());
            UserSessionData::put($u->id, 'class_types', $class_types);
            //UserSessionData::put($u->id, 'courseware_types', $this->getCoursewareTypes($class_types));
        }
    }

    /**
     * Handle the classes "restored" event.
     *
     * @param  \App\Classes  $cls
     * @return void
     */
    public function restored(Classes $cls) {
        AssignmentClasses::onlyTrashed()->where('class_id', $cls->id)->restore();
        AssignmentDue::onlyTrashed()->where('class_id', $cls->id)->restore();
        ClassMembers::onlyTrashed()->where('class_id', $cls->id)->restore();
        Teams::onlyTrashed()->where('class_id', $cls->id)->each(function ($t) {
            $t->restore();
        });
        Messages::onlyTrashed()->where(['recipient_id' => $cls->id, 'type' => 1])->restore();
    }

    /**
     * Handle the classes "force deleted" event.
     *
     * @param  \App\Classes  $cls
     * @return void
     */
    public function forceDeleted(Classes $cls) {
        //
    }
}
