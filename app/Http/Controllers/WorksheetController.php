<?php

namespace App\Http\Controllers;
use App;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

// Validation
use App\Http\Requests\CreateProjectPost;

// Models
use App\Worksheet;
use App\WorksheetAnswers;
use App\WorksheetRepeats;
use App\Projects;
use App\ProjectMembers;
use App\TeamMembers;

// Services
use App\Services\StudentAssignmentsService;
use App\Services\WorksheetService;

class WorksheetController extends Controller {

    /**
     * Show worksheet in blade
     *
     * @param  Request $request
     * @param  int $id
     * @param  int $project_id
     * @return resource - Worksheet view
     */
    public function show(Request $request, $id, $project_id) {

        // Check if team
        $team_id = strpos($request->path(), 'team') !== false ? auth()->user()->getTeamID() : 0;

        // Check if any projects are locked
        $has_locked = StudentAssignmentsService::selectProjects()->where('locked', 1)->exists();

        $p = Projects::find($project_id);

        $ws = Worksheet::select(
            'worksheet.id',
            'worksheet.title',
            'worksheet.example',
            'worksheet.order',
            'worksheet_rubric.category_value',
            'worksheet_rubric.active',
            'grades.points as grade',
            'assignment_submitted.status',
            DB::raw('count(worksheet_answers.worksheet_id) as has_answers'), 'assignment_submitted.id as assignment_submitted_id', 'm.content'
        )
            ->join('worksheet_rubric', 'worksheet_rubric.category_id', '=', 'worksheet.id')
            ->where('worksheet.id', '=', $id)
            ->leftjoin('grades', function ($join) use ($project_id) {
                $join->on('grades.type_id', '=', 'worksheet_rubric.id')
                    ->where('grades.type', '=', 1)
                    ->where('grades.project_id', $project_id);
            })
            ->leftJoin('worksheet_answers', function ($join) use ($project_id) {
                $join->on('worksheet_answers.worksheet_id', '=', 'worksheet_rubric.category_id')
                    ->where('worksheet_answers.project_id', '=', $project_id);
            })
            ->leftJoin('assignment_submitted', function ($join) use ($project_id) {
                $join->on('assignment_submitted.type_id', '=', 'worksheet_rubric.category_id')
                    ->where('assignment_submitted.project_id', '=', $project_id);
            })
            ->leftJoin('messages as m', function ($join) {
                $join->on('assignment_submitted.message_id', '=', 'm.id');
            })
            ->where('worksheet_rubric.teacher_id', auth()->user()->getTeacherID())
            ->groupBy('worksheet.id')
            ->orderBy('worksheet.order', 'asc')
            ->first();

        return view('assignments.worksheet', [
            'title' => $ws->title,
            'grade' => $ws->grade ?? 0,
            'active' => $ws->active,
            'points' => $ws->category_value ?? 0,
            'team_id' => $team_id,
            'status' => $ws->status ?? 0,
            'asid' => $ws->assignment_submitted_id ?? 0,
            'message' => $ws->content ?? '',
            'project_name' => $p->project_name,
            'wid' => $ws->id,
            'pid' => $project_id,
            'has_locked' => $has_locked,
            'locked' => $p->locked,
            'demo_error' => (auth()->user()->demo && !in_array($ws->id, config('app.demo.worksheets'))),
            'worksheet' => WorksheetService::get($id, $project_id)
        ]);
    }

    /**
     * Get worksheet via axios for use in Gradebook
     *
     * @param  int $id
     * @param  int $project_id
     * @return array
     */
    public function get($id, $project_id) {
        return WorksheetService::get($id, $project_id);
    }

    /**
     * Find worksheet (for use with WP course page activity links)
     *
     * @param  Request $request
     * @param  int $id
     * @return resource - View for iframe in WP
     */
    public function find(Request $request, $id) {
        $p = auth()->user()->projects()->latest('project.updated_at')->get();
        if (strpos($request->path(), 'pcount') !== false) {
            $data = [
                'project_count' => count($p),
                'projects' => $p,
                'worksheet_id' => $id
            ];
            return $data;
        } else {
            return view('assignments.project_select', [
                'worksheet_id' => $id,
                'projects' => $p
            ]);
        }
    }

    /**
     * Store worksheet answers
     *
     * @param  Request $request
     * @param  int $id
     * @param  int $project_id
     * @return Response
     */
    public function store(Request $request, $id, $project_id) {

        Projects::where('id', $project_id)->update(['last_updated_by' => $request->user_id]);
        // Store image
        if ($request->hasFile('answer') && $request->file('answer')->isValid()) {
            $image = $request->file('answer');
            $name = time() . \Str::random(5) . '.' . $image->getClientOriginalExtension();

            // Remove existing answer
            $existing_answer = WorksheetAnswers::where(['form_field_id' => $request->input('form_field_id'), 'worksheet_id' => $id, 'project_id' => $project_id])->first();
            if($existing_answer) {
                \Storage::disk('uploads')->delete('worksheets/' . $existing_answer->answer);
            }

            $image->storeAs('worksheets/' . date('Y'), $name, 'uploads');
            $answer = date('Y') . '/' . $name;
            // Or Text
        } else {
            $answer = $request->answer;
        }

        if (WorksheetAnswers::updateOrCreate(
            ['form_field_id' => $request->input('form_field_id'), 'worksheet_id' => $id, 'project_id' => $project_id],
            ['answer' => $answer]
        )) {
            return response()->json(['success' => null], Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Failed to save record'], 503);
        }
    }

    /**
     * Create additional worksheet fields (Add more)
     *
     * @param  Request $request
     * @return object|Response
     */
    public function createFields(Request $request) {
        if (WorksheetRepeats::updateOrCreate(
            [
                'group_id' => $request->group_id,
                'worksheet_id' => $request->worksheet_id,
                'project_id' => $request->project_id
            ],
            ['count' => DB::raw('count+1')]
        )) {
            return $this->get($request->worksheet_id, $request->project_id);
        } else {
            return response()->json(['error' => 'Failed to save record'], 503);
        }
    }

    /**
     * Remove last additional worksheet field (Remove last)
     * by decreasing worksheet_group_repeats count
     *
     * @param  mixed $request
     * @return void
     */
    public function removeFields(Request $request) {
        $fields = WorksheetRepeats::where([
            'group_id' => $request->group_id,
            'worksheet_id' => $request->worksheet_id,
            'project_id' => $request->project_id
        ])->update(['count' => DB::raw('count-1')]);
        if ($fields) {
            return $this->get($request->worksheet_id, $request->project_id);
        } else {
            return response()->json(['error' => 'Failed to save record'], 503);
        }
    }

    /**
     * Create project
     *
     * @param  CreateProjectPost $request
     * @return Response
     */
    public function create(CreateProjectPost $request) {
        $validated = $request->validated();

        $p = new Projects();
        $p->class_id = auth()->user()->role->slug === 'student' ? auth()->user()->getClassID() : 0;
        $p->school_id = auth()->user()->school_id;
        $p->project_name = $request->input('project_name');
        $wid = $request->input('worksheet_id') !== null ? $request->input('worksheet_id') : null;

        if ($p->save()) {
            // Add user to project member table now
            $pm = new ProjectMembers();
            $pm->user_id = auth()->user()->id;
            $pm->project_id = $p->id;
            if ($pm->save()) {
                if ($wid) {
                    return redirect('/worksheets/' . $wid . '/' . $p->id);
                } else {
                    return response()->json(['success' => 'Your project was successfully created.', 'id' => $p->id], Response::HTTP_OK);
                }

            } else {
                return response()->json(['error' => 'Your project was created, but we were unable to add you as a member of the project.'], Response::HTTP_OK);
            }

        } else {
            return response()->json(['error' => 'Your project was created, but we were unable to add you as a member of the project.'], Response::HTTP_OK);
        }

    }

    /**
     * Manage project
     *
     * @param  Request $request
     * @return Response
     */
    public function manage(Request $request) {
        $response = response()->json(['error' => 'Something went wrong.'], 503);

        switch ($request->type) {
            case 1: // Send to Team
                $response = WorksheetService::sendProjectToTeam($request->project_id);
                break;

            case 2: // Delete project
                $response = WorksheetService::deleteProject($request->project_id);
                break;

            case 3: // Change project name
                $response = WorksheetService::changeProjectName($request->project_id, $request->project_name);
                break;

            case 4: // Switch locked project
                $response = WorksheetService::switchProjectLock($request->locked_project_id, $request->project_id);
                break;
        }
        return $response;
    }

    /**
     * Switch locked project redirect
     *
     * @return Redirect
     */
    // TODO: /switched/project is a bandaid
    public function switchRedirect() {
        return redirect('assignments')->with('success', "Successfully switched primary project. Grades from the previous primary project have been transferred to this project if it's not a team project.");
    }
}
