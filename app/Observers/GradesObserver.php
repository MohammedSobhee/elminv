<?php

namespace App\Observers;

use App\Grades;
use App\Messages;

class GradesObserver {
    /**
     * Handle the grades "created" event.
     *
     * @param  \App\Grades  $grades
     * @return void
     */
    public function created(Grades $grades) {
        //
    }

    /**
     * Handle the grades "updated" event.
     *
     * @param  \App\Grades  $grades
     * @return void
     */
    public function updated(Grades $grades) {
        //
    }

    /**
     * Handle the grades "deleted" event.
     *
     * @param  \App\Grades  $grades
     * @return void
     */
    public function deleted(Grades $grades) {
        Messages::where('id', $grades->message_id)->delete();
    }

    /**
     * Handle the grades "restored" event.
     *
     * @param  \App\Grades  $grades
     * @return void
     */
    public function restored(Grades $grades) {
        Messages::onlyTrashed()->where('id', $grades->message_id)->restore();
    }

    /**
     * Handle the grades "force deleted" event.
     *
     * @param  \App\Grades  $grades
     * @return void
     */
    public function forceDeleted(Grades $grades) {
        //
    }
}
