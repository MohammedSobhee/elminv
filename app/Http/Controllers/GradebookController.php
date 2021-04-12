<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

// Models
use App\Messages;
use App\AssignmentSubmitted as asb;
use App\Grades;
use App\WorksheetRubric;

// Request
use App\Http\Requests\SaveGradesPost;
use App\Http\Requests\UpdateGradesPost;

// Services
use App\Services\GradeBookService;

class GradebookController extends Controller {

    /**
     * Show Initial data
     *
     * @param  string $pending_user_type
     * @param  int $pending_user_id
     * @return resource - View
     */
    public function show($pending_user_type = '', $pending_user_id = 0) {

        return view('school_management.gradebook', [
            'pending_user_type' => $pending_user_type,
            'pending_user_id' => intval($pending_user_id),
            'classes' => $this->get(auth()->user()->id)
        ]);
    }

    /**
     * Save Grade
     *
     * @param  SaveGradesPost $request
     * @return Response
     */
    public function save(SaveGradesPost $request) {
        $validated = $request->validated();
        $message = $request->input('message');

        $g = new Grades();

        $g->type = $request->input('type');
        //(1 or 2 accepted 1 = Worksheets & 2= Custom Assignments)
        $g->type_id = $request->input('type_id');
        //(type_id would either be the worksheet_ID or Assignment_id)
        $g->project_id = $request->input('project_id');
        //(optional only required when type = 1 )
        $g->category_id = $request->input('category_id');
        //(Required - Rubric Category)
        $g->points = $request->input('points');
        // Student ID
        $g->user_id = $request->input('user_id');

        if ($g->save()) {
            $this->id = $g->id;
            if ($message) {
                if (Messages::updateOrCreate(
                    ['recipient_id' => $g->id, 'type' => 4],
                    ['sender_id' => $request->input('teacher_id'), 'content' => $message]
                )) {

                    if ($m = Messages::where([['recipient_id', $this->id], ['type', 4]])->first()) {
                        Grades::where('id', $this->id)->update(['message_id' => $m->id]);
                    } else {
                        return response()->json(['error' => 'fail to load message'], 503);
                    }

                } else {
                    return response()->json(['error' => 'Failed to retrieve data'], 503);
                }
            }

            return response()->json(['success' => 'Saved Grade Record', 'id' => $g->id], Response::HTTP_OK);

        } else {
            return response()->json(['error' => 'Failed to retrieve data'], 503);
        }
    }

    /**
     * Update Grade
     *
     * @param  UpdateGradesPost $request
     * @return Response
     */
    public function update(UpdateGradesPost $request) {
        $validated = $request->validated();
        $message = $request->input('message');
        $g = Grades::find($request->input('id'));
        $g->points = $request->input('points');

        $this->id = $g->id;

        //if ($message) {

        //if ($message == '<p></p>') {
        if (!$message) {
            if (Messages::where('id', $g->message_id)->count() == 1) {
                if (Messages::where('id', $g->message_id)->delete()) {
                    $g->message_id = NULL;
                } else {
                    return response()->json(['error' => 'Failed to delete message'], 503);
                }
            }
        } else if (Messages::updateOrCreate(
            ['id' => $g->message_id],
            ['sender_id' => $request->input('teacher_id'), 'content' => $message, 'recipient_id' => $g->id, 'type' => 4]
        )) {
            if ($m = Messages::where([['recipient_id', $this->id], ['type', 4]])->first()) {
                Grades::where('id', $this->id)->update(['message_id' => $m->id, 'points' => $g->points]);
            } else {
                return response()->json(['error' => 'fail to load message'], 503);
            }
        } else {
            return response()->json(['error' => 'Failed to retrieve data'], 503);
        }
        //}

        if ($g->save()) {
            return response()->json(['success' => 'Updated Grade Record'], 200);
        } else {
            return response()->json(['error' => 'Failed to retrieve data'], 503);
        }

    }

    /**
     * Delete grade
     *
     * * See GradesObserver for additional operations associated with deleting a grade
     *
     * @param  UpdateGradesPost $request
     * @return response
     */
    public function delete(UpdateGradesPost $request) {
        $g = Grades::where('id', $request->input('id'))->first();
        // return response()->json(['success' => $g], 200);
        if (Grades::find($request->input('id'))->delete()) {

            if ($g->type == 1) {
                $wr = WorksheetRubric::select('category_id')->where('worksheet_rubric.id', $g->type_id)->first();
                $asb = asb::where([['type', $g->type], ['type_id', $wr->category_id], ['user_id', $g->user_id], ['project_id', $g->project_id]])->first();
                if ($asb) {
                    $message = Messages::find($asb->message_id);
                    if ($message) {
                        $message->delete();
                    }
                    $asb->delete();
                    return response()->json(['success' => 'Deleted Grade Record and assignment submission'], 200);
                } else {
                    return response()->json(['error' => ' worksheet deleted grade record but was unable to delete assignment submission'], 503);
                }
            } else {
                $asb = asb::where([['type', $g->type], ['type_id', $g->type_id], ['user_id', $g->user_id]])->first();
                if ($asb) {
                    $message = Messages::find($asb->message_id);
                    if ($message) {
                        $message->delete();
                    }
                    $asb->delete();
                    return response()->json(['success' => 'Deleted Grade Record and assignment submission'], 200);
                } else {
                    return response()->json(['error' => 'custom deleted grade record but was unable to delete assignment submission'], 503);
                }
            }

        } else {
            return response()->json(['error' => 'Failed to retrieve data'], 503);
        }
    }

    /**
     * Get all data for use in ajax request and show
     *
     * @param  int $teacherid
     * @return array
     */
    public function get($teacherid = 0) {
        return GradeBookService::getAllData($teacherid);
    }
}
