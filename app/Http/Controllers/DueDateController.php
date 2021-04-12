<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\DateTimeTrait;
// Models
use App\Classes;
use App\Projects;
use App\WorksheetRubric;
use App\ClassMembers;
use App\Assignment;

use DB;

class DueDateController extends Controller {

    use DateTimeTrait;

    /**
     * Show Due Date Page
     *
     * @param  int $id
     * @param  int $type
     * @return resource - View
     */
    public function show($id = 0, $type = 0) {

        $class_count = auth()->user()->classes()->count();

        return view('school_management.manage_duedates', [
            'class_count' => $class_count,
            'worksheet' => self::worksheets(),
            'custom' => self::custom(),
            'editing' => ['id' => intval($id), 'type' => intval($type)]
        ]);
    }

    /**
     * Worksheets due dates
     *
     * @return object
     */
    public static function worksheets() {
        if (!auth()->user()->hasWorksheetAccess()) {
            return [];
        }

        $worksheets = WorksheetRubric::where([['teacher_id', auth()->user()->getTeacherID()], ['active', 1]])
            ->select('id', 'category_id', 'category_name as assignment_name')
            ->get();

        $worksheets->each(function ($w) {
            // Demo
            if(auth()->user()->demo && !in_array($w->category_id, config('app.demo.worksheets'))) {
                $w->disabled = 1;
            }

            $wid = $w->id;
            $classes = ClassMembers::where('user_id', auth()->user()->getTeacherID())
                ->join('class', 'class.id', 'class_members.class_id')
                ->leftJoin('assignment_due as ad', function ($join) use ($wid) {
                    $join->on('ad.class_id', '=', 'class_members.class_id')
                        ->where('ad.type_id', '=', $wid)
                        ->where('ad.type', '=', 1);
                })
                ->select('class.id', 'class.class_name as name', 'ad.due_date')
                ->groupBy('class.id')
                ->get();
            $w->classes = $classes;
            // Fake project id for view assignment
            $w->project_id = Projects::all()->take(1)->pluck('id')[0];
        });

        return $worksheets ?? [];
    }

    /**
     * Custom assignments due dates
     *
     * @return object
     */
    public static function custom() {
        $assignments = Assignment::where([['user_id', auth()->user()->getTeacherID()], ['status', 1]])
            ->select(
                'id',
                'assignment_name',
                'file_name',
                'file_location as teacher_file_location',
                'file_screenshot',
                'category_id', 'points',
                'updated_at as created',
                'type'
            )
            ->orderBy('created_at', 'desc')
            ->get();

        $assignments->each(function ($a) {
            $classes = DB::table('assignment_classes')
                ->where('assignment_id', $a->id)
                ->join('class', 'class.id', 'assignment_classes.class_id')
                ->leftJoin('assignment_due as ad', function ($join) use ($a) {
                    $join->on('ad.class_id', '=', 'class.id')
                        ->where('ad.type_id', '=', $a->id)
                        ->groupBy('ad.due_date');
                })
                ->select('class.id', 'class.class_name as name', 'ad.due_date')
                ->groupBy('class.id')
                ->get();
            $a->classes = $classes;

        });

        return $assignments ?? [];
    }

    /**
     * Update Due Date
     *
     * @param  Request $request
     * @return Resonse
     */
    public function update(Request $request) {
        $upd = \DB::table('assignment_due')
            ->updateOrInsert(
                [
                    'class_id' => $request->class_id,
                    'type' => $request->type,
                    'type_id' => $request->assignment_id
                ],
                [
                    'due_date' => $this->getDateTime($request->due_date)
                ]
            );
        if ($upd) {
            return response()->json(['success' => 'Due date successfully updated.'], 200);
        } else {
            return response()->json(['error' => 'Failed to update data'], 400);
        }
    }
}
