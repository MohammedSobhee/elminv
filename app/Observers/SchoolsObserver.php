<?php

namespace App\Observers;

use App\ActivationAccounts;
use App\Classes;
use App\SchoolContactInfo;
use App\schools;
use App\SchoolSettings;
use App\User;

class SchoolsObserver
{
    /**
     * Handle the schools "created" event.
     *
     * @param  \App\schools  $schools
     * @return void
     */
    public function created(schools $schools)
    {
        //
    }

    /**
     * Handle the schools "updated" event.
     *
     * @param  \App\schools  $schools
     * @return void
     */
    public function updated(schools $schools)
    {
        //
    }

    /**
     * Handle the schools "deleted" event.
     *
     * @param  \App\schools  $schools
     * @return void
     */
    public function deleted(schools $s) {
        $admin = config('app.admin_school');
        $demo = config('app.demo.school');

        ActivationAccounts::where('school_id', $s->id)->delete();

        SchoolSettings::where('school_id', $s->id)->delete();
        SchoolContactInfo::where('school_id', $s->id)->delete();

        // Delete individually for observers
        User::where('school_id', $s->id)
            ->where('id', '<>', $admin)
            ->where('id', '<>', $demo)
            ->each(function ($u) {
                $u->delete();
        });
        Classes::where('school_id', $s->id)
            ->where('school_id', '<>', $admin)
            ->where('school_id', '<>', $demo)
            ->each(function ($c) {
                $c->delete();
            });
    }

    /**
     * Handle the schools "restored" event.
     *
     * @param  \App\schools  $schools
     * @return void
     */
    public function restored(schools $s) {
        ActivationAccounts::onlyTrashed()->where('school_id', $s->id)->restore();
        SchoolSettings::onlyTrashed()->where('school_id', $s->id)->restore();
        SchoolContactInfo::onlyTrashed()->where('school_id', $s->id)->restore();
        User::onlyTrashed()->where('school_id', $s->id)->each(function ($u) {
            $u->restore();
        });
        Classes::onlyTrashed()->where('school_id', $s->id)->each(function ($c) {
            $c->restore();
        });
    }

    /**
     * Handle the schools "force deleted" event.
     *
     * @param  \App\schools  $schools
     * @return void
     */
    public function forceDeleted(schools $schools)
    {
        //
    }
}
