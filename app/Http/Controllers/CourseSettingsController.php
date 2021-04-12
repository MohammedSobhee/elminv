<?php

namespace App\Http\Controllers;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

// Validation
use App\Http\Requests\SaveCourseSettingsPost;

// Model
use App\CourseSettings;

class CourseSettingsController extends Controller {

    /**
     * Save course settings
     *
     * @param  SaveCourseSettingsPost $request
     * @return Response
     */
    public function save(SaveCourseSettingsPost $request) {
        $validated = $request->validated();
        if (CourseSettings::updateOrInsert(
            ['teacher_id' => auth()->user()->getTeacherID(), 'name' => $request->name],
            ['value' => $request->value]
        )) {
            return response()->json(['success' => 'Updated Course Settings'], Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Failed to retrieve data'], 503);
        }
    }
}
