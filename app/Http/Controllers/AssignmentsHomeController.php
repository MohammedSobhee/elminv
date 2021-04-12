<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

// Service
use App\Services\StudentAssignmentsService;

class AssignmentsHomeController extends Controller {

    /**
     * Show Assignments Home for students
     *
     * @return resource Student assignments index
     */
    public function show($id = 0, $catid = 0) {
        $a = StudentAssignmentsService::getAll();
        return view('assignments.home', [
            'project_list' => $a->projects,
            'custom_list' => $a->customs,
            'assignment_id' => $id,
            'assignment_category_id' => $catid,
            'team_id' => auth()->user()->getTeamID()
        ]);
    }
}
