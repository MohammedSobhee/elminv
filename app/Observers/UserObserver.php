<?php

namespace App\Observers;

use App\Assignment;
use App\User;
use App\Projects;
use App\AssignmentSubmitted;
use App\Chat;
use App\CourseSettings;
use App\Grades;
use App\Messages;
use App\RubricCategories;
use App\RubricSettings;
use App\UserPermissions;
use App\UserRoles;
use App\UserSessionData;
use App\VideoCon;
use App\WorksheetRubric;

class UserObserver {
    /**
     * Handle the user "created" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function created(User $user) {
        //
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function updated(User $user) {
        //
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function deleted(User $user) {

        // Delete teacher settings
        CourseSettings::where('teacher_id', $user->id)->delete();
        WorksheetRubric::where('teacher_id', $user->id)->delete();
        RubricSettings::where('teacher_id', $user->id)->each(function ($rs) {
            RubricCategories::find($rs->category_id)->delete();
            $rs->delete();
        });

        // Delete assignments added by teacher
        Assignment::where('user_id', $user->id)->delete();

        // Delete user's role and user's permissions
        UserRoles::where('user_id', $user->id)->delete();
        UserPermissions::where('user_id', $user->id)->delete();

        // Delete personal projects only. Team projects will be deleted via class or team delete.
        Projects::findByUser($user->id)->whereNull('team_id')->each(function ($p) {
            $p->delete(); // Each for ProjectsObserver
        });


        // Delete submitted custom assignments (project submissions are handled above via observer)
        AssignmentSubmitted::where(['user_id' => $user->id, 'type' => 2])->each(function ($a) {
            $a->delete(); // Each for observer
        });

        // Delete grades
        Grades::where('user_id', $user->id)->whereNull('project_id')->each(function ($g) { // Leave project grades to ProjectObserver
            $g->delete(); // Delete individually for GradesObserver
        });

        // Delete associated personal and sent messages
        Messages::where(['recipient_id' => $user->id, 'type' => 3])
            ->orWhere('sender_id', $user->id)
            ->delete();

        // Remove wp session data
        UserSessionData::where('user_id', $user->id)->delete();

        // Delete sent chats and created video conferences
        Chat::where('user_id', $user->id)->delete();
        VideoCon::where('user_id', $user->id)->delete();
    }

    /**
     * Handle the user "restored" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function restored(User $user) {
        // Delete teacher settings
        CourseSettings::onlyTrashed()->where('teacher_id', $user->id)->restore();
        WorksheetRubric::onlyTrashed()->where('teacher_id', $user->id)->restore();
        RubricSettings::onlyTrashed()->where('teacher_id', $user->id)->each(function ($rs) {
            RubricCategories::onlyTrashed()->find($rs->category_id)->restore();
            $rs->restore();
        });


        Assignment::onlyTrashed()->where('user_id', $user->id)->restore();

        UserRoles::onlyTrashed()->where('user_id', $user->id)->restore();
        UserPermissions::onlyTrashed()->where('user_id', $user->id)->restore();

        Projects::findByUser($user->id)->onlyTrashed()->whereNull('team_id')->each(function ($p) {
            $p->restore(); // Each for ProjectsObserver
        });

        AssignmentSubmitted::onlyTrashed()->where(['user_id' => $user->id, 'type' => 2])->each(function ($a) {
            $a->restore(); // Each for observer
        });

        Grades::onlyTrashed()->where('user_id', $user->id)->whereNull('project_id')->each(function ($g) {
            $g->restore(); // Delete individually for GradesObserver
        });

        Messages::onlyTrashed()->where(['recipient_id' => $user->id, 'type' => 3])
            ->orWhere('sender_id', $user->id)
            ->restore();

        Chat::onlyTrashed()->where('user_id', $user->id)->restore();
        VideoCon::onlyTrashed()->where('user_id', $user->id)->restore();
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function forceDeleted(User $user) {
        //
    }
}
