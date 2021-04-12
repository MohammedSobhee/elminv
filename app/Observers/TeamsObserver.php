<?php

namespace App\Observers;

use App\Messages;
use App\Teams;
use App\TeamMembers;
use App\Projects;
// use App\ProjectMembers;
// use DB;

class TeamsObserver
{
    /**
     * Handle the teams "created" event.
     *
     * @param  \App\Teams  $teams
     * @return void
     */
    public function created(Teams $teams)
    {
        //
    }

    /**
     * Handle the teams "updated" event.
     *
     * @param  \App\Teams  $teams
     * @return void
     */
    public function updated(Teams $teams)
    {
        //
    }

    /**
     * Handle the teams "deleted" event.
     *
     * @param  \App\Teams  $teams
     * @return void
     */
    public function deleted(Teams $teams) {
        TeamMembers::where('team_id', $teams->id)->delete();
        // Remove associated messages
        Messages::where('recipient_id', $teams->id)->where('type', 2)->delete();
        // Remove associated projects
        Projects::where('team_id', $teams->id)->each(function ($p) {
            $p->delete(); // Delete individually for ProjectsObserver
        });
    }

    /**
     * Handle the teams "restored" event.
     *
     * @param  \App\Teams  $teams
     * @return void
     */
    public function restored(Teams $teams) {
        TeamMembers::onlyTrashed()->where('team_id', $teams->id)->restore();
        Messages::onlyTrashed()->where('recipient_id', $teams->id)->where('type', 2)->restore();
        Projects::onlyTrashed()->where('team_id', $teams->id)->each(function ($p) {
            $p->restore();
        });
    }

    /**
     * Handle the teams "force deleted" event.
     *
     * @param  \App\Teams  $teams
     * @return void
     */
    public function forceDeleted(Teams $teams)
    {
        //
    }
}
