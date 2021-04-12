<?php

namespace App\Observers;

use App\AssignmentSubmitted;
use App\Messages;

class AssignmentSubmittedObserver
{
    /**
     * Handle the assignment submitted "created" event.
     *
     * @param  \App\AssignmentSubmitted  $assignmentSubmitted
     * @return void
     */
    public function created(AssignmentSubmitted $assignmentSubmitted)
    {
        //
    }

    /**
     * Handle the assignment submitted "updated" event.
     *
     * @param  \App\AssignmentSubmitted  $assignmentSubmitted
     * @return void
     */
    public function updated(AssignmentSubmitted $assignmentSubmitted)
    {
        //
    }

    /**
     * Handle the assignment submitted "deleted" event.
     *
     * @param  \App\AssignmentSubmitted  $assignmentSubmitted
     * @return void
     */
    public function deleted(AssignmentSubmitted $as) {
        Messages::where('id', $as->message_id)->delete();
    }

    /**
     * Handle the assignment submitted "restored" event.
     *
     * @param  \App\AssignmentSubmitted  $assignmentSubmitted
     * @return void
     */
    public function restored(AssignmentSubmitted $as) {
        Messages::onlyTrashed()->where('id', $as->message_id)->restore();
    }

    /**
     * Handle the assignment submitted "force deleted" event.
     *
     * @param  \App\AssignmentSubmitted  $assignmentSubmitted
     * @return void
     */
    public function forceDeleted(AssignmentSubmitted $assignmentSubmitted)
    {
        //
    }
}
