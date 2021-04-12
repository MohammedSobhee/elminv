<?php
namespace App\Schedule;

use App\Assignment;
use App\AssignmentSubmitted;
use App\User;
use App\Projects;
use App\ProjectMembers;
use App\Classes;
use App\ClassMembers;
use App\Grades;
use App\Messages;
use App\Teams;
use App\TeamMembers;
use App\WorksheetAnswers;
use App\WorksheetRepeats;
use Carbon\Carbon;

class CleanupSoftDeletes {
    private function ctime() {
        return Carbon::now()->subMonth(6)->toDateTimeString();
    }

    public function __invoke() {
        $deleted_us = User::where('deleted_at', '<=', $this->ctime())->forceDelete();
        $deleted_ps = Projects::where('deleted_at', '<=', $this->ctime())->forceDelete();
        $deleted_pm = ProjectMembers::where('deleted_at', '<=', $this->ctime())->forceDelete();
        $deleted_at = Assignment::where('deleted_at', '<=', $this->ctime())->forceDelete();
        $deleted_as = AssignmentSubmitted::where('deleted_at', '<=', $this->ctime())->forceDelete();
        $deleted_cs = Classes::where('deleted_at', '<=', $this->ctime())->forceDelete();
        $deleted_cm = ClassMembers::where('deleted_at', '<=', $this->ctime())->forceDelete();
        $deleted_ts = Teams::where('deleted_at', '<=', $this->ctime())->forceDelete();
        $deleted_tm = TeamMembers::where('deleted_at', '<=', $this->ctime())->forceDelete();
        $deleted_wa = WorksheetAnswers::where('deleted_at', '<=', $this->ctime())->forceDelete();
        $deleted_wa = WorksheetRepeats::where('deleted_at', '<=', $this->ctime())->forceDelete();
        $deleted_gs = Grades::where('deleted_at', '<=',$this->ctime())->forceDelete();
        $deleted_ms = Messages::where('deleted_at', '<=', $this->ctime())->forceDelete();

        \Log::debug('Maintenance: Force deleted ' . $deleted_us . ' users');
        \Log::debug('Maintenance: Force deleted ' . $deleted_ps . ' projects');
        \Log::debug('Maintenance: Force deleted ' . $deleted_pm . ' project members');
        \Log::debug('Maintenance: Force deleted ' . $deleted_at . ' assignments');
        \Log::debug('Maintenance: Force deleted ' . $deleted_as . ' submitted assignments');
        \Log::debug('Maintenance: Force deleted ' . $deleted_cs . ' classes');
        \Log::debug('Maintenance: Force deleted ' . $deleted_cm . ' class members');
        \Log::debug('Maintenance: Force deleted ' . $deleted_ts . ' teams');
        \Log::debug('Maintenance: Force deleted ' . $deleted_tm . ' team members');
        \Log::debug('Maintenance: Force deleted ' . $deleted_wa . ' worksheet answers');
        \Log::debug('Maintenance: Force deleted ' . $deleted_gs . ' grades');
        \Log::debug('Maintenance: Force deleted ' . $deleted_ms . ' messages');
    }
}
