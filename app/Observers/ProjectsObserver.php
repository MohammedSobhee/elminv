<?php

namespace App\Observers;

use App\Projects;
use App\ProjectMembers;
use App\WorksheetAnswers;
use App\WorksheetRepeats;
use App\AssignmentSubmitted;
use App\Grades;

class ProjectsObserver
{
    /**
     * Handle the projects "created" event.
     *
     * @param  \App\Projects  $projects
     * @return void
     */
    public function created(Projects $projects)
    {
        //
    }

    /**
     * Handle the projects "updated" event.
     *
     * @param  \App\Projects  $projects
     * @return void
     */
    public function updated(Projects $projects)
    {
        //
    }

    /**
     * Handle the projects "deleted" event.
     *
     * @param  \App\Projects  $projects
     * @return void
     */
    public function deleted(Projects $projects) {
        ProjectMembers::where('project_id', $projects->id)->delete();
        WorksheetAnswers::where('project_id', $projects->id)->delete();
        WorksheetRepeats::where('project_id', $projects->id)->delete();
        AssignmentSubmitted::where(['project_id' => $projects->id, 'type' => 1])->each(function ($as) {
            $as->delete(); // Each for observers
        });
        Grades::where('project_id', $projects->id)->each(function ($g) {
            $g->delete(); // Each for observers
        });
    }

    /**
     * Handle the projects "restored" event.
     *
     * @param  \App\Projects  $projects
     * @return void
     */
    public function restored(Projects $projects) {
        ProjectMembers::onlyTrashed()->where('project_id', $projects->id)->restore();
        WorksheetAnswers::onlyTrashed()->where('project_id', $projects->id)->restore();
        WorksheetRepeats::onlyTrashed()->where('project_id', $projects->id)->restore();
        AssignmentSubmitted::onlyTrashed()->where(['project_id' => $projects->id, 'type' => 1])->each(function ($as) {
            $as->restore(); // Each for observers
        });
        Grades::onlyTrashed()->where('project_id', $projects->id)->each(function ($g) {
            $g->restore(); // Each for observers
        });
    }

    /**
     * Handle the projects "force deleted" event.
     *
     * @param  \App\Projects  $projects
     * @return void
     */
    public function forceDeleted(Projects $projects)
    {
        //
    }
}
