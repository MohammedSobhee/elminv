<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

use App\Http\Requests\AddCategoryPost;

// Models
use App\RubricSettings;
use App\RubricCategories;
use App\WorksheetRubric;
use App\ClassMembers;
use App\ZoomAuthToken;

// Service
use App\Services\CourseSettingsService;

class RubricController extends Controller {

    /**
     * Show rubric settings page
     *
     * @return resource - View
     */
    public function show() {
        // Rubric category settings
        $rc = RubricSettings::where('teacher_id', auth()->user()->getTeacherID())
            ->join('rubric_categories', 'rubric_categories.id', '=', 'rubric_settings.category_id')
            ->count();

        if ($rc > 0) {
            $r = RubricSettings::where('teacher_id', auth()->user()->getTeacherID())
                ->join('rubric_categories', 'rubric_categories.id', '=', 'rubric_settings.category_id')
                ->get();
        } else {
            // Insert default rubric settings
            DB::select('call default_rubric_value(?)', [auth()->user()->getTeacherID()]);

            $r = RubricSettings::where('teacher_id', auth()->user()->getTeacherID())
                ->join('rubric_categories', 'rubric_categories.id', '=', 'rubric_settings.category_id')
                ->get();
        }

        // Rubric worksheet settings
        if (!WorksheetRubric::where('teacher_id', auth()->user()->getTeacherID())->count()) {
            DB::table('worksheet')->select('id', 'title', 'order', 'default_point_value')
                ->where('status', 1)
                ->orderBy('order', 'asc')
                ->get()
                ->each(function ($w) {
                    $wr = new WorksheetRubric();
                    $wr->category_id = $w->id;
                    $wr->category_name = $w->title;
                    $wr->order = $w->order;
                    $wr->category_value = $w->default_point_value;
                    $wr->teacher_id = auth()->user()->getTeacherID();
                    $wr->save();
                });
        }
        $ws = WorksheetRubric::where('teacher_id', auth()->user()->getTeacherID())->get();

        $ws->each(function ($w) {
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
        });
        //
        // Course Settings
        // --------------------------------------------------------------------------
        $settings = [
            'rubric' => $r,
            'worksheets' => $ws,
            'zoom_auth' => ZoomAuthToken::where('user_id', auth()->user()->id)->first(),
            'settings' => (object) CourseSettingsService::get()
        ];

        return view('school_management.settings')->with($settings);
    }

    /**
     * Update rubric category
     *
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request) {
        if ($request->input('category_type') == 1) {
            $update = RubricCategories::where('id', $request->input('category_id'))->update(['category_value' => $request->input('category_value')]);
        } else {
            $update = WorksheetRubric::where('id', $request->input('category_id'))->update(['category_value' => $request->input('category_value')]);
        }

        if ($update) {
            return response()->json(['success' => 'Update successful'], Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Failed to save record'], 503);
        }

    }

    /**
     * Add new category
     *
     * @param  AddCategoryPost $request
     * @return Response
     */
    public function add(AddCategoryPost $request) {
        // Validate data
        $validated = $request->validated();

        // Run Stored Procedure
        $req = DB::select('call add_category(?,?,?,?)', [
            auth()->user()->getTeacherID(),
            $request->input('category_name'),
            $request->input('category_value'),
            $request->input('category_active')
        ]);
        // Return Response
        if ($req) {
            return response()->json(['success' => $req[0]->cat_id], Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Failed to save record'], 503);
        }
    }

    /**
     * Activate Category
     *
     * @param  Request $request
     * @return Response
     */
    public function activate(Request $request) {
        if ($request->input('category_type') == 1) {
            $update = RubricSettings::where('category_id', $request->input('category_id'))->update(['active' => $request->input('category_active')]);
        } else {
            $update = WorksheetRubric::where('id', $request->input('category_id'))->update(['active' => $request->input('category_active')]);
        }
        if ($update) {
            return response()->json(['success' => 'Activate successful'], Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Failed to save record'], 503);
        }
    }

    /**
     * Delete Category
     *
     * @param  Request $request
     * @return Response
     */
    public function delete(Request $request) {
        if (RubricCategories::where('id', $request->input('category_id'))->delete()) {
            if (RubricSettings::where('category_id', $request->input('category_id'))->delete()) {
                return response()->json(['success' => 'Category & settings for it removed.'], Response::HTTP_OK);
            } else {
                return response()->json(['error' => 'Failed to remove settings'], 503);
            }
        } else {
            return response()->json(['error' => 'Failed to delete record'], 503);
        }
    }
}
